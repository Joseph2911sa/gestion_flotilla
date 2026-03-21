<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
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

        $solicitud = TripRequest::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Solo se puede cancelar si está pendiente o aprobada
        if (!in_array($solicitud->status, ['pending', 'approved'])) {
            return back()->with('error', 'Esta solicitud no puede ser cancelada.');
        }

        $solicitud->update(['status' => 'cancelled']);

        // Si estaba aprobada, liberar el vehículo
        if ($solicitud->status === 'approved' && $solicitud->vehicle) {
            $otraAprobada = TripRequest::where('vehicle_id', $solicitud->vehicle_id)
                ->where('status', 'approved')
                ->where('id', '!=', $solicitud->id)
                ->exists();

            if (!$otraAprobada) {
                $solicitud->vehicle->update(['status' => 'available']);
            }
        }

        return back()->with('success', 'Solicitud cancelada correctamente.');
    }
}