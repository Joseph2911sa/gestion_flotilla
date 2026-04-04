<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    use ApiConsumer;

    public function index(Request $request)
    {
        $vehiculoFiltro = $request->query('vehicle_id', '');
        $statusFiltro   = $request->query('status', '');
        $params         = ['page' => $request->query('page', 1)];
        if ($vehiculoFiltro) $params['vehicle_id'] = $vehiculoFiltro;
        if ($statusFiltro)   $params['status']     = $statusFiltro;

        $response  = $this->apiGet('maintenances', $params);
        $responseV = $this->apiGet('vehicles', ['per_page' => 999]);

        if ($response->failed()) {
            return back()->with('error', 'No se pudo cargar los mantenimientos.');
        }

        $paginado  = $response->json('data');
        $vehiculos = collect($responseV->json('data.data') ?? []);

        return view('operador.mantenimientos.index', [
            'mantenimientos' => $paginado['data'] ?? [],
            'paginado'       => [
                'current_page' => $paginado['current_page'],
                'last_page'    => $paginado['last_page'],
                'total'        => $paginado['total'],
            ],
            'vehiculos'      => $vehiculos,
            'vehiculoFiltro' => $vehiculoFiltro,
            'statusFiltro'   => $statusFiltro,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'  => 'required',
            'type'        => 'required|in:preventive,corrective,inspection',
            'description' => 'required|string|max:500',
            'start_date'  => 'required|date',
        ], [
            'vehicle_id.required'  => 'Seleccione un vehículo.',
            'type.required'        => 'Seleccione el tipo.',
            'description.required' => 'La descripción es obligatoria.',
            'start_date.required'  => 'La fecha de inicio es obligatoria.',
        ]);

        $response = $this->apiPost('maintenances', [
            'vehicle_id'         => (int) $request->vehicle_id,
            'type'               => $request->type,
            'description'        => $request->description,
            'start_date'         => $request->start_date,
            'cost'               => $request->cost ?: null,
            'mileage_at_service' => $request->mileage_at_service ?: null,
        ]);

        if ($response->failed()) {
            return $this->handleError($response, 'Error al abrir el mantenimiento.');
        }

        return back()->with('success', 'Mantenimiento abierto. El vehículo pasó a estado de mantenimiento.');
    }

    public function cerrar(Request $request, int $id)
    {
        $response = $this->apiPatch("maintenances/{$id}/close", [
            'end_date' => $request->end_date ?? now()->toDateString(),
            'cost'     => $request->cost ?: null,
        ]);

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Error al cerrar el mantenimiento.');
        }

        return back()->with('success', 'Mantenimiento cerrado. El vehículo volvió a estado disponible.');
    }
}