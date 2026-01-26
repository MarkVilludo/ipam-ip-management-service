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
    ];

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
