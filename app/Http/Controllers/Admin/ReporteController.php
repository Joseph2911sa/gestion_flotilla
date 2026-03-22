<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    // ── GET /admin/reportes ───────────────────────────────────────────────────
    public function index()
    {
        return view('admin.reportes.index');
    }

    // ── GET /admin/reportes/disponibilidad ────────────────────────────────────
    public function disponibilidad(Request $request)
    {
        $vehiculos = collect();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ], [
                'end_date.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.',
            ]);

            $vehiculos = DB::table('vehicles')
                ->leftJoin('trip_requests', function ($join) use ($startDate, $endDate) {
                    $join->on('vehicles.id', '=', 'trip_requests.vehicle_id')
                        ->whereNull('trip_requests.deleted_at')
                        ->where('trip_requests.status', 'approved')
                        ->where(function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('trip_requests.departure_date', [$startDate, $endDate])
                              ->orWhereBetween('trip_requests.return_date', [$startDate, $endDate])
                              ->orWhere(function ($q2) use ($startDate, $endDate) {
                                  $q2->where('trip_requests.departure_date', '<=', $startDate)
                                     ->where('trip_requests.return_date', '>=', $endDate);
                              });
                        });
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
                ->orderBy('vehicles.plate')
                ->get();
        }

        return view('admin.reportes.disponibilidad', compact('vehiculos', 'startDate', 'endDate'));
    }

    // ── GET /admin/reportes/uso-flotilla ──────────────────────────────────────
    public function usoFlotilla(Request $request)
    {
        $reporte   = collect();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ], [
                'end_date.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.',
            ]);

            $reporte = DB::table('trips')
                ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
                ->whereNull('trips.deleted_at')
                ->whereNull('vehicles.deleted_at')
                ->whereBetween('trips.start_time', [
                    $startDate . ' 00:00:00',
                    $endDate   . ' 23:59:59',
                ])
                ->select(
                    'vehicles.id',
                    'vehicles.plate',
                    'vehicles.brand',
                    'vehicles.model',
                    'vehicles.vehicle_type',
                    DB::raw('COUNT(trips.id) as total_viajes'),
                    DB::raw('COALESCE(SUM(trips.end_mileage - trips.start_mileage), 0) as total_km')
                )
                ->groupBy(
                    'vehicles.id', 'vehicles.plate',
                    'vehicles.brand', 'vehicles.model', 'vehicles.vehicle_type'
                )
                ->orderByDesc('total_viajes')
                ->get();
        }

        return view('admin.reportes.uso-flotilla', compact('reporte', 'startDate', 'endDate'));
    }

    // ── GET /admin/reportes/historial-chofer ──────────────────────────────────
    public function historialChofer(Request $request)
    {
        $solicitudes = collect();
        $startDate   = $request->start_date;
        $endDate     = $request->end_date;
        $driverId    = $request->driver_id;
        $chofer      = null;

        // Lista de choferes para el select
        $choferes = User::whereHas('role', fn($q) => $q->where('name', 'Chofer'))
            ->orderBy('name')
            ->get();

        if ($request->filled('driver_id') && $request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'driver_id'  => 'required|exists:users,id',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ], [
                'driver_id.required'      => 'Seleccione un chofer.',
                'end_date.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.',
            ]);

            $chofer = User::find($driverId);

            $solicitudes = DB::table('trip_requests')
                ->join('vehicles', 'trip_requests.vehicle_id', '=', 'vehicles.id')
                ->whereNull('trip_requests.deleted_at')
                ->whereNull('vehicles.deleted_at')
                ->where('trip_requests.user_id', $driverId)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('trip_requests.departure_date', [$startDate, $endDate])
                      ->orWhereBetween('trip_requests.return_date', [$startDate, $endDate]);
                })
                ->select(
                    'trip_requests.id',
                    'trip_requests.departure_date',
                    'trip_requests.return_date',
                    'trip_requests.status',
                    'trip_requests.reason',
                    'trip_requests.rejection_reason',
                    'vehicles.plate',
                    'vehicles.brand',
                    'vehicles.model',
                    'vehicles.vehicle_type'
                )
                ->orderBy('trip_requests.departure_date')
                ->get();
        }

        return view('admin.reportes.historial-chofer', compact(
            'solicitudes', 'chofer', 'choferes',
            'startDate', 'endDate', 'driverId'
        ));
    }
}