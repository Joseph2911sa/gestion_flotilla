<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'success' => true,
            'message' => 'Vehicles retrieved successfully.',
            'data'    => $vehicles,
        ]);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('vehicles', 'public');
        }

        // Remove 'image' key — not a DB column
        unset($data['image']);

        $vehicle = Vehicle::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully.',
            'data'    => $vehicle,
        ], 201);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Vehicle retrieved successfully.',
            'data'    => $vehicle,
        ]);
    }

    /**
     * Update the specified vehicle.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $data = $request->validated();

        // Handle image replacement
        if ($request->hasFile('image')) {
            // Delete previous image if it exists
            if ($vehicle->image_path) {
                Storage::disk('public')->delete($vehicle->image_path);
            }
            $data['image_path'] = $request->file('image')->store('vehicles', 'public');
        }

        // Remove 'image' key — not a DB column
        unset($data['image']);

        $vehicle->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully.',
            'data'    => $vehicle->fresh(),
        ]);
    }

    /**
     * Soft-delete the specified vehicle.
     *
     * Blocked if:
     * - there are active trip requests (pending or approved)
     * - there are open maintenances
     *
     * NOT blocked by:
     * - historical trips (completed records, soft-delete does not corrupt history)
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        // Block: active trip requests that represent a current commitment
        $activeRequests = $vehicle->tripRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($activeRequests) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this vehicle. It has pending or approved trip requests.',
                'data'    => null,
            ], 422);
        }

        // Block: open maintenances indicate the vehicle is currently in a service process
        $openMaintenance = $vehicle->openMaintenances()->exists();

        if ($openMaintenance) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this vehicle. It has open maintenance records.',
                'data'    => null,
            ], 422);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully.',
            'data'    => null,
        ]);
    }
}