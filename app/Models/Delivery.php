<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'deliveries';

    protected $fillable = [
        'grn_number', 'purchase_order_id', 'received_by', 'inspected_by',
        'delivery_date', 'dr_number', 'invoice_number', 'status',
        'remarks', 'attachment', 'inventory_updated',
    ];

    protected $casts = [
        'delivery_date'     => 'date',
        'inventory_updated' => 'boolean',
    ];

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function receiver()      { return $this->belongsTo(User::class, 'received_by'); }
    public function inspector()     { return $this->belongsTo(User::class, 'inspected_by'); }
    public function items()         { return $this->hasMany(DeliveryItem::class); }

    public static function generateGrnNumber(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');
        $last  = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        return 'GRN-' . $year . $month . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'  => '<span class="badge bg-warning text-dark">Pending Inspection</span>',
            'partial'  => '<span class="badge bg-primary">Partial Delivery</span>',
            'complete' => '<span class="badge bg-success">Complete</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            default    => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }
}
