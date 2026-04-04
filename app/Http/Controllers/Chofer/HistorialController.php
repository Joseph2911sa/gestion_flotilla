<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    use ApiConsumer;

    public function index()
    {
        // El API filtra automáticamente por el chofer autenticado (token)
        $response = $this->apiGet('trip-requests', ['page' => 1, 'per_page' => 10]);

        $paginado    = $response->json('data') ?? [];
        $solicitudes = collect($paginado['data'] ?? []);

        return view('chofer.historial.index', compact('solicitudes', 'paginado'));
    }

    public function cancelar(Request $request, int $id)
    {
        $response = $this->apiPatch("trip-requests/{$id}/cancel");

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'No se puede cancelar la solicitud.');
        }

        return back()->with('success', 'Solicitud cancelada correctamente.');
    }
}