<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MantenimientoController extends Controller
{
    // ── GET /operador/mantenimientos ──────────────────────────────────────────
    public function index(Request $request)
    {
        $vehiculoFiltro = $request->query('vehicle_id', '');
        $statusFiltro   = $request->query('status', '');
        $page           = (int) $request->query('page', 1);

        $query = Maintenance::with('vehicle:id,plate,brand,model,status')->latest();

        if ($vehiculoFiltro) $query->where('vehicle_id', $vehiculoFiltro);
        if ($statusFiltro)   $query->where('status', $statusFiltro);

        $paginado  = $query->paginate(10, ['*'], 'page', $page);
        $vehiculos = Vehicle::orderBy('plate')->get(['id', 'plate', 'brand', 'model', 'status']);

        return view('operador.mantenimientos.index', [
            'mantenimientos' => $paginado->items(),
            'paginado'       => [
                'current_page' => $paginado->currentPage(),
                'last_page'    => $paginado->lastPage(),
                'total'        => $paginado->total(),
            ],
            'vehiculos'      => $vehiculos,
            'vehiculoFiltro' => $vehiculoFiltro,
            'statusFiltro'   => $statusFiltro,
        ]);
    }

    // ── POST /operador/mantenimientos ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'         => 'required|exists:vehicles,id',
            'type'               => 'required|in:preventive,corrective,inspection',
            'description'        => 'required|string|max:500',
            'start_date'         => 'required|date',
            'cost'               => 'nullable|numeric|min:0',
            'mileage_at_service' => 'nullable|integer|min:0',
        ], [
            'vehicle_id.required'  => 'Seleccione un vehículo.',
            'type.required'        => 'Seleccione el tipo de mantenimiento.',
            'description.required' => 'La descripción es obligatoria.',
            'start_date.required'  => 'La fecha de inicio es obligatoria.',
        ]);

        $vehicle = Vehicle::find($request->vehicle_id);

        if (!$vehicle->isAvailable()) {
            return back()->withInput()
                ->with('error', 'Solo vehículos disponibles pueden entrar a mantenimiento. Estado actual: ' . $vehicle->status);
        }

        if ($vehicle->openMaintenances()->exists()) {
            return back()->withInput()
                ->with('error', 'Este vehículo ya tiene un mantenimiento abierto.');
        }

        DB::transaction(function () use ($request, $vehicle) {
            Maintenance::create([
                'vehicle_id'         => $request->vehicle_id,
                'type'               => $request->type,
                'description'        => $request->description,
                'start_date'         => $request->start_date,
                'cost'               => $request->cost,
                'mileage_at_service' => $request->mileage_at_service,
                'status'             => Maintenance::STATUS_OPEN,
                'end_date'           => null,
            ]);
            $vehicle->markAsUnderMaintenance();
        });

        return back()->with('success', 'Mantenimiento abierto. El vehículo pasó a estado de mantenimiento.');
    }

    // ── PATCH /operador/mantenimientos/{id}/cerrar ────────────────────────────
    public function cerrar(Request $request, int $id)
    {
        $mantenimiento = Maintenance::with('vehicle')->findOrFail($id);

        if ($mantenimiento->isClosed()) {
            return back()->with('error', 'Este mantenimiento ya está cerrado.');
        }

        $request->validate([
            'end_date' => 'nullable|date|after_or_equal:' . $mantenimiento->start_date,
            'cost'     => 'nullable|numeric|min:0',
        ], [
            'end_date.after_or_equal' => 'La fecha de cierre no puede ser anterior al inicio.',
        ]);

        DB::transaction(function () use ($request, $mantenimiento) {
            $mantenimiento->update([
                'status'   => Maintenance::STATUS_CLOSED,
                'end_date' => $request->end_date ?? now()->toDateString(),
                'cost'     => $request->cost ?? $mantenimiento->cost,
            ]);

            $vehicle = Vehicle::find($mantenimiento->vehicle_id);
            if ($vehicle) {
                $vehicle->releaseToAvailableIfNoOpenMaintenances();
            }
        });

        return back()->with('success', 'Mantenimiento cerrado. El vehículo volvió a estado disponible.');
    }
}