<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::where('status', 'available');

        // Filtro por rango de fecha si se proporciona
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $fechaInicio = $request->fecha_inicio;
            $fechaFin    = $request->fecha_fin;

            // Excluir vehículos con solicitudes aprobadas que se traslapen
            $query->whereDoesntHave('tripRequests', function ($q) use ($fechaInicio, $fechaFin) {
                $q->where('status', 'approved')
                  ->where(function ($q2) use ($fechaInicio, $fechaFin) {
                      $q2->whereBetween('departure_date', [$fechaInicio, $fechaFin])
                         ->orWhereBetween('return_date', [$fechaInicio, $fechaFin])
                         ->orWhere(function ($q3) use ($fechaInicio, $fechaFin) {
                             $q3->where('departure_date', '<=', $fechaInicio)
                                ->where('return_date', '>=', $fechaFin);
                         });
                  });
            });
        }

        $vehiculos = $query->paginate(9);

        return view('chofer.vehiculos.index', compact('vehiculos'));
    }
}