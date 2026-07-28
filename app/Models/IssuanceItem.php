<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'issuance_id', 'inventory_item_id', 'quantity', 'unit_cost', 'notes',
    ];

    protected $casts = ['unit_cost' => 'decimal:2'];

    public function issuance()
    {
        return $this->belongsTo(Issuance::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }
}
