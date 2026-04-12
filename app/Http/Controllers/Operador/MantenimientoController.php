<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            return redirect()->route('operador.mantenimientos')
                ->with('error', 'No se pudo cargar los mantenimientos.');
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
        // Usar Validator manual para controlar el redirect
        // $request->validate() usa back() automaticamente y causa el 403
        $validator = Validator::make($request->all(), [
            'vehicle_id'  => 'required',
            'type'        => 'required|in:preventive,corrective,inspection',
            'description' => 'required|string|max:500',
            'start_date'  => 'required|date',
        ], [
            'vehicle_id.required'  => 'Seleccione un vehiculo.',
            'type.required'        => 'Seleccione el tipo.',
            'description.required' => 'La descripcion es obligatoria.',
            'start_date.required'  => 'La fecha de inicio es obligatoria.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('operador.mantenimientos')
                ->withErrors($validator)
                ->withInput()
                ->with('abrir_modal', true);
        }

        $response = $this->apiPost('maintenances', [
            'vehicle_id'         => (int) $request->vehicle_id,
            'type'               => $request->type,
            'description'        => $request->description,
            'start_date'         => $request->start_date,
            'cost'               => $request->cost ?: null,
            'mileage_at_service' => $request->mileage_at_service ?: null,
        ]);

        if ($response->failed()) {
            $msg = $response->json('message') ?? 'Error al abrir el mantenimiento.';
            return redirect()->route('operador.mantenimientos')
                ->with('error', $msg);
        }

        return redirect()->route('operador.mantenimientos')
            ->with('success', 'Mantenimiento abierto. El vehiculo paso a estado de mantenimiento.');
    }

    public function cerrar(Request $request, int $id)
    {
        $response = $this->apiPatch("maintenances/{$id}/close", [
            'end_date' => $request->end_date ?? now()->toDateTimeString(),
            'cost'     => $request->cost ?: null,
        ]);

        if ($response->failed()) {
            return redirect()->route('operador.mantenimientos')
                ->with('error', $response->json('message') ?? 'Error al cerrar el mantenimiento.');
        }

        return redirect()->route('operador.mantenimientos')
            ->with('success', 'Mantenimiento cerrado. El vehiculo volvio a estado disponible.');
    }
}