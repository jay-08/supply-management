<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Notification;
use App\Models\ActivityLog;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        if ($s = $request->status)      { $query->where('status', $s); }
        if ($sup = $request->supplier_id) { $query->where('supplier_id', $sup); }
        if ($from = $request->from)     { $query->where('po_date', '>=', $from); }
        if ($to   = $request->to)       { $query->where('po_date', '<=', $to); }

        $pos       = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('procurement.purchase-orders.index', compact('pos', 'suppliers'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        return view('procurement.purchase-orders.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'         => 'required|exists:suppliers,id',
            'po_date'             => 'required|date',
            'delivery_date'       => 'nullable|date|after:po_date',
            'payment_terms'       => 'nullable|string|max:100',
            'delivery_address'    => 'nullable|string',
            'notes'               => 'nullable|string',
            'tax_rate'            => 'nullable|numeric|min:0|max:100',
            'total_amount'        => 'required|numeric|min:0',
            'status'              => 'required_without:is_legacy|in:draft,pending',
            'attachment'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_legacy'           => 'nullable|boolean',
            'legacy_status'       => 'nullable|string|required_if:is_legacy,1',
            'legacy_remarks'      => 'nullable|string',
        ], [
            'attachment.max'   => 'The attached file size cannot exceed 5 MB.',
            'attachment.mimes' => 'The attached file must be a PDF or Image (.pdf, .jpg, .png).',
        ]);

        DB::transaction(function () use ($data, $request) {
            $taxRate  = (float)($data['tax_rate'] ?? 0);
            $totalAmount = (float)$data['total_amount'];
            // If there's tax, we just save the total amount, subtotal can be backwards calculated or left as total.
            // Let's just set subtotal to total_amount for simplicity if no tax, or calculate if there is.
            $subtotal = $totalAmount;
            $taxAmt = 0;
            if ($taxRate > 0) {
                // If user inputted total_amount including tax
                $subtotal = $totalAmount / (1 + ($taxRate / 100));
                $taxAmt = $totalAmount - $subtotal;
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                $file = $request->file('attachment');
                $file->store('purchase_orders', 'public');
                $mime = $file->getClientMimeType() ?: $file->getMimeType();
                $attachmentPath = 'data:' . $mime . ';base64,' . base64_encode($file->get());
            }

            $po = PurchaseOrder::create([
                'po_number'           => PurchaseOrder::generatePoNumber(),
                'supplier_id'         => $data['supplier_id'],
                'created_by'          => auth()->id(),
                'po_date'             => $data['po_date'],
                'delivery_date'       => $data['delivery_date'] ?? null,
                'payment_terms'       => $data['payment_terms'] ?? null,
                'delivery_address'    => $data['delivery_address'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'tax_rate'            => $taxRate,
                'tax_amount'          => $taxAmt,
                'subtotal'            => $subtotal,
                'total_amount'        => $totalAmount,
                'status'              => !empty($data['is_legacy']) ? $data['legacy_status'] : $data['status'],
                'attachment'          => $attachmentPath,
            ]);

            if (!empty($data['is_legacy'])) {
                $po->approvals()->create([
                    'approver_id' => auth()->id(),
                    'level'       => 'supply_officer',
                    'action'      => 'legacy_migrated',
                    'remarks'     => $data['legacy_remarks'] ?? 'Migrated from legacy system',
                    'acted_at'    => now(),
                ]);
            }

            ActivityLog::log('created', 'procurement', "Created PO: {$po->po_number}", $po);
        });

        return redirect()->route('procurement.purchase-orders.index')
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier', 'creator',
            'items.inventoryItem', 'deliveries.items', 'approvals.approver',
        ]);
        return view('procurement.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return back()->with('error', 'Only draft or pending POs can be edited.');
        }
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $purchaseOrder->load('items.inventoryItem');
        return view('procurement.purchase-orders.edit', compact('purchaseOrder', 'suppliers'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return back()->with('error', 'Cannot edit a sent/delivered PO.');
        }

        $data = $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'delivery_date'    => 'nullable|date',
            'payment_terms'    => 'nullable|string|max:100',
            'delivery_address' => 'nullable|string',
            'notes'            => 'nullable|string',
            'tax_rate'         => 'nullable|numeric|min:0|max:100',
            'total_amount'     => 'required|numeric|min:0',
            'status'           => 'required|in:draft,pending',
            'attachment'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'attachment.max'   => 'The attached file size cannot exceed 5 MB.',
            'attachment.mimes' => 'The attached file must be a PDF or Image (.pdf, .jpg, .png).',
        ]);

        DB::transaction(function () use ($purchaseOrder, $data, $request) {
            $taxRate  = (float)($data['tax_rate'] ?? 0);
            $totalAmount = (float)$data['total_amount'];
            $subtotal = $totalAmount;
            $taxAmt = 0;
            if ($taxRate > 0) {
                $subtotal = $totalAmount / (1 + ($taxRate / 100));
                $taxAmt = $totalAmount - $subtotal;
            }

            $updateData = [
                'supplier_id'      => $data['supplier_id'],
                'delivery_date'    => $data['delivery_date'] ?? null,
                'payment_terms'    => $data['payment_terms'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'tax_rate'         => $taxRate,
                'tax_amount'       => $taxAmt,
                'subtotal'         => $subtotal,
                'total_amount'     => $totalAmount,
                'status'           => $data['status'],
            ];

            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                $file = $request->file('attachment');
                $file->store('purchase_orders', 'public');
                $mime = $file->getClientMimeType() ?: $file->getMimeType();
                $updateData['attachment'] = 'data:' . $mime . ';base64,' . base64_encode($file->get());
            }

            $purchaseOrder->update($updateData);

            // Keep existing items if any to avoid breaking legacy POs, but new POs won't have any.
            // Alternatively, delete existing items:
            // $purchaseOrder->items()->delete();

            ActivityLog::log('updated', 'procurement', "Updated PO: {$purchaseOrder->po_number}", $purchaseOrder);
        });

        return redirect()->route('procurement.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft'])) {
            return back()->with('error', 'Only draft POs can be deleted.');
        }
        ActivityLog::log('deleted', 'procurement', "Deleted PO: {$purchaseOrder->po_number}", $purchaseOrder);
        $purchaseOrder->delete();
        return redirect()->route('procurement.purchase-orders.index')->with('success', 'PO deleted.');
    }

    public function markSent(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'sent', 'sent_at' => now()]);
        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => 'supply_officer',
            'action' => 'sent',
            'remarks' => 'Sent to Supplier',
            'acted_at' => now(),
        ]);
        ActivityLog::log('sent', 'procurement', "PO sent to supplier: {$purchaseOrder->po_number}", $purchaseOrder);
        return back()->with('success', 'PO marked as sent to supplier.');
    }

    public function submitDraft(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft POs can be submitted.');
        }
        $purchaseOrder->update(['status' => 'pending']);
        ActivityLog::log('submitted', 'procurement', "Submitted Draft PO: {$purchaseOrder->po_number}", $purchaseOrder);
        return back()->with('success', 'Purchase order submitted successfully.');
    }

    public function receivePO(PurchaseOrder $purchaseOrder)
    {
        $level = null;
        if ($purchaseOrder->status === 'routed_to_budget' && auth()->user()->hasAnyRole(['admin', 'budget-officer', 'budget-staff'])) {
            $purchaseOrder->update(['status' => 'received_by_budget']);
            $level = 'budget_officer';
        } elseif ($purchaseOrder->status === 'routed_to_accounting' && auth()->user()->hasAnyRole(['admin', 'accounting', 'accounting-staff'])) {
            $purchaseOrder->update(['status' => 'received_by_accounting']);
            $level = 'accounting';
        } elseif ($purchaseOrder->status === 'routed_to_ard' && auth()->user()->hasAnyRole(['admin', 'assistant-regional-director', 'ard-staff'])) {
            $purchaseOrder->update(['status' => 'received_by_ard']);
            $level = 'assistant_regional_director';
        } elseif ($purchaseOrder->status === 'routed_to_rd' && auth()->user()->hasAnyRole(['admin', 'regional-director', 'rd-staff'])) {
            $purchaseOrder->update(['status' => 'received_by_rd']);
            $level = 'regional_director';
        } elseif ($purchaseOrder->status === 'returned_to_supply' && auth()->user()->hasAnyRole(['admin', 'supply-officer', 'supply-staff'])) {
            $purchaseOrder->update(['status' => 'pending']);
            $level = 'supply_officer';
        } else {
            return back()->with('error', 'You cannot receive this PO at its current status.');
        }

        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => $level,
            'action' => 'received',
            'remarks' => 'Received Document',
            'acted_at' => now(),
        ]);
        ActivityLog::log('received', 'procurement', "PO {$purchaseOrder->po_number} received by {$level}", $purchaseOrder);
        return back()->with('success', 'PO received successfully. You may now approve or return it.');
    }

    public function routeToBudget(PurchaseOrder $purchaseOrder, $remarks = null)
    {
        $purchaseOrder->update(['status' => 'routed_to_budget']);
        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => 'supply_officer',
            'action' => 'routed',
            'remarks' => $remarks ?: 'Routed to Budget Officer for approval',
            'acted_at' => now(),
        ]);
        ActivityLog::log('routed', 'procurement', "PO {$purchaseOrder->po_number} routed to Budget Officer", $purchaseOrder);
        return back()->with('success', 'PO routed to Budget Officer.');
    }

    public function approveBudget(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'budget_approved']);
        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => 'budget_officer',
            'action' => 'approved',
            'remarks' => $request->remarks ?: null,
            'acted_at' => now(),
        ]);
        ActivityLog::log('approved', 'procurement', "PO {$purchaseOrder->po_number} budget approved", $purchaseOrder);
        return back()->with('success', 'Budget approved. PO is ready to be routed to Accounting.');
    }

    public function routeToAccounting(PurchaseOrder $purchaseOrder, $remarks = null)
    {
        $purchaseOrder->update(['status' => 'routed_to_accounting']);
        
        $level = 'admin';
        if (auth()->user()->hasRole('budget-officer')) $level = 'budget_officer';
        elseif (auth()->user()->hasRole('accounting')) $level = 'accounting';
        elseif (auth()->user()->hasRole('regional-director')) $level = 'regional_director';
        elseif (auth()->user()->hasRole('supply-officer')) $level = 'supply_officer';

        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => $level,
            'action' => 'routed',
            'remarks' => $remarks ?: 'Routed to Accounting for approval',
            'acted_at' => now(),
        ]);
        ActivityLog::log('routed', 'procurement', "PO {$purchaseOrder->po_number} routed to Accounting", $purchaseOrder);
        return back()->with('success', 'PO routed to Accounting.');
    }

    public function approveAccounting(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'accounting_approved']);
        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => 'accounting',
            'action' => 'approved',
            'remarks' => $request->remarks ?: null,
            'acted_at' => now(),
        ]);
        ActivityLog::log('approved', 'procurement', "PO {$purchaseOrder->po_number} accounting approved", $purchaseOrder);
        return back()->with('success', 'Accounting approved. PO is ready to be routed to RD/ARD.');
    }

    public function routeToRD(PurchaseOrder $purchaseOrder, $remarks = null)
    {
        $status = 'routed_to_rd';
        $levelStr = 'RD/ARD';
        $actionRemarks = 'Routed to RD for approval (100k and above)';

        if ($purchaseOrder->total_amount < 100000) {
            $status = 'routed_to_ard';
            $actionRemarks = 'Routed to ARD for approval (Below 100k)';
        }

        $purchaseOrder->update(['status' => $status]);

        $level = 'admin';
        if (auth()->user()->hasRole('budget-officer')) $level = 'budget_officer';
        elseif (auth()->user()->hasRole('accounting')) $level = 'accounting';
        elseif (auth()->user()->hasRole('regional-director')) $level = 'regional_director';
        elseif (auth()->user()->hasRole('assistant-regional-director')) $level = 'assistant_regional_director';
        elseif (auth()->user()->hasRole('supply-officer')) $level = 'supply_officer';

        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => $level,
            'action' => 'routed',
            'remarks' => $remarks ?: $actionRemarks,
            'acted_at' => now(),
        ]);
        ActivityLog::log('routed', 'procurement', "PO {$purchaseOrder->po_number} routed to {$levelStr}", $purchaseOrder);
        return back()->with('success', "PO routed to {$levelStr}.");
    }

    public function approveRD(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received_by_ard') {
            $purchaseOrder->update(['status' => 'ard_approved']);
            $level = 'assistant_regional_director';
            $logMsg = "PO {$purchaseOrder->po_number} ARD approved";
            $successMsg = 'ARD approved. PO is ready to be sent to the supplier.';
        } else {
            $purchaseOrder->update(['status' => 'rd_approved']);
            $level = 'regional_director';
            $logMsg = "PO {$purchaseOrder->po_number} RD approved";
            $successMsg = 'RD approved. PO is ready to be sent to the supplier.';
        }

        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => $level,
            'action' => 'approved',
            'remarks' => $request->remarks ?: null,
            'acted_at' => now(),
        ]);
        ActivityLog::log('approved', 'procurement', $logMsg, $purchaseOrder);
        return back()->with('success', $successMsg);
    }

    public function returnPO(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'return_to' => 'required|in:supply,budget,accounting',
            'remarks' => 'required|string',
        ]);

        $statusMap = [
            'supply' => 'returned_to_supply',
            'budget' => 'returned_to_budget',
            'accounting' => 'returned_to_accounting',
        ];

        $purchaseOrder->update(['status' => $statusMap[$data['return_to']]]);
        
        // determine current role of the returner for logging
        $level = 'admin';
        if (auth()->user()->hasRole('budget-officer')) $level = 'budget_officer';
        elseif (auth()->user()->hasRole('accounting')) $level = 'accounting';
        elseif (auth()->user()->hasRole('regional-director')) $level = 'regional_director';
        elseif (auth()->user()->hasRole('supply-officer')) $level = 'supply_officer';

        $purchaseOrder->approvals()->create([
            'approver_id' => auth()->id(),
            'level' => $level,
            'action' => 'returned',
            'remarks' => $data['remarks'],
            'acted_at' => now(),
        ]);

        ActivityLog::log('returned', 'procurement', "PO {$purchaseOrder->po_number} returned to {$data['return_to']}", $purchaseOrder);
        return back()->with('success', "PO returned to {$data['return_to']}.");
    }

    public function routeForward(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'forward_to' => 'required|in:budget,accounting,rd',
            'remarks'    => 'nullable|string',
        ]);

        if ($data['forward_to'] === 'budget') {
            return $this->routeToBudget($purchaseOrder, $data['remarks']);
        } elseif ($data['forward_to'] === 'accounting') {
            return $this->routeToAccounting($purchaseOrder, $data['remarks']);
        } elseif ($data['forward_to'] === 'rd') {
            return $this->routeToRD($purchaseOrder, $data['remarks']);
        }
        
        return back()->with('error', 'Invalid route destination.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'supply-officer'])) {
            return back()->with('error', 'Unauthorized action. Only Admin and Supply Officer can cancel POs.');
        }

        $data = $request->validate(['cancellation_reason' => 'required|string|max:500']);
        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $data['cancellation_reason'],
        ]);
        ActivityLog::log('cancelled', 'procurement', "Cancelled PO: {$purchaseOrder->po_number}", $purchaseOrder);
        return back()->with('success', 'PO cancelled.');
    }

    public function printPO(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.inventoryItem', 'creator']);
        return view('procurement.purchase-orders.print', compact('purchaseOrder'));
    }

    public function exportPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.inventoryItem', 'creator']);
        $pdf = Pdf::loadView('procurement.purchase-orders.pdf', compact('purchaseOrder'))
                  ->setPaper('a4', 'portrait');
        ActivityLog::log('exported', 'procurement', "PDF exported: {$purchaseOrder->po_number}", $purchaseOrder);
        return $pdf->download("PO-{$purchaseOrder->po_number}.pdf");
    }
}
