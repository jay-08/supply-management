<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ItemReturn;
use App\Models\Issuance;
use App\Models\InventoryItem;
use App\Models\Notification;
use App\Models\ActivityLog;

class ReturnController extends Controller
{
    public function index()
    {
        $returns = ItemReturn::with(['issuance', 'inventoryItem', 'returnedBy', 'receivedBy'])
            ->orderByDesc('returned_at')->paginate(15);
        return view('returns.index', compact('returns'));
    }

    public function create()
    {
        $issuances = Issuance::with('recipient', 'items.inventoryItem')
            ->orderByDesc('issued_at')->get();
        return view('returns.create', compact('issuances'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'issuance_id'       => 'required|exists:issuances,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity'          => 'required|integer|min:1',
            'condition'         => 'required|in:good,damaged,defective',
            'reason'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $ret = ItemReturn::create([
                'return_number'     => ItemReturn::generateReturnNumber(),
                'issuance_id'       => $data['issuance_id'],
                'returned_by'       => auth()->id(),
                'received_by'       => auth()->id(),
                'inventory_item_id' => $data['inventory_item_id'],
                'quantity'          => $data['quantity'],
                'condition'         => $data['condition'],
                'reason'            => $data['reason'] ?? null,
                'returned_at'       => now(),
            ]);

            // Restore stock if item is in good condition
            if ($data['condition'] === 'good') {
                $item = InventoryItem::findOrFail($data['inventory_item_id']);
                $item->adjustStock($data['quantity'], 'return', auth()->id(),
                    "Return: {$ret->return_number}", 'App\Models\ItemReturn', $ret->id);
            }

            ActivityLog::log('created', 'return', "Processed return: {$ret->return_number}", $ret);
        });

        return redirect()->route('returns.index')->with('success', 'Return processed successfully.');
    }

    public function show(int $id)
    {
        $return = ItemReturn::with(['issuance', 'inventoryItem', 'returnedBy', 'receivedBy'])->findOrFail($id);
        return view('returns.show', compact('return'));
    }
}
