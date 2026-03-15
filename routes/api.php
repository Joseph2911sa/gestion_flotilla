<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\TripRequestController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);

        // Users
        Route::apiResource('users', UserController::class)
            ->middleware('role:Admin');

        // Vehicles
        Route::apiResource('vehicles', VehicleController::class)
            ->middleware('role:Admin,Operador');

        // Fleet Routes
        Route::apiResource('routes', RouteController::class)
            ->middleware('role:Admin,Operador');

        // Trip Requests
        Route::apiResource('trip-requests', TripRequestController::class)
            ->middleware('role:Admin,Operador');

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
        )->middleware('role:Admin,Operador')
         ->name('trip-requests.cancel');

        // Trips
        Route::apiResource('trips', TripController::class)
            ->middleware('role:Admin,Operador');

        Route::patch(
            'trips/{trip}/register-return',
            [TripController::class, 'registerReturn']
        )->middleware('role:Chofer,Admin')
         ->name('trips.register-return');

        // Maintenances
        Route::apiResource('maintenances', MaintenanceController::class)
            ->middleware('role:Admin,Operador');

        Route::patch(
            'maintenances/{maintenance}/close',
            [MaintenanceController::class, 'close']
        )->middleware('role:Admin,Operador')
         ->name('maintenances.close');

    });

});