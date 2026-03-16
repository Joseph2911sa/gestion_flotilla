<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_vehicle_available(
                p_vehicle_id BIGINT,
                p_departure  TIMESTAMP WITHOUT TIME ZONE,
                p_return     TIMESTAMP WITHOUT TIME ZONE
            )
            RETURNS BOOLEAN
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_vehicle_status    VARCHAR;
                v_maintenance_count INTEGER;
                v_overlap_count     INTEGER;
            BEGIN
                -- 1. Verificar que el vehículo existe y no está soft-deleted
                SELECT status
                  INTO v_vehicle_status
                  FROM vehicles
                 WHERE id = p_vehicle_id
                   AND deleted_at IS NULL;

                -- Si no existe o está borrado lógicamente, no está disponible
                IF NOT FOUND THEN
                    RETURN FALSE;
                END IF;

                -- 2. El vehículo solo está disponible si su estado es 'available'
                IF v_vehicle_status <> 'available' THEN
                    RETURN FALSE;
                END IF;

                -- 3. Verificar que no tenga mantenimientos abiertos
                SELECT COUNT(*)
                  INTO v_maintenance_count
                  FROM maintenances
                 WHERE vehicle_id = p_vehicle_id
                   AND status = 'open'
                   AND deleted_at IS NULL;

                IF v_maintenance_count > 0 THEN
                    RETURN FALSE;
                END IF;

                -- 4. Verificar que no exista solapamiento con solicitudes aprobadas
                SELECT COUNT(*)
                  INTO v_overlap_count
                  FROM trip_requests
                 WHERE vehicle_id = p_vehicle_id
                   AND status = 'approved'
                   AND deleted_at IS NULL
                   AND departure_date < p_return
                   AND return_date > p_departure;

                IF v_overlap_count > 0 THEN
                    RETURN FALSE;
                END IF;

                -- Si pasó todas las validaciones, el vehículo está disponible
                RETURN TRUE;
            END;
            \$\$;
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_vehicle_available(
                BIGINT,
                TIMESTAMP WITHOUT TIME ZONE,
                TIMESTAMP WITHOUT TIME ZONE
            );
        ");
    }
};