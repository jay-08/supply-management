<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number', 'supplier_id', 'created_by', 'inventory_item_id',
        'quantity', 'unit_cost', 'total_cost', 'purchase_date',
        'delivery_date', 'status', 'notes',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'delivery_date'  => 'date',
        'unit_cost'      => 'decimal:2',
        'total_cost'     => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public static function generatePoNumber(): string
    {
        $prefix = 'PO-' . date('Ym') . '-';
        $last   = self::where('po_number', 'like', $prefix . '%')
                      ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->po_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
