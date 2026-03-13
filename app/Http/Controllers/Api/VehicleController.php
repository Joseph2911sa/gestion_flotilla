<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a paginated list of vehicles.
     * Supports optional filtering by status via ?status=available
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->paginate(10);

        return response()->json([
            'message' => 'Vehicles retrieved successfully.',
            'data' => $vehicles
        ]);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->only([
            'plate',
            'brand',
            'model',
            'year',
            'vehicle_type',
            'capacity',
            'fuel_type',
            'image_path',
            'status',
            'mileage'
        ]);

        $vehicle = Vehicle::create($data);

        return response()->json([
            'message' => 'Vehicle created successfully.',
            'data' => $vehicle
        ], 201);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json([
            'message' => 'Vehicle retrieved successfully.',
            'data' => $vehicle
        ]);
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $data = $request->only([
            'plate',
            'brand',
            'model',
            'year',
            'vehicle_type',
            'capacity',
            'fuel_type',
            'image_path',
            'status',
            'mileage'
        ]);

        $vehicle->update($data);

        return response()->json([
            'message' => 'Vehicle updated successfully.',
            'data' => $vehicle
        ]);
    }

    /**
     * Soft-delete the specified vehicle.
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully.',
            'data' => null
        ]);
    }
}