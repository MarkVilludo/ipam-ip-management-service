<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /**
     * Get audit logs with filtering
     * Only accessible by super-admins
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Super admin role required.'
            ], 403);
        }

        $query = Activity::query();

        // Filter by causer (user) ID
        if ($request->has('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', 'App\Models\User');
        }

        // Filter by subject type (entity type)
        if ($request->has('entity_type')) {
            $query->where('subject_type', $request->entity_type);
        }

        // Filter by subject ID (entity ID)
        if ($request->has('entity_id')) {
            $query->where('subject_id', $request->entity_id);
        }

        // Filter by event (action)
        if ($request->has('action')) {
            $query->where('event', $request->action);
        }

        // Filter by session ID
        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        // Filter by log name
        if ($request->has('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Pagination
        $perPage = $request->get('per_page', 50);
        $logs = $query->with(['subject', 'causer'])->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get audit logs for a specific IP address
     */
    public function getIpAddressLogs(Request $request, $ipId)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Super admin role required.'
            ], 403);
        }

        $logs = Activity::where('subject_type', 'App\Models\IpAddress')
            ->where('subject_id', $ipId)
            ->with(['subject', 'causer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get audit logs for a specific user
     */
    public function getUserLogs(Request $request, $userId)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Super admin role required.'
            ], 403);
        }

        $logs = Activity::where('causer_id', $userId)
            ->where('causer_type', 'App\Models\User')
            ->with(['subject', 'causer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get audit logs for a specific session
     */
    public function getSessionLogs(Request $request, $sessionId)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Super admin role required.'
            ], 403);
        }

        $logs = Activity::where('session_id', $sessionId)
            ->with(['subject', 'causer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get audit log statistics/dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Super admin role required.'
            ], 403);
        }

        $stats = [
            'total_logs' => Activity::count(),
            'logs_by_event' => Activity::select('event', DB::raw('count(*) as count'))
                ->whereNotNull('event')
                ->groupBy('event')
                ->get()
                ->pluck('count', 'event'),
            'logs_by_subject_type' => Activity::select('subject_type', DB::raw('count(*) as count'))
                ->whereNotNull('subject_type')
                ->groupBy('subject_type')
                ->get()
                ->pluck('count', 'subject_type'),
            'recent_logs' => Activity::with(['subject', 'causer'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'logs_by_user' => Activity::select('causer_id', 'user_email', DB::raw('count(*) as count'))
                ->where('causer_type', 'App\Models\User')
                ->whereNotNull('causer_id')
                ->groupBy('causer_id', 'user_email')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
