<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'icon', 'link', 'is_read',
    ];

    protected $casts = ['is_read' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Send a notification to a user.
     */
    public static function send(?int $userId, string $type, string $title, string $message, string $icon = null, string $link = null): void
    {
        if (!$userId) {
            return;
        }

        self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'icon'    => $icon ?? 'bi-bell',
            'link'    => $link,
        ]);
    }

    /**
     * Send low-stock notification to all supply officers and admins.
     */
    public static function notifyLowStock(InventoryItem $item): void
    {
        $officers = User::role(['admin', 'supply-officer'])->get();
        foreach ($officers as $user) {
            self::send(
                $user->id,
                'low_stock',
                'Low Stock Alert',
                "Item \"{$item->name}\" is running low (Qty: {$item->quantity}).",
                'bi-exclamation-triangle-fill',
                route('inventory.show', $item->id)
            );
        }
    }
}
