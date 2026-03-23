<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    // ── GET /admin/rutas ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $page     = (int) $request->query('page', 1);
        $paginado = Route::latest()->paginate(10, ['*'], 'page', $page);

        return view('admin.rutas.index', [
            'rutas'   => $paginado->items(),
            'paginado' => [
                'current_page' => $paginado->currentPage(),
                'last_page'    => $paginado->lastPage(),
                'total'        => $paginado->total(),
            ],
        ]);
    }

    // ── POST /admin/rutas ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:150',
            'origin'            => 'required|string|max:150',
            'destination'       => 'required|string|max:150',
            'distance_km'       => 'nullable|numeric|min:0',
            'estimated_minutes' => 'nullable|integer|min:1',
            'description'       => 'nullable|string|max:500',
        ], [
            'name.required'        => 'El nombre de la ruta es obligatorio.',
            'origin.required'      => 'El punto de inicio es obligatorio.',
            'destination.required' => 'El punto final es obligatorio.',
        ]);

        Route::create($request->only([
            'name', 'origin', 'destination',
            'distance_km', 'estimated_minutes', 'description',
        ]));

        return back()->with('success', 'Ruta creada correctamente.');
    }

    // ── PUT /admin/rutas/{id} ─────────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $ruta = Route::findOrFail($id);

        $request->validate([
            'name'              => 'required|string|max:150',
            'origin'            => 'required|string|max:150',
            'destination'       => 'required|string|max:150',
            'distance_km'       => 'nullable|numeric|min:0',
            'estimated_minutes' => 'nullable|integer|min:1',
            'description'       => 'nullable|string|max:500',
        ], [
            'name.required'        => 'El nombre de la ruta es obligatorio.',
            'origin.required'      => 'El punto de inicio es obligatorio.',
            'destination.required' => 'El punto final es obligatorio.',
        ]);

        $ruta->update($request->only([
            'name', 'origin', 'destination',
            'distance_km', 'estimated_minutes', 'description',
        ]));

        return back()->with('success', 'Ruta actualizada correctamente.');
    }

    // ── DELETE /admin/rutas/{id} ── Borrado lógico ────────────────────────────
    public function destroy(int $id)
    {
        $ruta = Route::findOrFail($id);
        $ruta->delete();

        return back()->with('success', 'Ruta eliminada correctamente (borrado lógico).');
    }
}