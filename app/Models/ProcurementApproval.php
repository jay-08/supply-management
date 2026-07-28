<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type', 'approvable_id', 'approver_id',
        'level', 'action', 'remarks', 'acted_at',
    ];

    protected $casts = ['acted_at' => 'datetime'];

    public function approvable() { return $this->morphTo(); }
    public function approver()   { return $this->belongsTo(User::class, 'approver_id'); }

    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            'returned' => '<span class="badge bg-warning text-dark">Returned</span>',
            'noted'    => '<span class="badge bg-secondary">Noted</span>',
            'sent'     => '<span class="badge bg-primary">Sent</span>',
            'received' => '<span class="badge bg-info"><i class="bi bi-box-arrow-in-down me-1"></i>Received</span>',
            'routed'   => '<span class="badge bg-info text-dark">Routed</span>',
            'legacy_migrated' => '<span class="badge bg-dark"><i class="bi bi-clock-history me-1"></i>Legacy Migrated</span>',
            default    => '<span class="badge bg-secondary">' . ucfirst($this->action) . '</span>',
        };
    }

    public function getLevelLabelAttribute(): string
    {
        return match($this->level) {
            'dept_head'         => 'Department Head',
            'supply_officer'    => 'Supply Officer',
            'budget_officer'    => 'Budget Officer',
            'accounting'        => 'Accounting',
            'assistant_regional_director' => 'Assistant Regional Director',
            'regional_director' => 'Regional Director',
            'admin'             => 'Administrator',
            default             => ucwords(str_replace('_', ' ', $this->level)),
        };
    }
}
