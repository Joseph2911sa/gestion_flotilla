<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\TripRequestController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleAvailabilityController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);

        // Reports
        Route::get('reports/vehicle-availability', [ReportController::class, 'vehicleAvailability'])
            ->middleware('role:Admin,Operador,Chofer');

        Route::get('reports/fleet-usage', [ReportController::class, 'fleetUsage'])
            ->middleware('role:Admin,Operador');

        Route::get('reports/driver-history', [ReportController::class, 'driverHistory'])
            ->middleware('role:Admin,Operador');

        // Users
        // GET index y show: Admin y Operador (el Operador necesita listar choferes)
        Route::get('users',       [UserController::class, 'index'])->middleware('role:Admin,Operador');
        Route::get('users/{user}',[UserController::class, 'show'])->middleware('role:Admin,Operador');
        // Crear, actualizar y eliminar: solo Admin
        Route::post(  'users',         [UserController::class, 'store'])  ->middleware('role:Admin');
        Route::put(   'users/{user}',   [UserController::class, 'update']) ->middleware('role:Admin');
        Route::patch( 'users/{user}',   [UserController::class, 'update']) ->middleware('role:Admin');
        Route::delete('users/{user}',   [UserController::class, 'destroy'])->middleware('role:Admin');

        // Vehicles
        Route::get('vehicles', [VehicleController::class, 'index'])
            ->middleware('role:Admin,Operador,Chofer');

        Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])
            ->middleware('role:Admin,Operador,Chofer');

        Route::post('vehicles', [VehicleController::class, 'store'])
            ->middleware('role:Admin,Operador');

        Route::match(['put', 'patch'], 'vehicles/{vehicle}', [VehicleController::class, 'update'])
            ->middleware('role:Admin,Operador');

        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])
            ->middleware('role:Admin,Operador');

        // Fleet Routes
        Route::apiResource('routes', RouteController::class)
            ->middleware('role:Admin,Operador');

        // ── Trip Requests ─────────────────────────────────────────────────────

        // Tarjeta 15 — Asignación directa
        Route::post(
            'trip-requests/direct-assign',
            [TripRequestController::class, 'directAssign']
        )->middleware('role:Admin,Operador')
         ->name('trip-requests.direct-assign');

        Route::apiResource('trip-requests', TripRequestController::class)
            ->middleware('role:Admin,Operador,Chofer');

        Route::patch(
            'trip-requests/{tripRequest}/approve',
            [TripRequestController::class, 'approve']
        )->middleware('role:Admin,Operador')
         ->name('trip-requests.approve');

        Route::patch(
            'trip-requests/{tripRequest}/reject',
            [TripRequestController::class, 'reject']
        )->middleware('role:Admin,Operador')
         ->name('trip-requests.reject');

        Route::patch(
            'trip-requests/{tripRequest}/cancel',
            [TripRequestController::class, 'cancel']
        )->middleware('role:Admin,Operador,Chofer')
         ->name('trip-requests.cancel');

        // Tarjeta 24 — Función BD
        Route::get(
            'vehicles/{id}/availability',
            [VehicleAvailabilityController::class, 'checkAvailability']
        )->middleware('role:Admin,Operador,Chofer')
         ->name('vehicles.availability');

        // Tarjeta 23 — Procedimiento almacenado
        Route::post(
            'trip-requests/{id}/approve-db',
            [VehicleAvailabilityController::class, 'approveViaDb']
        )->middleware('role:Admin,Operador')
         ->name('trip-requests.approve-db');

        // Trips
        Route::apiResource('trips', TripController::class)
            ->middleware('role:Admin,Operador');

        Route::patch(
            'trips/{trip}/register-return',
            [TripController::class, 'registerReturn']
        )->middleware('role:Chofer,Admin,Operador')
         ->name('trips.register-return');

        // ── Maintenances ──────────────────────────────────────────────────────

        Route::apiResource('maintenances', MaintenanceController::class)
            ->middleware('role:Admin,Operador');

        Route::patch(
            'maintenances/{maintenance}/close',
            [MaintenanceController::class, 'close']
        )->middleware('role:Admin,Operador')
         ->name('maintenances.close');

    });

});