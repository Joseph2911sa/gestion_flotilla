<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\TripRequestController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Fleet Management System
|--------------------------------------------------------------------------
|
| All routes are versioned under /api/v1
|
*/

Route::prefix('v1')->group(function () {

    // =========================================================================
    // Authentication
    // =========================================================================
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);

        // =========================================================================
        // Vehicles
        // GET    /v1/vehicles              → index   (list, supports ?status=)
        // POST   /v1/vehicles              → store
        // GET    /v1/vehicles/{vehicle}    → show
        // PUT    /v1/vehicles/{vehicle}    → update
        // PATCH  /v1/vehicles/{vehicle}    → update
        // DELETE /v1/vehicles/{vehicle}    → destroy (soft delete)
        // =========================================================================
        Route::apiResource('vehicles', VehicleController::class);

        // =========================================================================
        // Routes (fleet routes: origin → destination)
        // GET    /v1/routes              → index
        // POST   /v1/routes              → store
        // GET    /v1/routes/{route}      → show
        // PUT    /v1/routes/{route}      → update
        // PATCH  /v1/routes/{route}      → update
        // DELETE /v1/routes/{route}      → destroy (soft delete)
        // =========================================================================
        Route::apiResource('routes', RouteController::class);

        // =========================================================================
        // Trip Requests
        // GET    /v1/trip-requests                          → index  (supports ?status=)
        // POST   /v1/trip-requests                          → store
        // GET    /v1/trip-requests/{tripRequest}             → show
        // PUT    /v1/trip-requests/{tripRequest}             → update
        // PATCH  /v1/trip-requests/{tripRequest}             → update
        // DELETE /v1/trip-requests/{tripRequest}             → destroy (soft delete)
        // ─── Custom actions ───────────────────────────────────────────────────────
        // PATCH  /v1/trip-requests/{tripRequest}/approve    → approve
        // PATCH  /v1/trip-requests/{tripRequest}/reject     → reject
        // PATCH  /v1/trip-requests/{tripRequest}/cancel     → cancel
        // =========================================================================
        Route::apiResource('trip-requests', TripRequestController::class);

        Route::patch(
            'trip-requests/{tripRequest}/approve',
            [TripRequestController::class, 'approve']
        )->name('trip-requests.approve');

        Route::patch(
            'trip-requests/{tripRequest}/reject',
            [TripRequestController::class, 'reject']
        )->name('trip-requests.reject');

        Route::patch(
            'trip-requests/{tripRequest}/cancel',
            [TripRequestController::class, 'cancel']
        )->name('trip-requests.cancel');

        // =========================================================================
        // Trips (departure & return)
        // GET    /v1/trips                         → index  (supports ?driver_id= / ?vehicle_id=)
        // POST   /v1/trips                         → store  (register departure)
        // GET    /v1/trips/{trip}                  → show
        // PUT    /v1/trips/{trip}                  → update
        // PATCH  /v1/trips/{trip}                  → update
        // DELETE /v1/trips/{trip}                  → destroy (soft delete)
        // ─── Custom actions ───────────────────────────────────────────────────────
        // PATCH  /v1/trips/{trip}/register-return  → registerReturn
        // =========================================================================
        Route::apiResource('trips', TripController::class);

        Route::patch(
            'trips/{trip}/register-return',
            [TripController::class, 'registerReturn']
        )->name('trips.register-return');

        // =========================================================================
        // Maintenances
        // GET    /v1/maintenances                        → index  (supports ?vehicle_id= / ?status=)
        // POST   /v1/maintenances                        → store  (open maintenance)
        // GET    /v1/maintenances/{maintenance}           → show
        // PUT    /v1/maintenances/{maintenance}           → update
        // PATCH  /v1/maintenances/{maintenance}           → update
        // DELETE /v1/maintenances/{maintenance}           → destroy (soft delete)
        // ─── Custom actions ───────────────────────────────────────────────────────
        // PATCH  /v1/maintenances/{maintenance}/close    → close
        // =========================================================================
        Route::apiResource('maintenances', MaintenanceController::class);

        Route::patch(
            'maintenances/{maintenance}/close',
            [MaintenanceController::class, 'close']
        )->name('maintenances.close');

    });

});