<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    use ApiConsumer;

    public function index(Request $request)
    {
        $statusFiltro = $request->query('status', 'pending');
        $params       = ['page' => $request->query('page', 1)];

        if ($statusFiltro && $statusFiltro !== 'all') {
            $params['status'] = $statusFiltro;
        }

        $response = $this->apiGet('trip-requests', $params);

        if ($response->failed()) {
            return back()->with('error', 'No se pudo cargar las solicitudes.');
        }

        $paginado = $response->json('data');

        // Contadores por estado (ignorar si fallan)
        $contadores = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0];
        foreach (['pending', 'approved', 'rejected', 'cancelled'] as $st) {
            try {
                $r = $this->apiGet('trip-requests', ['status' => $st, 'per_page' => 1]);
                $contadores[$st] = $r->json('data.total') ?? 0;
            } catch (\Exception $e) {
                $contadores[$st] = 0;
            }
        }

        // Cargar choferes usando role_id=3
        $rChoferes  = $this->apiGet('users', ['role_id' => 3, 'per_page' => 999]);
        $rVehiculos = $this->apiGet('vehicles', ['status' => 'available', 'per_page' => 999]);
        $rRutas     = $this->apiGet('routes', ['per_page' => 999]);

        $choferes  = collect($rChoferes->json('data.data') ?? []);
        $vehiculos = collect($rVehiculos->json('data.data') ?? []);
        $rutas     = collect($rRutas->json('data.data') ?? []);

        return view('operador.solicitudes.index', [
            'solicitudes'  => $paginado['data'] ?? [],
            'paginado'     => [
                'current_page' => $paginado['current_page'] ?? 1,
                'last_page'    => $paginado['last_page']    ?? 1,
                'total'        => $paginado['total']        ?? 0,
            ],
            'statusFiltro' => $statusFiltro,
            'contadores'   => $contadores,
            'choferes'     => $choferes,
            'vehiculos'    => $vehiculos,
            'rutas'        => $rutas,
        ]);
    }

    public function aprobar(Request $request, int $id)
    {
        $response = $this->apiPatch("trip-requests/{$id}/approve");

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'No se pudo aprobar la solicitud.');
        }

        return back()->with('success', 'Solicitud aprobada correctamente.');
    }

    public function rechazar(Request $request, int $id)
    {
        $response = $this->apiPatch("trip-requests/{$id}/reject", [
            'rejection_reason' => $request->rejection_reason,
        ]);

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'No se pudo rechazar la solicitud.');
        }

        return back()->with('success', 'Solicitud rechazada correctamente.');
    }

    public function asignacionDirecta(Request $request)
    {
        $request->validate([
            'user_id'        => 'required',
            'vehicle_id'     => 'required',
            'departure_date' => 'required|date',
            'return_date'    => 'required|date|after:departure_date',
        ], [
            'user_id.required'        => 'Seleccione un chofer.',
            'vehicle_id.required'     => 'Seleccione un vehiculo.',
            'departure_date.required' => 'La fecha de salida es obligatoria.',
            'return_date.required'    => 'La fecha de retorno es obligatoria.',
            'return_date.after'       => 'El retorno debe ser posterior a la salida.',
        ]);

        $response = $this->apiPost('trip-requests/direct-assign', [
            'user_id'        => (int) $request->user_id,
            'vehicle_id'     => (int) $request->vehicle_id,
            'route_id'       => $request->route_id ? (int) $request->route_id : null,
            'departure_date' => $request->departure_date,
            'return_date'    => $request->return_date,
            'reason'         => $request->reason,
        ]);

        if ($response->failed()) {
            return $this->handleError($response, 'Error al crear la asignacion directa.');
        }

        return redirect()->route('operador.solicitudes')
            ->with('success', 'Asignacion directa creada correctamente.');
    }

    public function createDirecta()
    {
        $rChoferes  = $this->apiGet('users', ['role_id' => 3, 'per_page' => 999]);
        $rVehiculos = $this->apiGet('vehicles', ['status' => 'available', 'per_page' => 999]);
        $rRutas     = $this->apiGet('routes', ['per_page' => 999]);

        $choferes  = collect($rChoferes->json('data.data') ?? []);
        $vehiculos = collect($rVehiculos->json('data.data') ?? []);
        $rutas     = collect($rRutas->json('data.data') ?? []);

        return view('operador.solicitudes.directa', compact('choferes', 'vehiculos', 'rutas'));
    }
}