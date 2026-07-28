<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_code', 'name', 'description', 'category_id', 'supplier_id',
        'unit', 'quantity', 'reorder_level', 'unit_cost', 'location',
        'image', 'barcode', 'status',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'quantity'  => 'integer',
        'reorder_level' => 'integer',
    ];

    /** Relations */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryHistory()
    {
        return $this->hasMany(InventoryHistory::class);
    }

    public function requestItems()
    {
        return $this->hasMany(RequestItem::class);
    }

    public function issuanceItems()
    {
        return $this->hasMany(IssuanceItem::class);
    }

    public function purchaseRecords()
    {
        return $this->hasMany(PurchaseRecord::class);
    }

    /** Helpers */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('images/no-image.png');
        }
        if (str_starts_with($this->image, 'data:') || str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'       => '<span class="badge bg-success">Active</span>',
            'inactive'     => '<span class="badge bg-secondary">Inactive</span>',
            'discontinued' => '<span class="badge bg-danger">Discontinued</span>',
            default        => '<span class="badge bg-light text-dark">' . $this->status . '</span>',
        };
    }

    public function getStockBadgeAttribute(): string
    {
        if ($this->quantity === 0) {
            return '<span class="badge bg-danger">Out of Stock</span>';
        }
        if ($this->isLowStock()) {
            return '<span class="badge bg-warning text-dark">Low Stock</span>';
        }
        return '<span class="badge bg-success">In Stock</span>';
    }

    /**
     * Adjust stock with history tracking.
     */
    public function adjustStock(int $quantity, string $type, int $userId = null, string $notes = null, string $refType = null, int $refId = null): void
    {
        $before = $this->quantity;
        $this->increment('quantity', $quantity);
        $after = $this->fresh()->quantity;

        InventoryHistory::create([
            'inventory_item_id' => $this->id,
            'user_id'           => $userId ?? auth()->id(),
            'type'              => $type,
            'quantity'          => $quantity,
            'quantity_before'   => $before,
            'quantity_after'    => $after,
            'unit_cost'         => $this->unit_cost,
            'reference_type'    => $refType,
            'reference_id'      => $refId,
            'notes'             => $notes,
        ]);
    }
}
