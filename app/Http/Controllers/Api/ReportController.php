<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function vehicleAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $report = DB::table('vehicles')
            ->leftJoin('trip_requests', function ($join) use ($startDate, $endDate) {
                $join->on('vehicles.id', '=', 'trip_requests.vehicle_id')
                    ->whereNull('trip_requests.deleted_at')
                    // Solo solicitudes aprobadas (no pendientes, rechazadas ni canceladas)
                    ->whereIn('trip_requests.status', ['approved', 'completed'])
                    // Detección correcta de traslape de rangos:
                    // Dos rangos [A,B] y [C,D] se solapan si A < D AND B > C
                    ->where('trip_requests.departure_date', '<', $endDate)
                    ->where('trip_requests.return_date',    '>', $startDate);
            })
            ->whereNull('vehicles.deleted_at')
            ->select(
                'vehicles.id',
                'vehicles.plate',
                'vehicles.brand',
                'vehicles.model',
                'vehicles.year',
                'vehicles.vehicle_type',
                'vehicles.capacity',
                'vehicles.fuel_type',
                'vehicles.image_path',
                'vehicles.status',
                'trip_requests.id as trip_request_id',
                'trip_requests.departure_date',
                'trip_requests.return_date',
                'trip_requests.status as request_status'
            )
            ->orderBy('vehicles.id')
            ->get();

        return response()->json([
            'message' => 'Reporte de disponibilidad de vehículos',
            'data'    => $report,
        ]);
    }

    public function fleetUsage(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $report = DB::table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->whereNull('trips.deleted_at')
            ->whereNull('vehicles.deleted_at')
            ->whereBetween('trips.start_time', [$startDate, $endDate])
            ->select(
                'vehicles.id',
                'vehicles.plate',
                'vehicles.brand',
                'vehicles.model',
                DB::raw('COUNT(trips.id) as total_trips'),
                DB::raw('COALESCE(SUM(trips.end_mileage - trips.start_mileage), 0) as total_kilometers')
            )
            ->groupBy(
                'vehicles.id',
                'vehicles.plate',
                'vehicles.brand',
                'vehicles.model'
            )
            ->orderBy('vehicles.id')
            ->get();

        return response()->json([
            'message' => 'Reporte de uso de flotilla',
            'data'    => $report,
        ]);
    }

    public function driverHistory(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'  => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $driverId  = $request->query('driver_id');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $report = DB::table('trip_requests')
            ->join('vehicles', 'trip_requests.vehicle_id', '=', 'vehicles.id')
            ->whereNull('trip_requests.deleted_at')
            ->whereNull('vehicles.deleted_at')
            ->where('trip_requests.user_id', $driverId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('trip_requests.departure_date', [$startDate, $endDate])
                      ->orWhereBetween('trip_requests.return_date',  [$startDate, $endDate]);
            })
            ->select(
                'trip_requests.id',
                'trip_requests.departure_date',
                'trip_requests.return_date',
                'trip_requests.status',
                'trip_requests.reason',
                'vehicles.plate',
                'vehicles.brand',
                'vehicles.model',
                'vehicles.vehicle_type'
            )
            ->orderBy('trip_requests.departure_date')
            ->get();

        return response()->json([
            'message' => 'Reporte de historial del chofer',
            'data'    => $report,
        ]);
    }
}