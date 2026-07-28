<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseOrder;
use App\Models\Delivery;
use App\Models\Supplier;

class ProcurementReportController extends Controller
{
    public function index()
    {
        return view('procurement.reports.index');
    }

    public function purchaseOrders(Request $request)
    {
        $data = $this->buildPoReport($request);
        $title = 'Purchase Order Report';
        return view('procurement.reports.preview', $data + compact('title'))->with('type', 'po');
    }

    public function deliveries(Request $request)
    {
        $deliveries = Delivery::with(['purchaseOrder.supplier', 'receiver'])
            ->orderByDesc('delivery_date')->get();
        $title = 'Delivery / GRN Report';
        return view('procurement.reports.preview', compact('deliveries', 'title'))->with('type', 'deliveries');
    }

    public function supplierPerformance()
    {
        $suppliers = Supplier::withCount(['purchaseRecords'])
            ->with(['purchaseOrders' => fn($q) => $q->select('id','supplier_id','status','total_amount','delivery_date','sent_at')])
            ->where('status', 'active')
            ->get()
            ->map(function ($s) {
                $pos = $s->purchaseOrders;
                $s->po_count      = $pos->count();
                $s->total_spend   = $pos->whereNotIn('status', ['cancelled','draft'])->sum('total_amount');
                $s->on_time       = $pos->where('status', 'delivered')
                    ->filter(fn($p) => $p->delivery_date && $p->sent_at &&
                        $p->delivery_date->greaterThanOrEqualTo($p->sent_at))->count();
                return $s;
            });
        $title = 'Supplier Performance Report';
        return view('procurement.reports.preview', compact('suppliers', 'title'))->with('type', 'suppliers');
    }

    public function exportPdf(string $type, Request $request)
    {
        $data  = [];
        $title = '';

        match ($type) {
            'po'        => [$data, $title] = [$this->buildPoReport($request), 'Purchase Order Report'],
            'deliveries'=> [$data['deliveries'] = Delivery::with('purchaseOrder.supplier')->get(), $title = 'GRN Report'],
            default     => null,
        };

        $data['title'] = $title;
        $pdf = Pdf::loadView("procurement.reports.pdf.{$type}", $data)->setPaper('a4', 'landscape');
        return $pdf->download("procurement-{$type}-" . now()->format('Ymd') . '.pdf');
    }

    private function buildPoReport(Request $request): array
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'items']);
        if ($s = $request->status)      { $query->where('status', $s); }
        if ($sup = $request->supplier_id){ $query->where('supplier_id', $sup); }
        if ($from = $request->from)     { $query->where('po_date', '>=', $from); }
        if ($to   = $request->to)       { $query->where('po_date', '<=', $to); }
        return ['pos' => $query->orderByDesc('po_date')->get()];
    }
}
