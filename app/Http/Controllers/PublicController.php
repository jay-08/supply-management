<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SupplyRequest;
use App\Models\RequestItem;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use App\Models\Department;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;

class PublicController extends Controller
{
    public function index()
    {
        return view('public.home');
    }

    public function catalog()
    {
        $items = InventoryItem::with('category')->where('status', 'active')->orderBy('name')->get();
        return view('public.catalog', compact('items'));
    }

    public function checkout()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('public.checkout', compact('departments'));
    }

    public function storeCheckout(Request $request)
    {
        $data = $request->validate([
            'guest_name'     => 'required|string|max:255',
            'department_id'  => 'required|exists:departments,id',
            'purpose'        => 'required|string',
            'needed_by'      => 'nullable|date|after:today',
            'remarks'        => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'          => 'required|integer|min:1',
        ]);

        $reqNumber = '';

        DB::transaction(function () use ($data, &$reqNumber) {
            $reqNumber = SupplyRequest::generateRequestNumber();
            
            $req = SupplyRequest::create([
                'request_number' => $reqNumber,
                'requester_id'   => auth()->check() ? auth()->id() : null,
                'guest_name'     => auth()->check() ? auth()->user()->name : $data['guest_name'],
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
                ]);
            }

            ActivityLog::log('created', 'request', "Guest {$data['guest_name']} submitted request: {$reqNumber}", $req);

            // Notify supply officers
            $officers = User::role(['admin', 'supply-officer'])->get();
            foreach ($officers as $officer) {
                Notification::send($officer->id, 'new_request', 'New Guest Request',
                    "Guest {$data['guest_name']} submitted {$reqNumber}.",
                    'bi-cart-plus', route('requests.show', $req->id));
            }
        });

        return redirect()->route('public.track', ['query' => $reqNumber])
                         ->with('success', 'Your request has been submitted. Please save this tracking number!');
    }

    public function track(Request $request)
    {
        $query = $request->input('query');
        if (!$query) {
            return redirect()->route('home')->with('error', 'Please enter a tracking number.');
        }

        $query = strtoupper(trim($query));

        // Check if it's a Supply Request
        $supplyRequest = SupplyRequest::with(['requester', 'department', 'approver', 'issuer', 'items.inventoryItem.category'])
            ->where('request_number', $query)->first();
            
        if ($supplyRequest) {
            return view('public.track_request', ['request' => $supplyRequest]);
        }

        // Check if it's a Purchase Order
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items', 'deliveries'])
            ->where('po_number', $query)->first();

        if ($purchaseOrder) {
            return view('public.track_po', ['po' => $purchaseOrder]);
        }

        return redirect()->route('home')->with('error', "No record found for tracking number: {$query}");
    }

    public function claim(Request $request, $id)
    {
        $supplyRequest = SupplyRequest::findOrFail($id);

        if ($supplyRequest->status !== 'issued') {
            return back()->with('error', 'Request is not in a claimable state.');
        }

        $supplyRequest->update([
            'status'     => 'claimed',
            'claimed_at' => Carbon::now(),
        ]);

        ActivityLog::log('updated', 'request', "Guest claimed supplies for request {$supplyRequest->request_number}", $supplyRequest);

        return back()->with('success', 'Supplies claimed successfully.');
    }
}
