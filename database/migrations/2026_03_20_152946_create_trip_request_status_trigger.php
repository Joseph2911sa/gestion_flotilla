<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION update_trip_request_status()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.end_time IS NOT NULL THEN
                    UPDATE trip_requests
                    SET status = 'completed'
                    WHERE id = NEW.trip_request_id;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER trigger_update_trip_request_status
            AFTER UPDATE ON trips
            FOR EACH ROW
            EXECUTE FUNCTION update_trip_request_status();
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trigger_update_trip_request_status ON trips;
        ");

        DB::unprepared("
            DROP FUNCTION IF EXISTS update_trip_request_status();
        ");
    }
};