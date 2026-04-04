<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class ViajeController extends Controller
{
    use ApiConsumer;

    public function index(Request $request)
    {
        $params = ['page' => $request->query('page', 1)];
        if ($request->filled('driver_id'))  $params['driver_id']  = $request->driver_id;
        if ($request->filled('vehicle_id')) $params['vehicle_id'] = $request->vehicle_id;

        $response = $this->apiGet('trips', $params);

        if ($response->failed()) {
            return back()->with('error', 'No se pudo cargar los viajes.');
        }

        $paginado = $response->json('data');
        $viajes   = collect($paginado['data'] ?? []);

        // Solicitudes aprobadas sin viaje para el formulario de salida
        $rSolicitudes     = $this->apiGet('trip-requests', ['status' => 'approved', 'per_page' => 999]);
        $todasSolicitudes = collect($rSolicitudes->json('data.data') ?? []);

        // Filtrar las que NO tienen viaje aún (trip vacío o null)
        $solicitudesAprobadas = $todasSolicitudes->filter(
            fn($s) => empty($s['trip'])
        )->values();

        return view('operador.viajes.index', compact(
            'viajes', 'solicitudesAprobadas', 'paginado'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'trip_request_id' => 'required',
            'start_mileage'   => 'required|integer|min:0',
            'start_time'      => 'required|date',
            'observations'    => 'nullable|string|max:500',
        ], [
            'trip_request_id.required' => 'Seleccione una solicitud aprobada.',
            'start_mileage.required'   => 'El kilometraje inicial es obligatorio.',
            'start_time.required'      => 'La fecha/hora de salida es obligatoria.',
        ]);

        // Obtener los datos de la solicitud para extraer vehicle_id y driver_id
        $rSolicitud = $this->apiGet("trip-requests/{$request->trip_request_id}");

        if ($rSolicitud->failed()) {
            return back()->with('error', 'No se pudo cargar la solicitud seleccionada.');
        }

        $solicitud = $rSolicitud->json('data');

        // El API de trips espera: trip_request_id, vehicle_id, driver_id, route_id, start_time, start_mileage
        $response = $this->apiPost('trips', [
            'trip_request_id' => (int) $request->trip_request_id,
            'vehicle_id'      => (int) ($solicitud['vehicle_id'] ?? $solicitud['vehicle']['id'] ?? 0),
            'driver_id'       => (int) ($solicitud['user_id']    ?? $solicitud['user']['id']    ?? 0),
            'route_id'        => $solicitud['route_id'] ?? null,
            'start_time'      => $request->start_time,
            'start_mileage'   => (int) $request->start_mileage,
            'observations'    => $request->observations,
        ]);

        if ($response->failed()) {
            $msg = $response->json('message') ?? 'Error al registrar la salida.';
            return back()->with('error', $msg);
        }

        return back()->with('success', 'Salida registrada correctamente.');
    }

    public function registrarRetorno(Request $request, int $id)
    {
        $request->validate([
            'end_time'     => 'required|date',
            'end_mileage'  => 'required|integer|min:0',
            'observations' => 'nullable|string|max:500',
        ], [
            'end_time.required'    => 'La fecha/hora de retorno es obligatoria.',
            'end_mileage.required' => 'El kilometraje final es obligatorio.',
        ]);

        $response = $this->apiPatch("trips/{$id}/register-return", [
            'end_time'     => $request->end_time,
            'end_mileage'  => (int) $request->end_mileage,
            'observations' => $request->observations,
        ]);

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Error al registrar el retorno.');
        }

        return back()->with('success', 'Retorno registrado correctamente. Vehículo liberado.');
    }
}