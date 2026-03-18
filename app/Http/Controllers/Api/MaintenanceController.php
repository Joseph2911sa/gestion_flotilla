<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    // ─── index ────────────────────────────────────────────────────────────────

    /**
     * Listar mantenimientos.
     *
     * Filtros opcionales:
     * - ?vehicle_id=  → filtra por vehículo
     * - ?status=      → filtra por estado (open / closed)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Maintenance::with([
            'vehicle:id,plate,brand,model,status',
        ])->latest();

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Registros de mantenimiento recuperados correctamente.',
            'data'    => $maintenances,
        ]);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    /**
     * Crear mantenimiento.
     *
     * Reglas de negocio (en orden):
     * 1. El vehículo debe estar en status = available.
     *    → in_use y out_of_service bloquean la creación.
     * 2. No debe tener otro mantenimiento open.
     * 3. El mantenimiento se crea con status = open.
     * 4. El vehículo pasa a status = maintenance.
     *
     * Todo ocurre en una transacción de base de datos.
     */
    public function store(StoreMaintenanceRequest $request): JsonResponse
    {
        $vehicle = Vehicle::find($request->vehicle_id);

        // 1. El vehículo solo puede entrar a mantenimiento desde 'available'.
        if (! $vehicle->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede registrar mantenimiento para vehículos disponibles. Estado actual: ' . $vehicle->status . '.',
            ], 422);
        }

        // 2. No debe tener otro mantenimiento abierto.
        if ($vehicle->openMaintenances()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo ya tiene un mantenimiento abierto.',
            ], 422);
        }

        // 3 y 4. Crear mantenimiento y cambiar estado del vehículo en transacción.
        $maintenance = DB::transaction(function () use ($request, $vehicle) {
            $maintenance = Maintenance::create([
                'vehicle_id'         => $request->vehicle_id,
                'type'               => $request->type,
                'description'        => $request->description,
                'start_date'         => $request->start_date,
                'cost'               => $request->cost,
                'mileage_at_service' => $request->mileage_at_service,
                // Siempre fijados internamente:
                'status'             => Maintenance::STATUS_OPEN,
                'end_date'           => null,
            ]);

            $vehicle->markAsUnderMaintenance();

            return $maintenance;
        });

        $maintenance->load('vehicle:id,plate,brand,model,status');

        return response()->json([
            'success' => true,
            'message' => 'Mantenimiento registrado correctamente. El vehículo ha pasado a estado de mantenimiento.',
            'data'    => $maintenance,
        ], 201);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    /**
     * Ver detalle de un mantenimiento.
     */
    public function show(Maintenance $maintenance): JsonResponse
    {
        $maintenance->load('vehicle:id,plate,brand,model,status,mileage');

        return response()->json([
            'success' => true,
            'message' => 'Registro de mantenimiento recuperado correctamente.',
            'data'    => $maintenance,
        ]);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    /**
     * Actualizar mantenimiento.
     *
     * Campos editables: type, description, start_date, cost, mileage_at_service.
     * Campos bloqueados: vehicle_id, status, end_date.
     *
     * Restricción: no se permite editar un mantenimiento cerrado.
     * El estado cerrado es definitivo; cualquier corrección debe hacerse
     * antes del cierre.
     */
    public function update(UpdateMaintenanceRequest $request, Maintenance $maintenance): JsonResponse
    {
        // No se permite modificar mantenimientos ya cerrados.
        if ($maintenance->isClosed()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede modificar un mantenimiento cerrado.',
            ], 422);
        }

        // Solo se actualizan los campos explícitamente permitidos.
        // validated() ya excluye cualquier campo no declarado en las rules().
        $maintenance->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Mantenimiento actualizado correctamente.',
            'data'    => $maintenance,
        ]);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    /**
     * Soft delete de mantenimiento.
     *
     * Restricción: solo se puede borrar mantenimientos closed.
     * Un mantenimiento open no puede borrarse porque el vehículo
     * está asociado a ese estado activo.
     */
    public function destroy(Maintenance $maintenance): JsonResponse
    {
        if ($maintenance->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un mantenimiento abierto. Ciérrelo antes de eliminarlo.',
            ], 422);
        }

        $maintenance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro de mantenimiento eliminado correctamente.',
            'data'    => null,
        ]);
    }

    // ─── close ────────────────────────────────────────────────────────────────

    /**
     * Cerrar mantenimiento.
     *
     * Reglas de negocio (en orden):
     * 1. El mantenimiento debe estar open.
     * 2. Se actualiza a closed con end_date y cost opcionales.
     * 3. Si no quedan otros mantenimientos abiertos del mismo vehículo,
     *    y el vehículo sigue en status = maintenance,
     *    el vehículo vuelve a status = available.
     *    (La condición de status = maintenance la garantiza releaseToAvailableIfNoOpenMaintenances())
     *
     * Todo ocurre en una transacción de base de datos.
     */
    public function close(Request $request, Maintenance $maintenance): JsonResponse
    {
        // 1. Solo se puede cerrar un mantenimiento open.
        if (! $maintenance->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Este mantenimiento ya está cerrado.',
            ], 422);
        }

        // Validación de los campos opcionales del cierre.
        $request->validate([
            'end_date' => ['nullable', 'date', 'after_or_equal:' . $maintenance->start_date],
            'cost'     => ['nullable', 'numeric', 'min:0'],
        ]);

        // 2 y 3. Cerrar mantenimiento y liberar vehículo si corresponde.
        DB::transaction(function () use ($request, $maintenance) {
            $maintenance->update([
                'status'   => Maintenance::STATUS_CLOSED,
                'end_date' => $request->input('end_date', now()->toDateString()),
                'cost'     => $request->input('cost', $maintenance->cost),
            ]);

            // Recargar el vehículo para tener el estado actualizado en memoria.
            $vehicle = Vehicle::find($maintenance->vehicle_id);

            if ($vehicle) {
                // Solo cambia a available si:
                //   - status actual del vehículo es maintenance, Y
                //   - no quedan otros mantenimientos abiertos.
                $vehicle->releaseToAvailableIfNoOpenMaintenances();
            }
        });

        // Refrescar el modelo para que la respuesta refleje el estado final.
        $maintenance->refresh();
        $maintenance->load('vehicle:id,plate,brand,model,status');

        return response()->json([
            'success' => true,
            'message' => 'Mantenimiento cerrado correctamente.',
            'data'    => $maintenance,
        ]);
    }
}