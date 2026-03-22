<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Route;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    // ── GET /operador/solicitudes ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $statusFiltro = $request->query('status', 'pending');
        $page         = (int) $request->query('page', 1);

        $query = TripRequest::with([
            'user:id,name,email',
            'vehicle:id,plate,brand,model,status',
            'route:id,name,origin,destination',
            'reviewer:id,name',
        ])->latest();

        if ($statusFiltro && $statusFiltro !== 'all') {
            $query->where('status', $statusFiltro);
        }

        $paginado = $query->paginate(10, ['*'], 'page', $page);

        // Contadores por estado para los tabs
        $contadores = [
            'pending'   => TripRequest::where('status', 'pending')->count(),
            'approved'  => TripRequest::where('status', 'approved')->count(),
            'rejected'  => TripRequest::where('status', 'rejected')->count(),
            'cancelled' => TripRequest::where('status', 'cancelled')->count(),
        ];

        return view('operador.solicitudes.index', [
            'solicitudes'  => $paginado->items(),
            'paginado'     => [
                'current_page' => $paginado->currentPage(),
                'last_page'    => $paginado->lastPage(),
                'total'        => $paginado->total(),
            ],
            'statusFiltro' => $statusFiltro,
            'contadores'   => $contadores,
        ]);
    }

    // ── PATCH /operador/solicitudes/{id}/aprobar ──────────────────────────────
    public function aprobar(Request $request, int $id)
    {
        $solicitud = TripRequest::with('vehicle')->find($id);

        if (!$solicitud) {
            return back()->with('error', 'Solicitud no encontrada.');
        }

        if (!$solicitud->isPending()) {
            return back()->with('error',
                'Solo se pueden aprobar solicitudes pendientes. Estado actual: ' . $solicitud->status);
        }

        if (!$solicitud->vehicle_id) {
            return back()->with('error',
                'No se puede aprobar una solicitud sin vehículo asignado.');
        }

        $vehicle = $solicitud->vehicle;

        if (!$vehicle->isAvailable()) {
            return back()->with('error',
                'El vehículo no está disponible. Estado actual: ' . $vehicle->status);
        }

        if ($vehicle->openMaintenances()->exists()) {
            return back()->with('error',
                'El vehículo tiene un mantenimiento abierto y no puede ser asignado.');
        }

        // Verificar solapamiento
        $traslape = TripRequest::where('vehicle_id', $solicitud->vehicle_id)
            ->where('status', 'approved')
            ->where('id', '!=', $id)
            ->where('departure_date', '<', $solicitud->return_date)
            ->where('return_date',    '>', $solicitud->departure_date)
            ->exists();

        if ($traslape) {
            return back()->with('error',
                'El vehículo ya tiene una asignación aprobada que se traslapa con estas fechas.');
        }

        // Aprobar solicitud
        $solicitud->update([
            'status'      => TripRequest::STATUS_APPROVED,
            'reviewed_by' => session('user')['id'],
        ]);

        // Cambiar estado del vehículo a "in_use"
        $vehicle->update(['status' => Vehicle::STATUS_IN_USE]);

        return back()->with('success', 'Solicitud aprobada correctamente.');
    }

    // ── PATCH /operador/solicitudes/{id}/rechazar ─────────────────────────────
    public function rechazar(Request $request, int $id)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $solicitud = TripRequest::with('vehicle')->find($id);

        if (!$solicitud) {
            return back()->with('error', 'Solicitud no encontrada.');
        }

        if (!$solicitud->isPending()) {
            return back()->with('error',
                'Solo se pueden rechazar solicitudes pendientes. Estado actual: ' . $solicitud->status);
        }

        // Rechazar solicitud
        $solicitud->update([
            'status'           => TripRequest::STATUS_REJECTED,
            'reviewed_by'      => session('user')['id'],
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Liberar vehículo si tenía uno asignado y estaba en uso
        if ($solicitud->vehicle) {
            $solicitud->vehicle->update(['status' => Vehicle::STATUS_AVAILABLE]);
        }

        return back()->with('success', 'Solicitud rechazada correctamente.');
    }

    // ── POST /operador/solicitudes/asignacion-directa ─────────────────────────
    public function asignacionDirecta(Request $request)
    {
        $request->validate([
            'user_id'        => 'required|exists:users,id',
            'vehicle_id'     => 'required|exists:vehicles,id',
            'departure_date' => 'required|date|after:now',
            'return_date'    => 'required|date|after:departure_date',
            'route_id'       => 'nullable|exists:routes,id',
            'reason'         => 'nullable|string|max:500',
        ], [
            'user_id.required'        => 'Seleccione un chofer.',
            'vehicle_id.required'     => 'Seleccione un vehículo.',
            'departure_date.required' => 'La fecha de salida es obligatoria.',
            'departure_date.after'    => 'La fecha de salida debe ser futura.',
            'return_date.required'    => 'La fecha de retorno es obligatoria.',
            'return_date.after'       => 'La fecha de retorno debe ser posterior a la de salida.',
        ]);

        $vehicle = Vehicle::find($request->vehicle_id);

        if (!$vehicle->isAvailable()) {
            return back()
                ->withInput()
                ->with('error', 'El vehículo no está disponible. Estado: ' . $vehicle->status);
        }

        if ($vehicle->openMaintenances()->exists()) {
            return back()
                ->withInput()
                ->with('error', 'El vehículo tiene un mantenimiento abierto.');
        }

        // Verificar solapamiento
        $traslape = TripRequest::where('vehicle_id', $request->vehicle_id)
            ->where('status', 'approved')
            ->where('departure_date', '<', $request->return_date)
            ->where('return_date',    '>', $request->departure_date)
            ->exists();

        if ($traslape) {
            return back()
                ->withInput()
                ->with('error', 'El vehículo tiene una asignación aprobada que se traslapa con esas fechas.');
        }

        // Crear asignación directa ya aprobada
        TripRequest::create([
            'user_id'        => $request->user_id,
            'vehicle_id'     => $request->vehicle_id,
            'route_id'       => $request->route_id,
            'departure_date' => $request->departure_date,
            'return_date'    => $request->return_date,
            'reason'         => $request->reason,
            'status'         => TripRequest::STATUS_APPROVED,
            'reviewed_by'    => session('user')['id'],
        ]);

        // Cambiar estado del vehículo a "in_use"
        $vehicle->update(['status' => Vehicle::STATUS_IN_USE]);

        return redirect()->route('operador.solicitudes')
            ->with('success', 'Asignación directa creada correctamente.');
    }
}