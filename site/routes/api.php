<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IpAddressController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuditEventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Middleware\SuperAdminMiddleware;

// Internal service-to-service endpoint for logging login/logout events (no auth required)
Route::post('/internal/audit-log', [AuditEventController::class, 'logEvent']);

// IP Address routes (all authenticated users)
Route::middleware(['auth:api'])->group(function () {
    Route::get('/ip-addresses', [IpAddressController::class, 'index']);
    Route::get('/ip-addresses/{id}', [IpAddressController::class, 'show']);
    Route::post('/ip-addresses', [IpAddressController::class, 'store']);
    Route::put('/ip-addresses/{id}', [IpAddressController::class, 'update']);
    Route::delete('/ip-addresses/{id}', [IpAddressController::class, 'destroy']);

    // Audit Log routes (super-admin only) – dashboard & all audit APIs
    Route::prefix('audit-logs')->middleware([SuperAdminMiddleware::class])->group(function () {
        Route::get('/', [AuditLogController::class, 'index']);
        Route::get('/dashboard', [AuditLogController::class, 'dashboard']);
        Route::get('/ip-address/{ipId}', [AuditLogController::class, 'getIpAddressLogs']);
        Route::get('/user/{userId}', [AuditLogController::class, 'getUserLogs']);
        Route::get('/session/{sessionId}', [AuditLogController::class, 'getSessionLogs']);
    });
});
