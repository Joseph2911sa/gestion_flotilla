<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DirectAssignTripRequest;
use App\Http\Requests\StoreTripRequestRequest;
use App\Http\Requests\UpdateTripRequestRequest;
use App\Models\TripRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $query->where('user_id', $user->id);
        } else {
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

        if ($user->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        if (! $tripRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar solicitudes en estado pendiente. Estado actual: ' . $tripRequest->status . '.',
            ], 422);
        }

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
     * Estados permitidos: pending, rejected, cancelled.
     * Estados prohibidos: approved, completed.
     */
    public function destroy(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $user = $request->user();

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
     * Aprobar solicitud existente (flujo normal).
     *
     * Solo Admin/Operador (garantizado por middleware).
     * Reutiliza validateVehicleForAssignment() para no duplicar lógica.
     */
    public function approve(Request $request, TripRequest $tripRequest): JsonResponse
    {
        if (! $tripRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden aprobar solicitudes en estado pendiente. Estado actual: ' . $tripRequest->status . '.',
            ], 422);
        }

        if (is_null($tripRequest->vehicle_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede aprobar una solicitud sin vehículo asignado.',
            ], 422);
        }

        $vehicle = Vehicle::query()
            ->where('id', $tripRequest->vehicle_id)
            ->first();

        if (! $vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo asignado no existe o no está disponible.',
            ], 422);
        }

        // Delegar validaciones de negocio del vehículo al helper privado.
        // Se pasa $tripRequest->id para excluirlo del chequeo de solapamiento.
        $validationError = $this->validateVehicleForAssignment(
            $vehicle,
            $tripRequest->departure_date,
            $tripRequest->return_date,
            $tripRequest->id
        );

        if ($validationError !== null) {
            return $validationError;
        }

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
     * Solo Admin/Operador (garantizado por middleware).
     * rejection_reason es opcional.
     */
    public function reject(Request $request, TripRequest $tripRequest): JsonResponse
    {
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
     */
    public function cancel(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $user = $request->user();

        if ($user->isDriver() && $tripRequest->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar esta solicitud.',
            ], 403);
        }

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

    // ─── directAssign ─────────────────────────────────────────────────────────

    /**
     * Asignación directa (Tarjeta 15).
     *
     * Crea una TripRequest nueva directamente en status = approved.
     * No pasa por el flujo pending → approved.
     *
     * Solo Admin/Operador (garantizado por middleware y authorize() del FormRequest).
     *
     * Validaciones de campos: DirectAssignTripRequest.
     * Validaciones de negocio del vehículo: validateVehicleForAssignment().
     *
     * Toda la operación ocurre en una transacción de base de datos.
     */
    public function directAssign(DirectAssignTripRequest $request): JsonResponse
    {
        $vehicle = Vehicle::find($request->vehicle_id);

        // validateVehicleForAssignment() verifica:
        // 1. Vehículo disponible (status = available).
        // 2. Sin mantenimientos abiertos.
        // 3. Sin solapamiento de fechas con otras solicitudes approved.
        // No se pasa $excludeId porque esta es una solicitud nueva (no existe aún en BD).
        $validationError = $this->validateVehicleForAssignment(
            $vehicle,
            $request->departure_date,
            $request->return_date
        );

        if ($validationError !== null) {
            return $validationError;
        }

        $tripRequest = DB::transaction(function () use ($request) {
            return TripRequest::create([
                'user_id'        => $request->user_id,
                'vehicle_id'     => $request->vehicle_id,
                'route_id'       => $request->route_id,
                'departure_date' => $request->departure_date,
                'return_date'    => $request->return_date,
                'reason'         => $request->reason,
                // Campos fijados internamente — no vienen del request.
                'status'         => TripRequest::STATUS_APPROVED,
                'reviewed_by'    => $request->user()->id,
            ]);
        });

        $tripRequest->load($this->listRelations);

        return response()->json([
            'success' => true,
            'message' => 'Asignación directa creada correctamente.',
            'data'    => $tripRequest,
        ], 201);
    }

    // ─── Helper privado: validateVehicleForAssignment ─────────────────────────

    /**
     * Valida las reglas de negocio del vehículo antes de aprobar o asignar.
     *
     * Reutilizado por:
     * - approve()       → le pasa $excludeId = $tripRequest->id
     * - directAssign()  → no pasa $excludeId (solicitud aún no existe)
     *
     * Retorna un JsonResponse con error 422 si hay un problema,
     * o null si todas las validaciones pasan.
     *
     * @param  Vehicle       $vehicle
     * @param  string|Carbon $departureDate
     * @param  string|Carbon $returnDate
     * @param  int|null      $excludeId  ID de TripRequest a excluir del chequeo de solapamiento.
     * @return JsonResponse|null
     */
    private function validateVehicleForAssignment(
        Vehicle $vehicle,
        $departureDate,
        $returnDate,
        ?int $excludeId = null
    ): ?JsonResponse {

        // 1. El vehículo debe estar disponible.
        if (! $vehicle->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo seleccionado no está disponible. Estado actual: ' . $vehicle->status . '.',
            ], 422);
        }

        // 2. El vehículo no debe tener mantenimientos abiertos.
        if ($vehicle->openMaintenances()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo seleccionado tiene un mantenimiento abierto y no puede ser asignado.',
            ], 422);
        }

        // 3. No debe existir solapamiento de fechas con otra solicitud approved del mismo vehículo.
        // Regla: dos rangos se traslapan si A_inicio < B_fin AND A_fin > B_inicio
        $overlapQuery = TripRequest::where('vehicle_id', $vehicle->id)
            ->where('status', TripRequest::STATUS_APPROVED)
            ->where('departure_date', '<', $returnDate)
            ->where('return_date', '>', $departureDate);

        if ($excludeId !== null) {
            $overlapQuery->where('id', '!=', $excludeId);
        }

        if ($overlapQuery->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo ya tiene una solicitud aprobada que se traslapa con las fechas solicitadas.',
            ], 422);
        }

        return null;
    }
}