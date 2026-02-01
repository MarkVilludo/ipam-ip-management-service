<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuditEventController extends Controller
{
    /**
     * Internal endpoint for logging events from other services (e.g. auth service).
     * Records login/logout and other user events in the central audit log.
     * Should be protected by service-to-service authentication in production.
     */
    public function logEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string',
            'user_id' => 'required|integer',
            'user_email' => 'required|string|email',
            'description' => 'nullable|string',
            'session_id' => 'nullable|string',
            'ip_address' => 'nullable|string',
            'user_agent' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $sessionId = $request->session_id ?? Str::uuid()->toString();
        $activity = null;

        try {
            // Sync user so they exist in IP management service (same as SyncUserMiddleware)
            // This fixes "User not found" when auth service logs login/logout before user has called any IP API
            $user = User::updateOrCreate(
                ['id' => $request->user_id],
                [
                    'email' => $request->user_email,
                    'name' => $request->name ?? 'Unknown',
                    'role' => $request->role ?? 'user',
                ]
            );

            $event = in_array(strtolower($request->action), ['login', 'logout'])
                ? strtolower($request->action)
                : $request->action;

            $activity = activity()
                ->causedBy($user)
                ->event($event)
                ->withProperties([
                    'session_id' => $sessionId,
                    'user_email' => $request->user_email,
                    'ip_address' => $request->ip_address ?? $request->ip(),
                    'user_agent' => $request->user_agent ?? $request->userAgent(),
                ])
                ->log($request->description ?? "User {$request->user_email} {$request->action}");

            if ($activity) {
                $activity->session_id = $sessionId;
                $activity->user_email = $request->user_email;
                $activity->ip_address = $request->ip_address ?? $request->ip();
                $activity->user_agent = $request->user_agent ?? $request->userAgent();
                $activity->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Event logged successfully',
                'data' => [
                    'activity_id' => $activity->id ?? null,
                    'session_id' => $sessionId,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Audit log internal error', [
                'action' => $request->action,
                'user_id' => $request->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to log audit event',
            ], 500);
        }
    }
}
