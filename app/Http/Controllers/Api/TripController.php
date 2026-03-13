<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Trip::with([
            'driver:id,name,email',
            'vehicle:id,plate,brand,model',
            'route:id,name,origin,destination',
            'tripRequest:id,departure_date,return_date,status'
        ])->latest();

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $trips = $query->paginate(10);

        return response()->json([
            'message' => 'Trips retrieved successfully.',
            'data' => $trips
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->only([
            'trip_request_id',
            'vehicle_id',
            'driver_id',
            'route_id',
            'start_time',
            'start_mileage',
            'observations'
        ]);

        $trip = Trip::create($data);

        return response()->json([
            'message' => 'Trip created successfully.',
            'data' => $trip
        ], 201);
    }

    public function show(Trip $trip): JsonResponse
    {
        $trip->load([
            'driver:id,name,email',
            'vehicle:id,plate,brand,model,mileage',
            'route:id,name,origin,destination',
            'tripRequest'
        ]);

        return response()->json([
            'message' => 'Trip retrieved successfully.',
            'data' => $trip
        ]);
    }

    public function update(Request $request, Trip $trip): JsonResponse
    {
        $data = $request->only([
            'end_time',
            'end_mileage',
            'observations'
        ]);

        $trip->update($data);

        return response()->json([
            'message' => 'Trip updated successfully.',
            'data' => $trip
        ]);
    }

    public function destroy(Trip $trip): JsonResponse
    {
        $trip->delete();

        return response()->json([
            'message' => 'Trip deleted successfully.',
            'data' => null
        ]);
    }

    public function registerReturn(Request $request, Trip $trip): JsonResponse
    {
        $trip->update([
            'end_time' => $request->input('end_time', now()),
            'end_mileage' => $request->input('end_mileage'),
            'observations' => $request->input('observations')
        ]);

        return response()->json([
            'message' => 'Vehicle return registered successfully.',
            'data' => $trip
        ]);
    }
}