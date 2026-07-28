<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'inventory_item_id',
        'item_name', 'specifications', 'unit',
        'quantity_ordered', 'quantity_received', 'unit_price', 'notes',
    ];

    protected $casts = [
        'quantity_ordered'  => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_price'        => 'decimal:2',
    ];

    public function purchaseOrder()       { return $this->belongsTo(PurchaseOrder::class); }
    public function inventoryItem()       { return $this->belongsTo(InventoryItem::class); }
    public function deliveryItems()       { return $this->hasMany(DeliveryItem::class); }

    public function getRemainingAttribute(): float
    {
        return (float)$this->quantity_ordered - (float)$this->quantity_received;
    }
}
