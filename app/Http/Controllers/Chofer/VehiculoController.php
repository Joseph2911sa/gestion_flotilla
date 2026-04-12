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
            // datetime-local envia "2026-04-12T15:00"
            // El API necesita "2026-04-12 15:00:00"
            $startFormatted = str_replace('T', ' ', $fecha_inicio) . ':00';
            $endFormatted   = str_replace('T', ' ', $fecha_fin)   . ':00';

            $response = $this->apiGet('reports/vehicle-availability', [
                'start_date' => $startFormatted,
                'end_date'   => $endFormatted,
            ]);

           if ($response->failed()) {
    dd($response->status(), $response->json(), $startFormatted, $endFormatted);
}

            $todos     = collect($response->json('data') ?? []);
            $vehiculos = $todos->filter(fn($v) => empty($v['trip_request_id']))->values();

            return view('chofer.vehiculos.index', [
                'vehiculos'    => $vehiculos,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin'    => $fecha_fin,
                'paginado'     => null,
            ]);
        }

        // Sin filtro: mostrar todos los disponibles paginados
        $response = $this->apiGet('vehicles', [
            'status' => 'available',
            'page'   => $request->query('page', 1),
        ]);

        if ($response->failed()) {
            return redirect()->route('chofer.vehiculos')
                ->with('error', 'Error al cargar vehiculos disponibles.');
        }

        $paginado = $response->json('data');

        return view('chofer.vehiculos.index', [
            'vehiculos'    => $paginado['data'] ?? [],
            'fecha_inicio' => null,
            'fecha_fin'    => null,
            'paginado'     => [
                'current_page' => $paginado['current_page'] ?? 1,
                'last_page'    => $paginado['last_page']    ?? 1,
                'total'        => $paginado['total']        ?? 0,
            ],
        ]);
    }
}