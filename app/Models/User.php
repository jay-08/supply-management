<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'avatar',
        'department_id', 'position', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /** Relations */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function supplyRequests()
    {
        return $this->hasMany(SupplyRequest::class, 'requester_id');
    }

    public function issuancesReceived()
    {
        return $this->hasMany(Issuance::class, 'issued_to');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /** Helpers */
    public function getAvatarUrlAttribute(): string
    {
        if (!$this->avatar) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=2563eb&color=fff&size=40';
        }
        if (str_starts_with($this->avatar, 'data:') || str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }
        return asset('storage/' . $this->avatar);
    }

    public function getRoleNameAttribute(): string
    {
        return $this->getRoleNames()->first() ?? 'No Role';
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}
