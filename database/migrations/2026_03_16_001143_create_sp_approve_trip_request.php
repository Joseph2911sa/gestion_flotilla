<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * TARJETA 23 — Procedimiento almacenado de aprobación de solicitud
 *
 * Crea el procedimiento sp_approve_trip_request en PostgreSQL.
 *
 * El procedimiento ejecuta la aprobación completa de una solicitud
 * de viaje con todas sus validaciones de negocio.
 *
 * Validaciones en orden:
 *   1. La solicitud existe y no está soft-deleted.
 *   2. La solicitud está en estado 'pending'.
 *   3. La solicitud tiene vehicle_id asignado.
 *   4. Debe existir un usuario revisor válido.
 *   5. El vehículo está disponible para el rango de fechas
 *      (reutiliza fn_vehicle_available).
 *
 * Si todo pasa:
 *   - Actualiza status = 'approved'
 *   - Actualiza reviewed_by = p_reviewed_by
 *
 * Si algo falla:
 *   - Lanza RAISE EXCEPTION con mensaje descriptivo.
 *
 * NOTA: Este procedimiento coexiste con el método approve() del
 * TripRequestController. NO lo reemplaza como flujo principal.
 * Existe como objeto de BD demostrable e integrado vía endpoint
 * separado POST /api/v1/trip-requests/{id}/approve-db.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_approve_trip_request(
                p_trip_request_id BIGINT,
                p_reviewed_by     BIGINT
            )
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_request      trip_requests%ROWTYPE;
                v_is_available BOOLEAN;
            BEGIN
                -- 1. Buscar la solicitud (excluye soft-deleted)
                SELECT *
                  INTO v_request
                  FROM trip_requests
                 WHERE id = p_trip_request_id
                   AND deleted_at IS NULL;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'SOLICITUD_NO_ENCONTRADA: La solicitud con id % no existe o fue eliminada.', p_trip_request_id;
                END IF;

                -- 2. Verificar que la solicitud está en pending
                IF v_request.status <> 'pending' THEN
                    RAISE EXCEPTION 'ESTADO_INVALIDO: La solicitud no está en estado pendiente. Estado actual: %.', v_request.status;
                END IF;

                -- 3. Verificar que tiene vehículo asignado
                IF v_request.vehicle_id IS NULL THEN
                    RAISE EXCEPTION 'VEHICULO_REQUERIDO: La solicitud no tiene vehículo asignado. Asigne un vehículo antes de aprobar.';
                END IF;

                -- 4. Verificar que exista un revisor válido
                IF p_reviewed_by IS NULL THEN
                    RAISE EXCEPTION 'REVISOR_REQUERIDO: Debe indicar el usuario que aprueba la solicitud.';
                END IF;

                -- 5. Verificar disponibilidad del vehículo
                SELECT fn_vehicle_available(
                    v_request.vehicle_id,
                    v_request.departure_date,
                    v_request.return_date
                ) INTO v_is_available;

                IF NOT v_is_available THEN
                    RAISE EXCEPTION 'VEHICULO_NO_DISPONIBLE: El vehículo con id % no está disponible para el rango de fechas solicitado. Verifique mantenimientos abiertos o solicitudes aprobadas que se traslapen.', v_request.vehicle_id;
                END IF;

                -- 6. Aprobar la solicitud
                UPDATE trip_requests
                   SET status      = 'approved',
                       reviewed_by = p_reviewed_by,
                       updated_at  = NOW()
                 WHERE id = p_trip_request_id
                   AND deleted_at IS NULL;

            END;
            \$\$;
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_approve_trip_request(BIGINT, BIGINT);');
    }
};