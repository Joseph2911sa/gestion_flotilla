<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    use ApiConsumer;

    public function index()
    {
        $response  = $this->apiGet('vehicles', ['status' => 'available', 'per_page' => 999]);
        $vehiculos = collect($response->json('data.data') ?? []);

        return view('chofer.solicitudes.index', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'     => 'required',
            'departure_date' => 'required|date',
            'return_date'    => 'required|date|after:departure_date',
            'reason'         => 'nullable|string|max:500',
        ], [
            'vehicle_id.required'     => 'Debe seleccionar un vehículo.',
            'departure_date.required' => 'La fecha de inicio es obligatoria.',
            'return_date.required'    => 'La fecha de fin es obligatoria.',
            'return_date.after'       => 'La fecha de fin debe ser mayor a la de inicio.',
        ]);

        $response = $this->apiPost('trip-requests', [
            'vehicle_id'     => (int) $request->vehicle_id,
            'departure_date' => $request->departure_date,
            'return_date'    => $request->return_date,
            'reason'         => $request->reason,
        ]);

        if ($response->failed()) {
            return $this->handleError($response, 'Error al crear la solicitud.');
        }

        return redirect()->route('chofer.historial')
            ->with('success', 'Solicitud creada correctamente. Estado: Pendiente de aprobación.');
    }
}