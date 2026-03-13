<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            // Origin: every trip comes from an approved request
            $table->foreignId('trip_request_id')
                  ->unique()                         // 1-to-1 with trip_requests
                  ->constrained('trip_requests')
                  ->restrictOnDelete();

            // Denormalised for quick queries (also on the request, but useful here)
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            $table->foreignId('driver_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Route can be decided/changed at departure time
            $table->foreignId('route_id')
                  ->nullable()
                  ->constrained('routes')
                  ->nullOnDelete();

            // Actual departure & return (may differ from requested range)
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();

            // Mileage — business rule: end_mileage >= start_mileage
            $table->unsignedInteger('start_mileage')->nullable();
            $table->unsignedInteger('end_mileage')->nullable();

            $table->text('observations')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};