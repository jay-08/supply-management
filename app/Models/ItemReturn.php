<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_number', 'issuance_id', 'returned_by', 'received_by',
        'inventory_item_id', 'quantity', 'condition', 'reason', 'returned_at',
    ];

    protected $casts = ['returned_at' => 'datetime'];

    public function issuance()
    {
        return $this->belongsTo(Issuance::class);
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RET-' . date('Ym') . '-';
        $last   = self::where('return_number', 'like', $prefix . '%')
                      ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->return_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
