<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index()
    {
        $userId = session('user')['id'];

        $solicitudes = TripRequest::with(['vehicle:id,plate,brand,model', 'reviewer:id,name'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10);

        return view('chofer.historial.index', compact('solicitudes'));
    }

    public function cancelar(Request $request, $id)
    {
        $userId = session('user')['id'];

        $solicitud = TripRequest::with('vehicle')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Solo se puede cancelar si está pendiente o aprobada
        if (!in_array($solicitud->status, ['pending', 'approved'])) {
            return back()->with('error', 'Esta solicitud no puede ser cancelada.');
        }

        // ── Guardar estado ANTES de actualizar ────────────────────────────────
        $estabaAprobada = $solicitud->status === 'approved';

        // Cancelar la solicitud
        $solicitud->update(['status' => 'cancelled']);

        // ── Si estaba aprobada, verificar si el vehículo debe liberarse ───────
        if ($estabaAprobada && $solicitud->vehicle_id) {
            // Verificar si hay OTRA solicitud aprobada para el mismo vehículo
            $otraAprobada = TripRequest::where('vehicle_id', $solicitud->vehicle_id)
                ->where('status', 'approved')
                ->where('id', '!=', $solicitud->id)
                ->exists();

            // Solo liberar si no hay otra asignación vigente
            if (!$otraAprobada) {
                Vehicle::where('id', $solicitud->vehicle_id)
                    ->update(['status' => Vehicle::STATUS_AVAILABLE]);
            }
        }

        return back()->with('success', 'Solicitud cancelada correctamente.');
    }
}