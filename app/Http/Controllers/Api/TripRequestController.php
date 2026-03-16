<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequestRequest;
use App\Http\Requests\UpdateTripRequestRequest;
use App\Models\TripRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{
    // ─── Relaciones base para listados ───────────────────────────────────────

    /**
     * Relaciones cargadas en index().
     * No se incluye 'trip' para evitar consultas innecesarias en listados.
     */
    private array $listRelations = [
        'user:id,name,email',
        'vehicle:id,plate,brand,model',
        'route:id,name,origin,destination',
        'reviewer:id,name',
    ];

    /**
     * Relaciones completas para show().
     * Incluye 'trip' porque es una vista de detalle.
     */
    private array $detailRelations = [
        'user:id,name,email',
        'vehicle:id,plate,brand,model,status',
        'route:id,name,origin,destination',
        'reviewer:id,name',
        'trip',
    ];

    // ─── index ────────────────────────────────────────────────────────────────

    /**
     * Listar solicitudes.
     *
     * Chofer    → solo ve sus propias solicitudes.
     * Admin/Op  → ve todas, con filtro opcional ?status=
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = TripRequest::with($this->listRelations)->latest();

        if ($user->isDriver()) {
            // El chofer solo puede ver sus propias solicitudes.
            $query->where('user_id', $user->id);
        } else {
            // Operador y Admin pueden filtrar por estado.
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        }

        $requests = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Solicitudes recuperadas correctamente.',
            'data'    => $requests,
        ]);
    }

    // ─── store ────────────────────────────────────────────────────────────────

    /**
     * Crear solicitud.
     *
     * - user_id siempre viene de auth(), nunca del request.
     * - status siempre inicia como pending.
     * - vehicle_id y route_id son opcionales.
     */
    public function store(StoreTripRequestRequest $request): JsonResponse
    {
        $tripRequest = TripRequest::create([
            'user_id'        => $request->user()->id,
            'vehicle_id'     => $request->vehicle_id,
            'route_id'       => $request->route_id,
            'departure_date' => $request->departure_date,
            'return_date'    => $request->return_date,
            'reason'         => $request->reason,
            'status'         => TripRequest::STATUS_PENDING,
        ]);

        $tripRequest->load($this->listRelations);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada correctamente.',
            'data'    => $tripRequest,
        ], 201);
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    /**
     * Ver detalle de una solicitud.
     *
     * Chofer → solo puede ver sus propias solicitudes.
     * Admin/Op → pueden ver cualquiera.
     */
    public function show(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $user = $request->user();

        if ($user->isDriver() && $tripRequest->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver esta solicitud.',
            ], 403);
        }

        $tripRequest->load($this->detailRelations);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud recuperada correctamente.',
            'data'    => $tripRequest,
        ]);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    /**
     * Editar solicitud.
     *
     * Solo permitido si:
     * - El usuario no es Chofer (el Chofer no puede editar).
     * - La solicitud está en estado pending.
     */
    public function update(UpdateTripRequestRequest $request, TripRequest $tripRequest): JsonResponse
    {
        $user = $request->user();

        // El Chofer no tiene permiso para editar solicitudes.
        if ($user->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        // Solo se puede editar si la solicitud está pendiente.
        if (! $tripRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar solicitudes en estado pendiente. Estado actual: ' . $tripRequest->status . '.',
            ], 422);
        }

        // Calcular el rango final real:
        // usar la fecha del request si viene, o la ya guardada en BD si no viene.
        $finalDeparture = $request->filled('departure_date')
            ? $request->departure_date
            : $tripRequest->departure_date;

        $finalReturn = $request->filled('return_date')
            ? $request->return_date
            : $tripRequest->return_date;

        if ($finalReturn <= $finalDeparture) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha de retorno debe ser posterior a la fecha de salida.',
                'errors'  => [
                    'return_date' => ['La fecha de retorno debe ser posterior a la fecha de salida.'],
                ],
            ], 422);
        }

        $tripRequest->update($request->validated());

        $tripRequest->load($this->listRelations);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud actualizada correctamente.',
            'data'    => $tripRequest,
        ]);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    /**
     * Soft delete administrativo.
     *
     * Solo Admin/Operador (garantizado por middleware en rutas).
     *
     * Estados permitidos : pending, rejected, cancelled.
     * Estados prohibidos : approved (vehículo comprometido),
     *                      completed (historial operativo).
     */
    public function destroy(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $user = $request->user();

        // El Chofer no puede hacer borrado administrativo.
        if ($user->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        $forbidden = [
            TripRequest::STATUS_APPROVED,
            TripRequest::STATUS_COMPLETED,
        ];

        if (in_array($tripRequest->status, $forbidden)) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una solicitud en estado "' . $tripRequest->status . '". '
                           . 'Solo se permite eliminar solicitudes pendientes, rechazadas o canceladas.',
            ], 422);
        }

        $tripRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud eliminada correctamente.',
            'data'    => null,
        ]);
    }

    // ─── approve ──────────────────────────────────────────────────────────────

    /**
     * Aprobar solicitud.
     *
     * Solo Admin/Operador (garantizado por middleware en rutas).
     *
     * Validaciones en orden:
     * 1. Debe estar en pending.
     * 2. Debe tener vehicle_id asignado.
     * 3. El vehículo no debe tener mantenimientos abiertos.
     * 4. No debe existir solapamiento de fechas con otra solicitud approved del mismo vehículo.
     *
     * El controlador NO cambia el estado del vehículo.
     * Eso lo gestiona un trigger en la base de datos.
     */
    public function approve(Request $request, TripRequest $tripRequest): JsonResponse
    {
        // 1. Verificar que la solicitud está pendiente.
        if (! $tripRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden aprobar solicitudes en estado pendiente. Estado actual: ' . $tripRequest->status . '.',
            ], 422);
        }

        // 2. Verificar que tiene vehículo asignado.
        if (is_null($tripRequest->vehicle_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede aprobar una solicitud sin vehículo asignado.',
            ], 422);
        }

        // 3. Verificar que el vehículo exista.
        $vehicle = Vehicle::query()
            ->where('id', $tripRequest->vehicle_id)
            ->first();

        if (! $vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo asignado no existe o no está disponible.',
            ], 422);
        }

        // 4. Verificar que el vehículo esté disponible.
        if (! $vehicle->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo seleccionado no está disponible para aprobación. Estado actual: ' . $vehicle->status . '.',
            ], 422);
        }

        // 5. Verificar que no tenga mantenimientos abiertos.
        if ($vehicle->openMaintenances()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo seleccionado tiene un mantenimiento abierto y no puede ser asignado.',
            ], 422);
        }

        // 6. Verificar solapamiento de fechas.
        // Dos rangos se traslapan si: A_inicio < B_fin AND A_fin > B_inicio
        $overlap = TripRequest::where('vehicle_id', $tripRequest->vehicle_id)
            ->where('status', TripRequest::STATUS_APPROVED)
            ->where('id', '!=', $tripRequest->id)
            ->where('departure_date', '<', $tripRequest->return_date)
            ->where('return_date', '>', $tripRequest->departure_date)
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo ya tiene una solicitud aprobada que se traslapa con las fechas solicitadas.',
            ], 422);
        }

        // 7. Aprobar solicitud.
        $tripRequest->update([
            'status'      => TripRequest::STATUS_APPROVED,
            'reviewed_by' => $request->user()->id,
        ]);

        $tripRequest->load($this->listRelations);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada correctamente.',
            'data'    => $tripRequest,
        ]);
    }

    // ─── reject ───────────────────────────────────────────────────────────────

    /**
     * Rechazar solicitud.
     *
     * Solo Admin/Operador (garantizado por middleware en rutas).
     *
     * rejection_reason es opcional según el enunciado.
     */
    public function reject(Request $request, TripRequest $tripRequest): JsonResponse
    {
        // Solo se pueden rechazar solicitudes pendientes.
        if (! $tripRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden rechazar solicitudes en estado pendiente. Estado actual: ' . $tripRequest->status . '.',
            ], 422);
        }

        $tripRequest->update([
            'status'           => TripRequest::STATUS_REJECTED,
            'reviewed_by'      => $request->user()->id,
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        $tripRequest->load($this->listRelations);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada correctamente.',
            'data'    => $tripRequest,
        ]);
    }

    // ─── cancel ───────────────────────────────────────────────────────────────

    /**
     * Cancelar solicitud.
     *
     * Accesible por: Chofer (solo las propias), Admin, Operador.
     *
     * Diferencia con destroy():
     * - cancel() cambia el estado a 'cancelled'. El registro queda visible en el historial.
     * - destroy() hace soft delete. El registro desaparece de los listados normales.
     *
     * Si la solicitud estaba approved y tenía vehicle_id,
     * la liberación del vehículo la gestiona el trigger de la BD.
     */
    public function cancel(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $user = $request->user();

        // El Chofer solo puede cancelar sus propias solicitudes.
        if ($user->isDriver() && $tripRequest->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar esta solicitud.',
            ], 403);
        }

        // Usar el helper del modelo para validar si el estado permite cancelación.
        // isCancellable() permite: pending, approved.
        if (! $tripRequest->isCancellable()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud no puede cancelarse. Estado actual: ' . $tripRequest->status . '.',
            ], 422);
        }

        $tripRequest->update([
            'status' => TripRequest::STATUS_CANCELLED,
        ]);

        $tripRequest->load($this->listRelations);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud cancelada correctamente.',
            'data'    => $tripRequest,
        ]);
    }
}