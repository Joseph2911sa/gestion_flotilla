<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    use ApiConsumer;

    public function index()
    {
        return view('admin.reportes.index');
    }

    public function disponibilidad(Request $request)
    {
        $vehiculos = collect();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ], ['end_date.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.']);

            $response = $this->apiGet('reports/vehicle-availability', [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);

            if ($response->successful()) {
                $vehiculos = collect($response->json('data') ?? []);
            } else {
                return back()->with('error', 'Error al generar el reporte.');
            }
        }

        return view('admin.reportes.disponibilidad', compact('vehiculos', 'startDate', 'endDate'));
    }

    public function usoFlotilla(Request $request)
    {
        $reporte   = collect();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ], ['end_date.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.']);

            $response = $this->apiGet('reports/fleet-usage', [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);

            if ($response->successful()) {
                $reporte = collect($response->json('data') ?? []);
            } else {
                return back()->with('error', 'Error al generar el reporte.');
            }
        }

        return view('admin.reportes.uso-flotilla', compact('reporte', 'startDate', 'endDate'));
    }

    public function historialChofer(Request $request)
    {
        $solicitudes = collect();
        $startDate   = $request->start_date;
        $endDate     = $request->end_date;
        $driverId    = $request->driver_id;
        $chofer      = null;

        // Cargar lista de choferes
        $rUsers   = $this->apiGet('users', ['per_page' => 999]);
        $choferes = collect($rUsers->json('data.data') ?? [])
            ->filter(fn($u) => ($u['role']['name'] ?? '') === 'Chofer')
            ->values();

        if ($request->filled('driver_id') && $request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'driver_id'  => 'required',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ], [
                'driver_id.required'      => 'Seleccione un chofer.',
                'end_date.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.',
            ]);

            $chofer   = $choferes->firstWhere('id', (int) $driverId);
            $response = $this->apiGet('reports/driver-history', [
                'driver_id'  => $driverId,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);

            if ($response->successful()) {
                $solicitudes = collect($response->json('data') ?? []);
            } else {
                return back()->with('error', 'Error al generar el reporte.');
            }
        }

        return view('admin.reportes.historial-chofer', compact(
            'solicitudes', 'chofer', 'choferes', 'startDate', 'endDate', 'driverId'
        ));
    }
}