<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\PurchaseRecord;
use App\Models\ActivityLog;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('inventoryItems', 'purchaseRecords');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%")
                  ->orWhere('contact_person', 'like', "%$search%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'code'           => 'required|string|max:20|unique:suppliers',
            'contact_person' => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:100',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'website'        => 'nullable|url|max:200',
            'notes'          => 'nullable|string',
        ]);

        $supplier = Supplier::create($data);

        ActivityLog::log('created', 'supplier', "Added supplier: {$supplier->name}", $supplier);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$supplier->name}\" added successfully.");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('inventoryItems');

        $purchases = PurchaseRecord::with('inventoryItem', 'creator')
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('purchase_date')
            ->paginate(10);

        return view('suppliers.show', compact('supplier', 'purchases'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'code'           => "required|string|max:20|unique:suppliers,code,{$supplier->id}",
            'contact_person' => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:100',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'website'        => 'nullable|url|max:200',
            'notes'          => 'nullable|string',
            'status'         => 'required|in:active,inactive',
        ]);

        $supplier->update($data);

        ActivityLog::log('updated', 'supplier', "Updated supplier: {$supplier->name}", $supplier);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$supplier->name}\" updated.");
    }

    public function destroy(Supplier $supplier)
    {
        ActivityLog::log('deleted', 'supplier', "Deleted supplier: {$supplier->name}", $supplier);
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier removed.');
    }

    public function purchaseHistory(int $id)
    {
        $supplier  = Supplier::findOrFail($id);
        $purchases = PurchaseRecord::with('inventoryItem', 'creator')
            ->where('supplier_id', $id)
            ->orderByDesc('purchase_date')
            ->paginate(15);

        return view('suppliers.history', compact('supplier', 'purchases'));
    }
}
