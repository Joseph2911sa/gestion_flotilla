<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    public function index()
    {
        $rutas = Route::latest()->paginate(10);
        return view('operador.rutas.index', compact('rutas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'origin'      => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'distance'    => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required'        => 'El nombre de la ruta es obligatorio.',
            'origin.required'      => 'El punto de inicio es obligatorio.',
            'destination.required' => 'El punto final es obligatorio.',
        ]);

        Route::create($request->only([
            'name', 'origin', 'destination', 'distance_km', 'description'
        ]));

        return back()->with('success', 'Ruta creada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'origin'      => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'distance'    => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required'        => 'El nombre de la ruta es obligatorio.',
            'origin.required'      => 'El punto de inicio es obligatorio.',
            'destination.required' => 'El punto final es obligatorio.',
        ]);

        $ruta = Route::findOrFail($id);
        $ruta->update($request->only([
            'name', 'origin', 'destination', 'distance_km', 'description'
        ]));

        return back()->with('success', 'Ruta actualizada correctamente.');
    }

    public function destroy($id)
    {
        $ruta = Route::findOrFail($id);
        $ruta->delete();
        return back()->with('success', 'Ruta eliminada correctamente.');
    }
}