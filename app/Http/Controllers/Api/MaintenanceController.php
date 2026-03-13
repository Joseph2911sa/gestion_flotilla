<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Maintenance::with([
            'vehicle:id,plate,brand,model,status'
        ])->latest();

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->paginate(10);

        return response()->json([
            'message' => 'Maintenance records retrieved successfully.',
            'data' => $maintenances
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->only([
            'vehicle_id',
            'type',
            'description',
            'start_date',
            'cost',
            'mileage_at_service'
        ]);

        $data['status'] = Maintenance::STATUS_OPEN;

        $maintenance = Maintenance::create($data);

        return response()->json([
            'message' => 'Maintenance record created successfully.',
            'data' => $maintenance
        ], 201);
    }

    public function show(Maintenance $maintenance): JsonResponse
    {
        $maintenance->load('vehicle:id,plate,brand,model,status,mileage');

        return response()->json([
            'message' => 'Maintenance record retrieved successfully.',
            'data' => $maintenance
        ]);
    }

    public function update(Request $request, Maintenance $maintenance): JsonResponse
    {
        $data = $request->only([
            'type',
            'description',
            'start_date',
            'cost',
            'mileage_at_service'
        ]);

        $maintenance->update($data);

        return response()->json([
            'message' => 'Maintenance record updated successfully.',
            'data' => $maintenance
        ]);
    }

    public function destroy(Maintenance $maintenance): JsonResponse
    {
        $maintenance->delete();

        return response()->json([
            'message' => 'Maintenance record deleted successfully.',
            'data' => null
        ]);
    }

    public function close(Request $request, Maintenance $maintenance): JsonResponse
    {
        $maintenance->update([
            'status' => Maintenance::STATUS_CLOSED,
            'end_date' => $request->input('end_date', now()->toDateString()),
            'cost' => $request->input('cost', $maintenance->cost)
        ]);

        return response()->json([
            'message' => 'Maintenance record closed successfully.',
            'data' => $maintenance
        ]);
    }
}