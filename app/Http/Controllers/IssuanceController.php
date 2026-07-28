<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Issuance;
use App\Models\ActivityLog;

class IssuanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Issuance::with(['recipient', 'issuer', 'department', 'items']);
        if ($month = $request->month) {
            $query->whereMonth('issued_at', date('m', strtotime($month)))
                  ->whereYear('issued_at', date('Y', strtotime($month)));
        }
        $issuances = $query->orderByDesc('issued_at')->paginate(15)->withQueryString();
        return view('issuances.index', compact('issuances'));
    }

    public function show(int $id)
    {
        $issuance = Issuance::with([
            'supplyRequest', 'recipient', 'issuer',
            'department', 'items.inventoryItem.category', 'returns'
        ])->findOrFail($id);
        return view('issuances.show', compact('issuance'));
    }

    public function destroy(Issuance $issuance)
    {
        ActivityLog::log('deleted', 'issuance', "Deleted issuance: {$issuance->issuance_number}", $issuance);
        $issuance->delete();
        return redirect()->route('issuances.index')->with('success', 'Issuance record deleted.');
    }

    public function printSlip(int $id)
    {
        $issuance = Issuance::with([
            'supplyRequest', 'recipient', 'issuer',
            'department', 'items.inventoryItem.category'
        ])->findOrFail($id);
        return view('issuances.print', compact('issuance'));
    }

    public function pdf(int $id)
    {
        $issuance = Issuance::with([
            'supplyRequest', 'recipient', 'issuer',
            'department', 'items.inventoryItem.category'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('issuances.pdf', compact('issuance'))
                  ->setPaper('a4', 'portrait');

        ActivityLog::log('exported', 'issuance', "Exported PDF: {$issuance->issuance_number}", $issuance);

        return $pdf->download("issuance-{$issuance->issuance_number}.pdf");
    }
}
