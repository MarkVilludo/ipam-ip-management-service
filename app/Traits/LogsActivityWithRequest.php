<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Contracts\Activity;

trait LogsActivityWithRequest
{
    /**
     * Add request information to activity log
     * This ensures all activity logs include session tracking, IP address, and user agent
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $request = request();

        if ($request) {
            // Set IP address from request
            $activity->ip_address = $request->ip();

            // Set user agent from request
            $activity->user_agent = $request->userAgent();

            // Get or generate session ID for tracking activities within a session
            // Try to get session ID from request header first (for API calls)
            $sessionId = $request->header('X-Session-ID');

            if (!$sessionId) {
                // Try to get from Laravel session
                if (session()->isStarted() && session()->has('audit_session_id')) {
                    $sessionId = session('audit_session_id');
                } else {
                    // Generate a new session ID
                    $sessionId = Str::uuid()->toString();
                    if (session()->isStarted()) {
                        session(['audit_session_id' => $sessionId]);
                    }
                }
            }

            $activity->session_id = $sessionId;

            // Get user email from causer if available
            if ($activity->causer) {
                $activity->user_email = $activity->causer->email ?? null;
            } elseif ($request->user()) {
                // Fallback to authenticated user from request
                $activity->user_email = $request->user()->email ?? null;
            }
        }
    }
}
