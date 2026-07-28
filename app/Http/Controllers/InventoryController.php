<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\InventoryItem;
use App\Models\InventoryHistory;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Notification;
use App\Models\ActivityLog;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with(['category', 'supplier'])
            ->where('status', '!=', 'discontinued');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('item_code', 'like', "%$search%")
                  ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%$search%"));
            });
        }
        if ($cat = $request->category_id) {
            $query->where('category_id', $cat);
        }
        if ($request->low_stock) {
            $query->whereColumn('quantity', '<=', 'reorder_level');
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $items      = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $suppliers  = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('inventory.index', compact('items', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_code'     => 'required|string|unique:inventory_items',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'unit'          => 'required|string|max:50',
            'quantity'      => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_cost'     => 'required|numeric|min:0',
            'location'      => 'nullable|string|max:100',
            'status'        => 'required|in:active,inactive',
            'image'         => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('inventory', 'public');
        }

        $qty = $data['quantity'];
        $item = InventoryItem::create($data);

        // Record initial stock history
        InventoryHistory::create([
            'inventory_item_id' => $item->id,
            'user_id'           => auth()->id(),
            'type'              => 'initial',
            'quantity'          => $qty,
            'quantity_before'   => 0,
            'quantity_after'    => $qty,
            'unit_cost'         => $item->unit_cost,
            'notes'             => 'Initial stock entry',
        ]);

        // Check low stock on creation
        if ($item->isLowStock()) {
            Notification::notifyLowStock($item);
        }

        ActivityLog::log('created', 'inventory', "Added inventory item: {$item->name}", $item);

        return redirect()->route('inventory.index')
            ->with('success', "Item \"{$item->name}\" added successfully.");
    }

    public function show(InventoryItem $inventory)
    {
        $inventory->load(['category', 'supplier']);
        $history = InventoryHistory::with('user')
            ->where('inventory_item_id', $inventory->id)
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('inventory.show', compact('inventory', 'history'));
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $data = $request->validate([
            'item_code'     => "required|string|unique:inventory_items,item_code,{$inventory->id}",
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'unit'          => 'required|string|max:50',
            'reorder_level' => 'required|integer|min:0',
            'unit_cost'     => 'required|numeric|min:0',
            'location'      => 'nullable|string|max:100',
            'status'        => 'required|in:active,inactive,discontinued',
            'image'         => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($inventory->image) Storage::disk('public')->delete($inventory->image);
            $data['image'] = $request->file('image')->store('inventory', 'public');
        }

        $inventory->update($data);
        ActivityLog::log('updated', 'inventory', "Updated inventory item: {$inventory->name}", $inventory);

        return redirect()->route('inventory.index')
            ->with('success', "Item \"{$inventory->name}\" updated.");
    }

    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();
        ActivityLog::log('deleted', 'inventory', "Deleted inventory item: {$inventory->name}", $inventory);
        return redirect()->route('inventory.index')->with('success', 'Item removed.');
    }

    public function history(int $id)
    {
        $item    = InventoryItem::findOrFail($id);
        $history = InventoryHistory::with('user')
            ->where('inventory_item_id', $id)
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('inventory.history', compact('item', 'history'));
    }

    public function adjustStock(Request $request, int $id)
    {
        $data = $request->validate([
            'type'     => 'required|in:stock_in,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string',
        ]);

        $item = InventoryItem::findOrFail($id);
        $qty  = $data['type'] === 'stock_in' ? abs($data['quantity']) : -abs($data['quantity']);

        if ($item->quantity + $qty < 0) {
            return back()->withErrors(['quantity' => 'Insufficient stock.']);
        }

        $item->adjustStock($qty, $data['type'], auth()->id(), $data['notes']);

        if ($item->fresh()->isLowStock()) {
            Notification::notifyLowStock($item->fresh());
        }

        $sign = $qty >= 0 ? '+' : '';
        ActivityLog::log('adjusted', 'inventory', "Stock adjusted for: {$item->name} ({$sign}{$qty})", $item);

        return back()->with('success', 'Stock adjusted successfully.');
    }

    public function barcode(int $id)
    {
        $item = InventoryItem::findOrFail($id);
        $code = $item->barcode ?: $item->item_code;

        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        // milon/barcode
        $dns1d     = new \DNS1D();
        $svg       = $dns1d->getBarcodeSVG($code, 'C128', 2, 60);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
