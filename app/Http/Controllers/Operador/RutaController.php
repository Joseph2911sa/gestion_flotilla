<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    use ApiConsumer;

    public function index(Request $request)
    {
        $response = $this->apiGet('routes', ['page' => $request->query('page', 1)]);

        if ($response->failed()) {
            return back()->with('error', 'No se pudo cargar las rutas.');
        }

        $rutas = $response->json('data');

        return view('operador.rutas.index', ['rutas' => $rutas]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'origin'      => 'required|string|max:150',
            'destination' => 'required|string|max:150',
        ], [
            'name.required'        => 'El nombre es obligatorio.',
            'origin.required'      => 'El origen es obligatorio.',
            'destination.required' => 'El destino es obligatorio.',
        ]);

        $response = $this->apiPost('routes', [
            'name'              => $request->name,
            'origin'            => $request->origin,
            'destination'       => $request->destination,
            'distance_km'       => $request->distance_km ?: null,
            'estimated_minutes' => $request->estimated_minutes ?: null,
            'description'       => $request->description,
        ]);

        if ($response->failed()) {
            return $this->handleError($response, 'Error al crear la ruta.');
        }

        return back()->with('success', 'Ruta creada correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'origin'      => 'required|string|max:150',
            'destination' => 'required|string|max:150',
        ]);

        $response = $this->apiPut("routes/{$id}", [
            'name'              => $request->name,
            'origin'            => $request->origin,
            'destination'       => $request->destination,
            'distance_km'       => $request->distance_km ?: null,
            'estimated_minutes' => $request->estimated_minutes ?: null,
            'description'       => $request->description,
        ]);

        if ($response->failed()) {
            return $this->handleError($response, 'Error al actualizar la ruta.');
        }

        return back()->with('success', 'Ruta actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $response = $this->apiDelete("routes/{$id}");

        if ($response->failed()) {
            return back()->with('error', 'Error al eliminar la ruta.');
        }

        return back()->with('success', 'Ruta eliminada correctamente.');
    }
}