<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    use ApiConsumer;

    public function index(Request $request)
    {
        $fecha_inicio = $request->query('fecha_inicio');
        $fecha_fin    = $request->query('fecha_fin');

        if ($fecha_inicio && $fecha_fin) {
            // Usar reporte de disponibilidad para filtrar por rango
            $response  = $this->apiGet('reports/vehicle-availability', [
                'start_date' => $fecha_inicio,
                'end_date'   => $fecha_fin,
            ]);
            $todos     = collect($response->json('data') ?? []);
            $vehiculos = $todos->filter(fn($v) => is_null($v['trip_request_id']))->values();

            return view('chofer.vehiculos.index', [
                'vehiculos'    => $vehiculos,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin'    => $fecha_fin,
                'paginado'     => null,
            ]);
        }

        $response = $this->apiGet('vehicles', [
            'status' => 'available',
            'page'   => $request->query('page', 1),
        ]);

        $paginado = $response->json('data');

        return view('chofer.vehiculos.index', [
            'vehiculos'    => $paginado['data'] ?? [],
            'fecha_inicio' => null,
            'fecha_fin'    => null,
            'paginado'     => [
                'current_page' => $paginado['current_page'] ?? 1,
                'last_page'    => $paginado['last_page'] ?? 1,
                'total'        => $paginado['total'] ?? 0,
            ],
        ]);
    }
}