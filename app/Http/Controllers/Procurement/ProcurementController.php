<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Delivery;
use App\Models\Supplier;
use Carbon\Carbon;

class ProcurementController extends Controller
{
    public function dashboard()
    {
        // KPI counts
        $activePO     = PurchaseOrder::whereIn('status', ['pending','sent','partially_delivered'])->count();
        $deliveredPO  = PurchaseOrder::where('status', 'delivered')->count();
        $cancelledPO  = PurchaseOrder::where('status', 'cancelled')->count();

        // Monthly spending (last 6 months)
        $monthlySeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $total = PurchaseOrder::whereMonth('po_date', $date->month)
                ->whereYear('po_date', $date->year)
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->sum('total_amount');
            $monthlySeries[] = ['month' => $date->format('M Y'), 'total' => (float)$total];
        }

        // Top suppliers by PO value
        $topSuppliers = PurchaseOrder::with('supplier')
            ->selectRaw('supplier_id, SUM(total_amount) as total_spend, COUNT(*) as po_count')
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->groupBy('supplier_id')
            ->orderByDesc('total_spend')
            ->limit(5)
            ->get();

        // Recent activity
        $recentPOs = PurchaseOrder::with(['supplier', 'creator'])
            ->orderByDesc('created_at')->limit(6)->get();

        // Total procurement value this year
        $yearTotal = PurchaseOrder::whereYear('po_date', now()->year)
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->sum('total_amount');

        // Overdue POs (delivery date passed, not delivered)
        $overduePOs = PurchaseOrder::whereIn('status', ['sent', 'partially_delivered'])
            ->where('delivery_date', '<', now()->toDateString())
            ->count();

        return view('procurement.dashboard', compact(
            'activePO', 'deliveredPO', 'cancelledPO',
            'monthlySeries', 'topSuppliers', 'recentPOs',
            'yearTotal', 'overduePOs'
        ));
    }
}
