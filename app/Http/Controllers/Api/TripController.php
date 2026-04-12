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
            'data'    => $trips,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'trip_request_id' => 'required|integer|exists:trip_requests,id',
            'vehicle_id'      => 'required|integer|exists:vehicles,id',
            'driver_id'       => 'required|integer|exists:users,id',
            'start_time'      => 'required|date',
            'start_mileage'   => 'required|integer|min:0',
        ], [
            'trip_request_id.required' => 'La solicitud es obligatoria.',
            'vehicle_id.required'      => 'El vehiculo es obligatorio.',
            'driver_id.required'       => 'El chofer es obligatorio.',
            'start_time.required'      => 'La fecha/hora de salida es obligatoria.',
            'start_mileage.required'   => 'El kilometraje inicial es obligatorio.',
        ]);

        // Bloquear si el chofer ya tiene un viaje en curso sin retorno
        $viajeEnCurso = Trip::where('driver_id', $request->driver_id)
            ->whereNull('end_time')
            ->exists();

        if ($viajeEnCurso) {
            return response()->json([
                'message' => 'El chofer ya tiene un viaje en curso. Registre el retorno antes de iniciar uno nuevo.',
            ], 422);
        }

        // Bloquear si ya existe un viaje para esta solicitud
        $tripExistente = Trip::where('trip_request_id', $request->trip_request_id)->exists();

        if ($tripExistente) {
            return response()->json([
                'message' => 'Ya existe un viaje registrado para esta solicitud.',
            ], 422);
        }

        $trip = Trip::create($request->only([
            'trip_request_id',
            'vehicle_id',
            'driver_id',
            'route_id',
            'start_time',
            'start_mileage',
            'observations',
        ]));

        return response()->json([
            'message' => 'Trip created successfully.',
            'data'    => $trip,
        ], 201);
    }

    public function show(Trip $trip): JsonResponse
    {
        $trip->load([
            'driver:id,name,email',
            'vehicle:id,plate,brand,model,mileage',
            'route:id,name,origin,destination',
            'tripRequest',
        ]);

        return response()->json([
            'message' => 'Trip retrieved successfully.',
            'data'    => $trip,
        ]);
    }

    public function update(Request $request, Trip $trip): JsonResponse
    {
        $trip->update($request->only(['end_time', 'end_mileage', 'observations']));

        return response()->json([
            'message' => 'Trip updated successfully.',
            'data'    => $trip,
        ]);
    }

    public function destroy(Trip $trip): JsonResponse
    {
        $trip->delete();

        return response()->json([
            'message' => 'Trip deleted successfully.',
            'data'    => null,
        ]);
    }

    public function registerReturn(Request $request, Trip $trip): JsonResponse
    {
        $endMileage = (int) $request->input('end_mileage');

        if ($endMileage <= (int) $trip->start_mileage) {
            return response()->json([
                'message' => 'El kilometraje final debe ser mayor al inicial.',
            ], 422);
        }

        $trip->update([
            'end_time'     => $request->input('end_time', now()),
            'end_mileage'  => $endMileage,
            'observations' => $request->input('observations'),
        ]);

        return response()->json([
            'message' => 'Vehicle return registered successfully.',
            'data'    => $trip,
        ]);
    }
}