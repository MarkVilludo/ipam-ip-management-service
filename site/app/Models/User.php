<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id', // We'll sync user IDs from auth service
        'name',
        'email',
        'role',
    ];

    /**
     * For testing: allow generating a JWT for this user so feature tests can call the API.
     * In production, tokens are issued by the auth service.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role ?? 'user',
        ];
    }

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
