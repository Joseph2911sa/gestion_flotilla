<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    public function index()
    {
        // Vehículos disponibles para el selector
        $vehiculos = Vehicle::where('status', 'available')->get();

        return view('chofer.solicitudes.index', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        // Validaciones frontend
        $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'departure_date' => 'required|date',
            'return_date'    => 'required|date|after:departure_date',
            'reason'         => 'nullable|string|max:500',
        ], [
            'vehicle_id.required'     => 'Debe seleccionar un vehículo.',
            'vehicle_id.exists'       => 'El vehículo seleccionado no es válido.',
            'departure_date.required' => 'La fecha de inicio es obligatoria.',
            'return_date.required'    => 'La fecha de fin es obligatoria.',
            'return_date.after'       => 'La fecha de fin debe ser mayor a la fecha de inicio.',
        ]);

        // Verificar que el vehículo no tenga solicitudes aprobadas que se traslapen
        $traslape = TripRequest::where('vehicle_id', $request->vehicle_id)
            ->where('status', 'approved')
            ->where(function ($q) use ($request) {
                $q->whereBetween('departure_date', [$request->departure_date, $request->return_date])
                  ->orWhereBetween('return_date', [$request->departure_date, $request->return_date])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('departure_date', '<=', $request->departure_date)
                         ->where('return_date', '>=', $request->return_date);
                  });
            })->exists();

        if ($traslape) {
            return back()
                ->withInput()
                ->with('error', 'El vehículo ya tiene una asignación aprobada en ese rango de fechas.');
        }

        // Crear la solicitud
        TripRequest::create([
            'user_id'        => session('user')['id'],
            'vehicle_id'     => $request->vehicle_id,
            'departure_date' => $request->departure_date,
            'return_date'    => $request->return_date,
            'reason'         => $request->reason,
            'status'         => 'pending',
        ]);

        return redirect()->route('chofer.historial')
            ->with('success', 'Solicitud creada correctamente. Estado: Pendiente de aprobación.');
    }
}