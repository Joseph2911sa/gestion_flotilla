<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_requests', function (Blueprint $table) {
            $table->id();

            // Who is requesting
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Vehicle may be assigned later by operator
            $table->foreignId('vehicle_id')
                  ->nullable()
                  ->constrained('vehicles')
                  ->nullOnDelete();

            // Requested route
            $table->foreignId('route_id')
                  ->nullable()
                  ->constrained('routes')
                  ->nullOnDelete();

            // Who approved/rejected the request
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Requested dates
            $table->dateTime('departure_date');
            $table->dateTime('return_date');

            // Request status
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'completed',
            ])->default('pending');

            // Request details
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_requests');
    }
};