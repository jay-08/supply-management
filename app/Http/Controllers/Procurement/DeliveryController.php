<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\InventoryItem;
use App\Models\ActivityLog;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['purchaseOrder.supplier', 'receiver']);
        if ($s = $request->status) { $query->where('status', $s); }
        $deliveries = $query->orderByDesc('delivery_date')->paginate(15)->withQueryString();
        return view('procurement.deliveries.index', compact('deliveries'));
    }

    public function create(Request $request)
    {
        $pos = PurchaseOrder::with(['supplier'])
            ->whereIn('status', ['sent', 'partially_delivered'])
            ->orderByDesc('po_date')->get();
        $selectedPO = $request->po_id ? PurchaseOrder::find($request->po_id) : null;
        $inventoryItems = InventoryItem::where('status', 'active')->orderBy('name')->get();
        return view('procurement.deliveries.create', compact('pos', 'selectedPO', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'delivery_date'     => 'required|date',
            'dr_number'         => 'nullable|string|max:50',
            'invoice_number'    => 'nullable|string|max:50',
            'inspected_by'      => 'nullable|exists:users,id',
            'remarks'           => 'nullable|string',
            'is_final_delivery' => 'nullable|boolean',
            'items'             => 'required|array|min:1',
            'items.*.inventory_item_id'      => 'required|exists:inventory_items,id',
            'items.*.quantity_delivered'     => 'required|numeric|min:0.01',
            'items.*.quantity_accepted'      => 'required|numeric|min:0',
            'items.*.condition'              => 'required|in:good,damaged,defective,expired',
            'items.*.remarks'                => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $request) {
            $po = PurchaseOrder::findOrFail($data['purchase_order_id']);

            $delivery = Delivery::create([
                'grn_number'        => Delivery::generateGrnNumber(),
                'purchase_order_id' => $po->id,
                'received_by'       => auth()->id(),
                'inspected_by'      => $data['inspected_by'] ?? null,
                'delivery_date'     => $data['delivery_date'],
                'dr_number'         => $data['dr_number'] ?? null,
                'invoice_number'    => $data['invoice_number'] ?? null,
                'remarks'           => $data['remarks'] ?? null,
                'status'            => 'complete',
                'inventory_updated' => true,
                'attachment'        => $request->hasFile('attachment')
                    ? $request->file('attachment')->store('deliveries', 'public')
                    : null,
            ]);

            foreach ($data['items'] as $item) {
                $qtyRejected = $item['quantity_delivered'] - $item['quantity_accepted'];

                DeliveryItem::create([
                    'delivery_id'            => $delivery->id,
                    'purchase_order_item_id' => null, // No longer tracked
                    'inventory_item_id'      => $item['inventory_item_id'],
                    'quantity_delivered'     => $item['quantity_delivered'],
                    'quantity_accepted'      => $item['quantity_accepted'],
                    'quantity_rejected'      => max(0, $qtyRejected),
                    'condition'              => $item['condition'],
                    'remarks'                => $item['remarks'] ?? null,
                ]);

                if ($item['condition'] === 'good' && $item['quantity_accepted'] > 0) {
                    $invItem = InventoryItem::find($item['inventory_item_id']);
                    if ($invItem) {
                        $invItem->adjustStock(
                            $item['quantity_accepted'], 'stock_in', auth()->id(),
                            "Received from PO {$po->po_number} / GRN {$delivery->grn_number}",
                            'App\Models\Delivery', $delivery->id
                        );
                    }
                }
            }

            $isFinal = $request->boolean('is_final_delivery');
            $po->update(['status' => $isFinal ? 'delivered' : 'partially_delivered']);

            ActivityLog::log('received', 'procurement', "GRN created: {$delivery->grn_number}", $delivery);
        });

        return redirect()->route('procurement.deliveries.index')
            ->with('success', 'Delivery recorded and inventory updated successfully.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['purchaseOrder.supplier', 'items.inventoryItem', 'receiver', 'inspector']);
        return view('procurement.deliveries.show', compact('delivery'));
    }
}
