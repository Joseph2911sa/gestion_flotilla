<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Route;
use Illuminate\Http\Request;

class ViajeController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with([
            'driver:id,name,email',
            'vehicle:id,plate,brand,model',
            'route:id,name,origin,destination',
            'tripRequest:id,departure_date,return_date,status',
        ])->latest();

        // Filtros opcionales
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $viajes = $query->paginate(10);

        // Para los selectores del formulario de registro
        $solicitudesAprobadas = TripRequest::with(['user:id,name', 'vehicle:id,plate,brand,model'])
            ->where('status', 'approved')
            ->whereDoesntHave('trip')
            ->get();

        $choferes  = User::whereHas('role', fn($q) => $q->where('name', 'Chofer'))->get();
        $vehiculos = Vehicle::whereIn('status', ['available', 'in_use'])->get();
        $rutas     = Route::all();

        return view('operador.viajes.index', compact(
            'viajes',
            'solicitudesAprobadas',
            'choferes',
            'vehiculos',
            'rutas'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'trip_request_id' => 'required|exists:trip_requests,id',
            'start_mileage'   => 'required|integer|min:0',
            'start_time'      => 'required|date',
            'observations'    => 'nullable|string|max:500',
        ], [
            'trip_request_id.required' => 'Seleccione una solicitud aprobada.',
            'start_mileage.required'   => 'El kilometraje inicial es obligatorio.',
            'start_time.required'      => 'La fecha/hora de salida es obligatoria.',
        ]);

        $solicitud = TripRequest::with('vehicle')->find($request->trip_request_id);

        // Crear el viaje
        Trip::create([
            'trip_request_id' => $solicitud->id,
            'vehicle_id'      => $solicitud->vehicle_id,
            'driver_id'       => $solicitud->user_id,
            'route_id'        => $solicitud->route_id,
            'start_time'      => $request->start_time,
            'start_mileage'   => $request->start_mileage,
            'observations'    => $request->observations,
        ]);

        return back()->with('success', 'Salida registrada correctamente.');
    }

    public function registrarRetorno(Request $request, $id)
    {
        $request->validate([
            'end_time'     => 'required|date',
            'end_mileage'  => 'required|integer|min:0',
            'observations' => 'nullable|string|max:500',
        ], [
            'end_time.required'    => 'La fecha/hora de retorno es obligatoria.',
            'end_mileage.required' => 'El kilometraje final es obligatorio.',
        ]);

        $viaje = Trip::find($id);

        if (!$viaje) {
            return back()->with('error', 'Viaje no encontrado.');
        }

        if ($viaje->isCompleted()) {
            return back()->with('error', 'Este viaje ya fue completado.');
        }

        // Validar kilometraje
        if ($request->end_mileage < $viaje->start_mileage) {
            return back()->with('error',
                "El kilometraje final ({$request->end_mileage}) no puede ser menor al inicial ({$viaje->start_mileage}).");
        }

        $viaje->update([
            'end_time'     => $request->end_time,
            'end_mileage'  => $request->end_mileage,
            'observations' => $request->observations ?? $viaje->observations,
        ]);

        // Liberar vehículo
        if ($viaje->vehicle) {
            $viaje->vehicle->update(['status' => Vehicle::STATUS_AVAILABLE]);
        }

        // Marcar solicitud como completada
        if ($viaje->tripRequest) {
            $viaje->tripRequest->update(['status' => 'completed']);
        }

        return back()->with('success', 'Retorno registrado correctamente. Vehículo liberado.');
    }
}