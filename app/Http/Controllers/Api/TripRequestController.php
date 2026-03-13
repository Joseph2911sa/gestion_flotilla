<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TripRequest::with([
            'user:id,name,email',
            'vehicle:id,plate,brand,model',
            'route:id,name,origin,destination',
            'reviewer:id,name'
        ])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(10);

        return response()->json([
            'message' => 'Trip requests retrieved successfully.',
            'data' => $requests
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->only([
            'vehicle_id',
            'route_id',
            'departure_date',
            'return_date',
            'reason'
        ]);

        $data['status'] = TripRequest::STATUS_PENDING;

        $tripRequest = TripRequest::create($data);

        $tripRequest->load([
            'user:id,name,email',
            'vehicle:id,plate,brand,model',
            'route:id,name,origin,destination'
        ]);

        return response()->json([
            'message' => 'Trip request created successfully.',
            'data' => $tripRequest
        ], 201);
    }

    public function show(TripRequest $tripRequest): JsonResponse
    {
        $tripRequest->load([
            'user:id,name,email',
            'vehicle:id,plate,brand,model,status',
            'route:id,name,origin,destination',
            'reviewer:id,name',
            'trip'
        ]);

        return response()->json([
            'message' => 'Trip request retrieved successfully.',
            'data' => $tripRequest
        ]);
    }

    public function update(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $data = $request->only([
            'vehicle_id',
            'route_id',
            'departure_date',
            'return_date',
            'reason'
        ]);

        $tripRequest->update($data);

        return response()->json([
            'message' => 'Trip request updated successfully.',
            'data' => $tripRequest
        ]);
    }

    public function destroy(TripRequest $tripRequest): JsonResponse
    {
        $tripRequest->delete();

        return response()->json([
            'message' => 'Trip request deleted successfully.',
            'data' => null
        ]);
    }

    public function approve(TripRequest $tripRequest): JsonResponse
    {
        $tripRequest->update([
            'status' => TripRequest::STATUS_APPROVED
        ]);

        return response()->json([
            'message' => 'Trip request approved.',
            'data' => $tripRequest
        ]);
    }

    public function reject(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $tripRequest->update([
            'status' => TripRequest::STATUS_REJECTED,
            'rejection_reason' => $request->input('rejection_reason')
        ]);

        return response()->json([
            'message' => 'Trip request rejected.',
            'data' => $tripRequest
        ]);
    }

    public function cancel(TripRequest $tripRequest): JsonResponse
    {
        $tripRequest->update([
            'status' => TripRequest::STATUS_CANCELLED
        ]);

        return response()->json([
            'message' => 'Trip request cancelled.',
            'data' => $tripRequest
        ]);
    }
}