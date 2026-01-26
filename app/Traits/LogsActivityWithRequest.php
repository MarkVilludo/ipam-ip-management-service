<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Contracts\Activity;

trait LogsActivityWithRequest
{
    /**
     * Add request information to activity log
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $request = request();

        if ($request) {
            $activity->ip_address = $request->ip();
            $activity->user_agent = $request->userAgent();

            // Get or generate session ID
            if (!session()->has('audit_session_id')) {
                session(['audit_session_id' => Str::uuid()->toString()]);
            }
            $activity->session_id = session('audit_session_id');

            // Get user email from causer if available
            if ($activity->causer) {
                $activity->user_email = $activity->causer->email ?? null;
            }
        }
    }
}
