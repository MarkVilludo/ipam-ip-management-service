<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuditEventController extends Controller
{
    /**
     * Internal endpoint for logging events from other services
     * This endpoint should be protected by service-to-service authentication in production
     */
    public function logEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string',
            'user_id' => 'required|integer',
            'user_email' => 'required|string|email',
            'description' => 'nullable|string',
            'session_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        
        // Get or generate session ID
        $sessionId = $request->session_id ?? Str::uuid()->toString();

        activity()
            ->causedBy($user)
            ->withProperties([
                'session_id' => $sessionId,
                'user_email' => $request->user_email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log($request->description ?? "User {$request->user_email} performed {$request->action}");

        return response()->json([
            'success' => true,
            'message' => 'Event logged successfully'
        ]);
    }
}
