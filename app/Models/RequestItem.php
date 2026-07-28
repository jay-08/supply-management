<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_request_id', 'inventory_item_id',
        'quantity_requested', 'quantity_approved', 'quantity_issued', 'notes',
    ];

    public function supplyRequest()
    {
        return $this->belongsTo(SupplyRequest::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
