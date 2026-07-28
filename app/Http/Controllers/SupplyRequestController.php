<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SupplyRequest;
use App\Models\RequestItem;
use App\Models\Issuance;
use App\Models\IssuanceItem;
use App\Models\InventoryItem;
use App\Models\Department;
use App\Models\Notification;
use App\Models\ActivityLog;

class SupplyRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplyRequest::with(['requester', 'department', 'items']);

        // Only global approvers can see all requests; everyone else sees only their own.
        if (!auth()->user()->hasAnyRole(['admin', 'supply-officer', 'regional-director', 'assistant-regional-director', 'supply-staff', 'budget-staff', 'accounting-staff', 'ard-staff', 'rd-staff'])) {
            $query->where('requester_id', auth()->id());
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($dept = $request->department_id) {
            $query->where('department_id', $dept);
        }

        $requests    = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('requests.index', compact('requests', 'departments'));
    }

    public function create()
    {
        $items       = InventoryItem::with('category')->where('status', 'active')->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('requests.create', compact('items', 'departments'));
    }

    public function checkout()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('requests.checkout', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id'  => 'required|exists:departments,id',
            'purpose'        => 'required|string',
            'needed_by'      => 'nullable|date|after:today',
            'remarks'        => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'          => 'required|integer|min:1',
            'items.*.notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $req = SupplyRequest::create([
                'request_number' => SupplyRequest::generateRequestNumber(),
                'requester_id'   => auth()->id(),
                'department_id'  => $data['department_id'],
                'purpose'        => $data['purpose'],
                'needed_by'      => $data['needed_by'] ?? null,
                'remarks'        => $data['remarks'] ?? null,
                'status'         => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                RequestItem::create([
                    'supply_request_id'  => $req->id,
                    'inventory_item_id'  => $item['inventory_item_id'],
                    'quantity_requested' => $item['quantity'],
                    'notes'              => $item['notes'] ?? null,
                ]);
            }

            ActivityLog::log('created', 'request', "Submitted supply request: {$req->request_number}", $req);

            // Notify supply officers
            $officers = \App\Models\User::role(['admin', 'supply-officer'])->get();
            foreach ($officers as $officer) {
                Notification::send($officer->id, 'new_request', 'New Supply Request',
                    auth()->user()->name . " submitted {$req->request_number}.",
                    'bi-cart-plus', route('requests.show', $req->id));
            }
        });

        return redirect()->route('requests.index')->with('success', 'Supply request submitted successfully.');
    }

    public function show(SupplyRequest $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'supply-officer', 'regional-director', 'assistant-regional-director', 'supply-staff', 'budget-staff', 'accounting-staff', 'ard-staff', 'rd-staff'])) {
            if ($request->requester_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this request.');
            }
        }

        $request->load(['requester', 'department', 'approver', 'issuer', 'items.inventoryItem.category']);
        return view('requests.show', compact('request'));
    }

    public function approve(Request $r, int $id)
    {
        $req    = SupplyRequest::findOrFail($id);
        $data   = $r->validate([
            'items'                     => 'required|array',
            'items.*.id'                => 'required|exists:request_items,id',
            'items.*.quantity_approved' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($req, $data) {
            foreach ($data['items'] as $item) {
                RequestItem::where('id', $item['id'])->update([
                    'quantity_approved' => $item['quantity_approved'],
                ]);
            }
            $req->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            ActivityLog::log('approved', 'request', "Approved request: {$req->request_number}", $req);
            Notification::send($req->requester_id, 'request_approved', 'Request Approved',
                "Your request {$req->request_number} has been approved.",
                'bi-check-circle', route('requests.show', $req->id));
        });

        return back()->with('success', 'Request approved.');
    }

    public function reject(Request $r, int $id)
    {
        $req  = SupplyRequest::findOrFail($id);
        $data = $r->validate(['rejection_reason' => 'required|string|max:500']);
        $req->update(['status' => 'rejected', 'rejection_reason' => $data['rejection_reason']]);
        ActivityLog::log('rejected', 'request', "Rejected request: {$req->request_number}", $req);
        Notification::send($req->requester_id, 'request_rejected', 'Request Rejected',
            "Your request {$req->request_number} was rejected: {$data['rejection_reason']}",
            'bi-x-circle', route('requests.show', $req->id));
        return back()->with('success', 'Request rejected.');
    }

    public function issue(Request $r, int $id)
    {
        $req  = SupplyRequest::with('items.inventoryItem')->findOrFail($id);
        $data = $r->validate(['remarks' => 'nullable|string']);

        DB::transaction(function () use ($req, $data) {
            $issuance = Issuance::create([
                'issuance_number'   => Issuance::generateIssuanceNumber(),
                'supply_request_id' => $req->id,
                'issued_to'         => $req->requester_id,
                'issued_by'         => auth()->id(),
                'department_id'     => $req->department_id,
                'remarks'           => $data['remarks'] ?? null,
                'issued_at'         => now(),
            ]);

            foreach ($req->items as $item) {
                $qty = $item->quantity_approved ?? $item->quantity_requested;
                if ($qty <= 0) continue;

                IssuanceItem::create([
                    'issuance_id'       => $issuance->id,
                    'inventory_item_id' => $item->inventory_item_id,
                    'quantity'          => $qty,
                    'unit_cost'         => $item->inventoryItem->unit_cost,
                ]);

                // Deduct from inventory
                $invItem = $item->inventoryItem;
                $invItem->adjustStock(-$qty, 'stock_out', auth()->id(),
                    "Issued via {$issuance->issuance_number}", 'App\Models\Issuance', $issuance->id);

                $item->update(['quantity_issued' => $qty]);

                // Low-stock check
                if ($invItem->fresh()->isLowStock()) {
                    Notification::notifyLowStock($invItem->fresh());
                }
            }

            $req->update([
                'status'    => 'issued',
                'issued_by' => auth()->id(),
                'issued_at' => now(),
            ]);

            ActivityLog::log('issued', 'request', "Issued supplies for: {$req->request_number}", $req);
            Notification::send($req->requester_id, 'supplies_issued', 'Supplies Issued',
                "Supplies for {$req->request_number} have been issued. Receipt: {$issuance->issuance_number}",
                'bi-box-seam', route('issuances.show', $issuance->id));
        });

        return redirect()->route('issuances.index')->with('success', 'Supplies issued successfully.');
    }

    public function claim(Request $r, int $id)
    {
        $req  = SupplyRequest::findOrFail($id);
        
        // Ensure only the requester can claim
        if (auth()->id() !== $req->requester_id) {
            abort(403, 'Unauthorized to claim this request.');
        }

        if ($req->status !== 'issued') {
            return back()->with('error', 'Request must be issued before it can be claimed.');
        }

        $req->update([
            'status'     => 'claimed',
            'claimed_at' => now(),
        ]);

        ActivityLog::log('claimed', 'request', "Claimed supplies for: {$req->request_number}", $req);
        
        if ($req->approved_by) {
            Notification::send($req->approved_by, 'supplies_claimed', 'Supplies Claimed',
                "Supplies for {$req->request_number} have been claimed by {$req->requester?->name}.",
                'bi-box-seam', route('requests.show', $req->id));
        }

        return redirect()->route('requests.show', $req->id)->with('success', 'Supplies claimed successfully.');
    }

    public function cancel(Request $r, int $id)
    {
        $req = SupplyRequest::findOrFail($id);
        if (!in_array($req->status, ['pending', 'approved'])) {
            return back()->with('error', 'Cannot cancel this request.');
        }
        $req->update(['status' => 'cancelled']);
        ActivityLog::log('cancelled', 'request', "Cancelled request: {$req->request_number}", $req);
        return back()->with('success', 'Request cancelled.');
    }
}
