<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplyRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number', 'requester_id', 'department_id', 'approved_by',
        'issued_by', 'status', 'purpose', 'remarks', 'rejection_reason',
        'approved_at', 'issued_at', 'claimed_at', 'needed_by', 'guest_name'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'issued_at'   => 'datetime',
        'claimed_at'  => 'datetime',
        'needed_by'   => 'datetime',
    ];

    /** Relations */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items()
    {
        return $this->hasMany(RequestItem::class);
    }

    public function issuances()
    {
        return $this->hasMany(Issuance::class);
    }

    /** Helpers */
    public static function generateRequestNumber(): string
    {
        $prefix = 'REQ-' . date('Ym') . '-';
        $last   = self::where('request_number', 'like', $prefix . '%')
                      ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->request_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getWorkflowProgressAttribute(): int
    {
        return match ($this->status) {
            'pending'          => 20,
            'approved'         => 60,
            'issued'           => 100,
            'claimed'          => 100,
            'partially_issued' => 80,
            'returned'         => 100,
            'rejected'         => 100,
            'cancelled'        => 100,
            default            => 0,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'          => '<span class="badge bg-warning text-dark">Pending</span>',
            'approved'         => '<span class="badge bg-primary">Approved</span>',
            'issued'           => '<span class="badge bg-success">Issued</span>',
            'claimed'          => '<span class="badge bg-success">Claimed</span>',
            'partially_issued' => '<span class="badge bg-info text-dark">Partially Issued</span>',
            'rejected'         => '<span class="badge bg-danger">Rejected</span>',
            'cancelled'        => '<span class="badge bg-secondary">Cancelled</span>',
            default            => '<span class="badge bg-light text-dark">' . $this->status . '</span>',
        };
    }

    public function getRequesterNameAttribute(): string
    {
        return $this->requester ? $this->requester->name : ($this->guest_name ?? 'Guest User');
    }

    public function getStatusMessageAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'This request is currently pending for approval by the Supply Officer.',
            'approved'         => 'This request has been approved and is awaiting supply issuance.',
            'issued'           => 'Supplies for this request have been issued and are ready to be claimed.',
            'claimed'          => 'Supplies for this request have been successfully claimed.',
            'partially_issued' => 'Supplies for this request have been partially issued.',
            'rejected'         => 'This request was rejected.' . ($this->rejection_reason ? ' Reason: ' . $this->rejection_reason : ''),
            'cancelled'        => 'This request has been cancelled.',
            default            => 'Status: ' . ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getPublicStatusMessageAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'Your request is currently pending for approval by the Supply Officer.',
            'approved'         => 'Your request has been approved and is awaiting supply issuance.',
            'issued'           => 'Supplies for your request have been issued and are ready to be claimed.',
            'claimed'          => 'Supplies have been successfully claimed.',
            'partially_issued' => 'Supplies for your request have been partially issued.',
            'rejected'         => 'Your request was rejected.' . ($this->rejection_reason ? ' Reason: ' . $this->rejection_reason : ''),
            'cancelled'        => 'This request has been cancelled.',
            default            => 'Status: ' . ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusAlertTypeAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'warning',
            'approved'         => 'info',
            'issued', 'claimed'=> 'success',
            'partially_issued' => 'info',
            'rejected'         => 'danger',
            'cancelled'        => 'secondary',
            default            => 'info',
        };
    }
}
