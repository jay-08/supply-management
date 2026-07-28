<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id', 'purchase_order_item_id', 'inventory_item_id',
        'quantity_delivered', 'quantity_accepted', 'quantity_rejected',
        'condition', 'remarks',
    ];

    protected $casts = [
        'quantity_delivered' => 'decimal:2',
        'quantity_accepted'  => 'decimal:2',
        'quantity_rejected'  => 'decimal:2',
    ];

    public function delivery()          { return $this->belongsTo(Delivery::class); }
    public function purchaseOrderItem() { return $this->belongsTo(PurchaseOrderItem::class); }
    public function inventoryItem()     { return $this->belongsTo(InventoryItem::class); }
}
