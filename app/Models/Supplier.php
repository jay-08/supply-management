<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'contact_person', 'email', 'phone',
        'address', 'city', 'website', 'notes', 'status',
    ];

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function purchaseRecords()
    {
        return $this->hasMany(PurchaseRecord::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }
}
