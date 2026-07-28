<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\SupplyRequest;
use App\Models\Issuance;
use App\Models\ActivityLog;
use App\Models\Notification;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasAnyRole(['staff', 'client'])) {
            return redirect()->route('requests.index');
        }

        $totalItems        = InventoryItem::where('status', 'active')->count();
        $lowStockItems     = InventoryItem::where('status', 'active')
                                ->whereColumn('quantity', '<=', 'reorder_level')->count();
        $pendingRequests   = SupplyRequest::where('status', 'pending')->count();
        $issuedThisMonth   = Issuance::whereMonth('issued_at', now()->month)
                                ->whereYear('issued_at', now()->year)->count();

        $lowStockList = InventoryItem::with('category')
            ->where('status', 'active')
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        $recentIssuances = Issuance::with(['recipient', 'department', 'items.inventoryItem'])
            ->orderByDesc('issued_at')
            ->limit(8)
            ->get();

        $pendingList = SupplyRequest::with(['requester', 'department', 'items'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $recentActivity = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalItems', 'lowStockItems', 'pendingRequests', 'issuedThisMonth',
            'lowStockList', 'recentIssuances', 'pendingList', 'recentActivity'
        ));
    }

    /**
     * JSON endpoint for Chart.js charts.
     */
    public function chartData(Request $request)
    {
        // Monthly issuances for the past 6 months
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $date   = Carbon::now()->subMonths($i);
            $count  = Issuance::whereMonth('issued_at', $date->month)
                               ->whereYear('issued_at', $date->year)->count();
            $monthly[] = ['month' => $date->format('M Y'), 'count' => $count];
        }

        // Category distribution (by quantity in inventory)
        $categories = InventoryItem::with('category')
            ->selectRaw('category_id, SUM(quantity) as total')
            ->groupBy('category_id')
            ->get()
            ->map(fn($i) => [
                'label' => $i->category?->name ?? 'Uncategorized',
                'value' => (int) $i->total,
            ]);

        // Request status distribution
        $statuses = SupplyRequest::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->get()
            ->map(fn($r) => ['label' => ucfirst($r->status), 'value' => $r->count]);

        return response()->json(compact('monthly', 'categories', 'statuses'));
    }
}
