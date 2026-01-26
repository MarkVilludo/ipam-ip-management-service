<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id', // We'll sync user IDs from auth service
        'name',
        'email',
        'role',
    ];

    protected $hidden = [];

    protected function casts(): array
    {
        return [];
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Get all IP addresses created by this user
     */
    public function ipAddresses()
    {
        return $this->hasMany(IpAddress::class, 'created_by');
    }

    /**
     * Get all activity logs for this user (as causer)
     */
    public function activities()
    {
        return $this->morphMany(Activity::class, 'causer');
    }
}
