<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'causer_type',
        'causer_id',
        'properties',
        'session_id',
        'user_email',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'collection',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all activities for a specific IP address (lifetime)
     */
    public static function forIpAddress($ipId)
    {
        return static::where('subject_type', 'App\Models\IpAddress')
            ->where('subject_id', $ipId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all activities for a specific IP address within a session
     */
    public static function forIpAddressInSession($ipId, $sessionId)
    {
        return static::where('subject_type', 'App\Models\IpAddress')
            ->where('subject_id', $ipId)
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all activities for a specific user (lifetime)
     */
    public static function forUser($userId)
    {
        return static::where('causer_type', 'App\Models\User')
            ->where('causer_id', $userId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all activities for a specific user within a session
     */
    public static function forUserInSession($userId, $sessionId)
    {
        return static::where('causer_type', 'App\Models\User')
            ->where('causer_id', $userId)
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get login/logout events
     */
    public static function loginLogoutEvents()
    {
        return static::whereIn('event', ['login', 'logout'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Prevent deletion of audit logs
     */
    public function delete()
    {
        throw new \Exception('Audit logs cannot be deleted');
    }

    /**
     * Prevent force deletion of audit logs
     */
    public function forceDelete()
    {
        throw new \Exception('Audit logs cannot be deleted');
    }
}
