<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issuance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'issuance_number', 'supply_request_id', 'issued_to',
        'issued_by', 'department_id', 'remarks', 'issued_at',
    ];

    protected $casts = ['issued_at' => 'datetime'];

    /** Relations */
    public function supplyRequest()
    {
        return $this->belongsTo(SupplyRequest::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(IssuanceItem::class);
    }

    public function returns()
    {
        return $this->hasMany(ItemReturn::class);
    }

    /** Helpers */
    public static function generateIssuanceNumber(): string
    {
        $prefix = 'ISS-' . date('Ym') . '-';
        $last   = self::where('issuance_number', 'like', $prefix . '%')
                      ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->issuance_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalValueAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_cost);
    }
}
