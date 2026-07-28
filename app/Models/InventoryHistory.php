<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryHistory extends Model
{
    use HasFactory;

    protected $table = 'inventory_history';

    protected $fillable = [
        'inventory_item_id', 'user_id', 'type', 'quantity',
        'quantity_before', 'quantity_after', 'unit_cost',
        'reference_type', 'reference_id', 'notes',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'stock_in'   => '<span class="badge bg-success">Stock In</span>',
            'stock_out'  => '<span class="badge bg-danger">Stock Out</span>',
            'adjustment' => '<span class="badge bg-warning text-dark">Adjustment</span>',
            'return'     => '<span class="badge bg-info">Return</span>',
            'initial'    => '<span class="badge bg-secondary">Initial</span>',
            default      => '<span class="badge bg-light text-dark">' . $this->type . '</span>',
        };
    }
}
