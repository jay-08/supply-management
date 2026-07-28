<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InventoryItem;
use App\Models\SupplyRequest;
use App\Models\Issuance;
use App\Models\ActivityLog;
use App\Models\Department;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function inventory(Request $request)
    {
        $items = InventoryItem::with(['category', 'supplier'])
            ->orderBy('category_id')->orderBy('name')->get();
        $title = 'Inventory Report';
        return view('reports.preview', compact('items', 'title'))->with('type', 'inventory');
    }

    public function lowStock(Request $request)
    {
        $items = InventoryItem::with(['category', 'supplier'])
            ->where('status', 'active')
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')->get();
        $title = 'Low Stock Report';
        return view('reports.preview', compact('items', 'title'))->with('type', 'low_stock');
    }

    public function issuance(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);

        $issuances = Issuance::with(['recipient', 'department', 'items.inventoryItem'])
            ->whereMonth('issued_at', $mon)->whereYear('issued_at', $year)
            ->orderByDesc('issued_at')->get();
        $title = 'Monthly Issuance Report — ' . Carbon::create($year, $mon)->format('F Y');
        return view('reports.preview', compact('issuances', 'title'))->with('type', 'issuance');
    }

    public function requests(Request $request)
    {
        $requests = SupplyRequest::with(['requester', 'department', 'items'])
            ->orderByDesc('created_at')->get();
        $title = 'Supply Request History';
        return view('reports.preview', compact('requests', 'title'))->with('type', 'requests');
    }

    public function activity(Request $request)
    {
        $logs = ActivityLog::with('user')->orderByDesc('created_at')->limit(200)->get();
        $title = 'User Activity Log';
        return view('reports.preview', compact('logs', 'title'))->with('type', 'activity');
    }

    public function exportPdf(string $type, Request $request)
    {
        $data  = [];
        $title = '';

        switch ($type) {
            case 'inventory':
                $data['items'] = InventoryItem::with(['category', 'supplier'])->orderBy('name')->get();
                $title = 'Inventory Report';
                break;
            case 'low_stock':
                $data['items'] = InventoryItem::with(['category', 'supplier'])
                    ->whereColumn('quantity', '<=', 'reorder_level')->orderBy('quantity')->get();
                $title = 'Low Stock Report';
                break;
            case 'issuance':
                $month = $request->get('month', now()->format('Y-m'));
                [$year, $mon] = explode('-', $month);
                $data['issuances'] = Issuance::with(['recipient', 'department', 'items.inventoryItem'])
                    ->whereMonth('issued_at', $mon)->whereYear('issued_at', $year)->get();
                $title = 'Issuance Report';
                break;
            case 'requests':
                $data['requests'] = SupplyRequest::with(['requester', 'department'])->get();
                $title = 'Supply Request History';
                break;
            case 'activity':
                $data['logs'] = ActivityLog::with('user')->orderByDesc('created_at')->limit(200)->get();
                $title = 'Activity Log';
                break;
        }

        $data['title'] = $title;
        $pdf = Pdf::loadView("reports.pdf.{$type}", $data)->setPaper('a4', 'landscape');
        ActivityLog::log('exported', 'report', "Exported PDF report: {$title}");

        return $pdf->download(str_replace(' ', '-', strtolower($title)) . '-' . now()->format('Ymd') . '.pdf');
    }
}
