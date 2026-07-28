<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number', 'supplier_id', 'created_by',
        'po_date', 'delivery_date', 'payment_terms', 'delivery_address', 'notes',
        'subtotal', 'tax_rate', 'tax_amount', 'total_amount',
        'status', 'sent_at', 'cancelled_at', 'cancellation_reason', 'attachment',
    ];

    protected $casts = [
        'po_date'       => 'date',
        'delivery_date' => 'date',
        'sent_at'       => 'datetime',
        'cancelled_at'  => 'datetime',
        'subtotal'      => 'decimal:2',
        'tax_rate'      => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
    ];

    public function supplier()        { return $this->belongsTo(Supplier::class); }
    public function creator()         { return $this->belongsTo(User::class, 'created_by'); }
    public function items()           { return $this->hasMany(PurchaseOrderItem::class); }
    public function deliveries()      { return $this->hasMany(Delivery::class); }
    public function approvals()       { return $this->morphMany(ProcurementApproval::class, 'approvable'); }

    public static function generatePoNumber(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');
        $last  = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        return 'PO-' . $year . $month . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(float $taxRate = 0): void
    {
        $subtotal  = $this->items()->sum('total_price');
        $taxAmount = $subtotal * ($taxRate / 100);
        $this->update([
            'subtotal'     => $subtotal,
            'tax_rate'     => $taxRate,
            'tax_amount'   => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'                  => '<span class="badge bg-secondary">Draft</span>',
            'routed_to_budget'       => '<span class="badge bg-info text-dark">Routed to Budget Officer</span>',
            'received_by_budget'     => '<span class="badge bg-primary">Received by Budget Officer</span>',
            'returned_to_supply'     => '<span class="badge bg-secondary text-white">Returned to Supply Officer</span>',
            'budget_approved'        => '<span class="badge bg-primary">Budget Approved</span>',
            'routed_to_accounting'   => '<span class="badge bg-info text-dark">Routed to Accounting</span>',
            'received_by_accounting' => '<span class="badge bg-primary">Received by Accounting</span>',
            'returned_to_budget'     => '<span class="badge bg-secondary text-white">Returned to Budget Officer</span>',
            'accounting_approved'    => '<span class="badge bg-primary">Accounting Approved</span>',
            'routed_to_ard'          => '<span class="badge bg-info text-dark">Routed to ARD</span>',
            'received_by_ard'        => '<span class="badge bg-primary">Received by ARD</span>',
            'ard_approved'           => '<span class="badge bg-primary">ARD Approved</span>',
            'routed_to_rd'           => '<span class="badge bg-info text-dark">Routed to RD/ARD</span>',
            'received_by_rd'         => '<span class="badge bg-primary">Received by RD/ARD</span>',
            'returned_to_accounting' => '<span class="badge bg-secondary text-white">Returned to Accounting</span>',
            'sent'                   => '<span class="badge bg-info text-dark">Sent to Supplier</span>',
            'partially_delivered'    => '<span class="badge bg-primary">Partially Delivered</span>',
            'delivered'              => '<span class="badge bg-success">Delivered</span>',
            'cancelled'              => '<span class="badge bg-danger">Cancelled</span>',
            default                  => '<span class="badge bg-secondary">' . ucfirst(str_replace('_', ' ', $this->status)) . '</span>',
        };
    }

    public function getWorkflowProgressAttribute(): float
    {
        return match($this->status) {
            'draft'                  => 10,
            'pending'                => 20,
            'returned_to_supply'     => 20,
            'routed_to_budget'       => 25,
            'received_by_budget'     => 30,
            'budget_approved'        => 40,
            'returned_to_budget'     => 40,
            'routed_to_accounting'   => 45,
            'received_by_accounting' => 50,
            'accounting_approved'    => 60,
            'returned_to_accounting' => 60,
            'routed_to_ard'          => 65,
            'received_by_ard'        => 70,
            'ard_approved'           => 80,
            'routed_to_rd'           => 65,
            'received_by_rd'         => 70,
            'rd_approved'            => 80,
            'sent'                   => 90,
            'partially_delivered'    => 95,
            'delivered'              => 100,
            'cancelled'              => 0,
            default                  => 0,
        };
    }

    public function getStatusMessageAttribute(): string
    {
        return match($this->status) {
            'draft'                  => 'This PO is currently in Draft mode.',
            'pending'                => 'This PO is pending and ready for routing.',
            'routed_to_budget'       => 'This PO is routed to Budget Officer for approval.',
            'received_by_budget'     => 'This PO has been received by Budget Officer and is under review.',
            'budget_approved'        => 'This PO Budget has been approved. Ready to route to Accounting.',
            'returned_to_supply'     => 'This PO was returned to Supply Officer for review/revisions.',
            'routed_to_accounting'   => 'This PO is routed to Accounting for approval.',
            'received_by_accounting' => 'This PO has been received by Accounting and is under review.',
            'accounting_approved'    => 'This PO Accounting has been approved. Ready to route to RD/ARD.',
            'returned_to_budget'     => 'This PO was returned to Budget Officer for review.',
            'routed_to_ard'          => 'This PO is routed to Assistant Regional Director (ARD) for approval.',
            'received_by_ard'        => 'This PO has been received by Assistant Regional Director (ARD).',
            'ard_approved'           => 'This PO has been approved by ARD. Ready to send to supplier.',
            'routed_to_rd'           => 'This PO is routed to Regional Director (RD) for approval.',
            'received_by_rd'         => 'This PO has been received by Regional Director (RD).',
            'rd_approved'            => 'This PO has been approved by Regional Director (RD). Ready to send to supplier.',
            'returned_to_accounting' => 'This PO was returned to Accounting for review.',
            'sent'                   => 'This PO has been sent to the Supplier.',
            'partially_delivered'    => 'This PO items have been partially delivered.',
            'delivered'              => 'This PO has been fully delivered.',
            'cancelled'              => 'This Purchase Order has been cancelled.' . ($this->cancellation_reason ? ' Reason: ' . $this->cancellation_reason : ''),
            default                  => 'Status: ' . ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getPublicStatusMessageAttribute(): string
    {
        return match($this->status) {
            'draft'                  => 'Your PO is currently in Draft mode.',
            'pending'                => 'Your PO is pending and ready for routing.',
            'routed_to_budget'       => 'Your PO is routed to Budget Officer for approval.',
            'received_by_budget'     => 'Your PO has been received by Budget Officer and is under review.',
            'budget_approved'        => 'Your PO Budget has been approved. Ready to route to Accounting.',
            'returned_to_supply'     => 'Your PO was returned to Supply Officer for review/revisions.',
            'routed_to_accounting'   => 'Your PO is routed to Accounting for approval.',
            'received_by_accounting' => 'Your PO has been received by Accounting and is under review.',
            'accounting_approved'    => 'Your PO Accounting has been approved. Ready to route to RD/ARD.',
            'returned_to_budget'     => 'Your PO was returned to Budget Officer for review.',
            'routed_to_ard'          => 'Your PO is routed to Assistant Regional Director (ARD) for approval.',
            'received_by_ard'        => 'Your PO has been received by Assistant Regional Director (ARD).',
            'ard_approved'           => 'Your PO has been approved by ARD. Ready to send to supplier.',
            'routed_to_rd'           => 'Your PO is routed to Regional Director (RD) for approval.',
            'received_by_rd'         => 'Your PO has been received by Regional Director (RD).',
            'rd_approved'            => 'Your PO has been approved by Regional Director (RD). Ready to send to supplier.',
            'returned_to_accounting' => 'Your PO was returned to Accounting for review.',
            'sent'                   => 'Your PO has been sent to the Supplier.',
            'partially_delivered'    => 'Your PO items have been partially delivered.',
            'delivered'              => 'Your PO has been fully delivered.',
            'cancelled'              => 'This Purchase Order has been cancelled.' . ($this->cancellation_reason ? ' Reason: ' . $this->cancellation_reason : ''),
            default                  => 'Status: ' . ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusAlertTypeAttribute(): string
    {
        return match($this->status) {
            'draft', 'pending'       => 'secondary',
            'routed_to_budget', 'routed_to_accounting', 'routed_to_ard', 'routed_to_rd' => 'info',
            'received_by_budget', 'received_by_accounting', 'received_by_ard', 'received_by_rd' => 'warning',
            'returned_to_supply', 'returned_to_budget', 'returned_to_accounting' => 'warning',
            'budget_approved', 'accounting_approved', 'ard_approved', 'rd_approved', 'sent', 'partially_delivered', 'delivered' => 'success',
            'cancelled'              => 'danger',
            default                  => 'info',
        };
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment) {
            return null;
        }
        if (str_starts_with($this->attachment, 'http') || str_starts_with($this->attachment, 'data:')) {
            return $this->attachment;
        }
        return asset('storage/' . $this->attachment);
    }
}
