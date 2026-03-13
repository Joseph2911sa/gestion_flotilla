<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
 
            // Identification
            $table->string('plate', 20)->unique();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year');
 
            // Specifications
            $table->string('vehicle_type', 50);           // sedan, pick-up, SUV, etc.
            $table->unsignedTinyInteger('capacity');       // passenger capacity
            $table->string('fuel_type', 30);              // gasoline, diesel, electric, hybrid
 
            // Media
            $table->string('image_path', 500)->nullable(); // file path or URL
 
            // Operational state (enum, no extra table)
            $table->enum('status', [
                'available',
                'in_use',
                'maintenance',
                'out_of_service',
            ])->default('available');
 
            // Mileage tracking
            $table->unsignedInteger('mileage')->default(0); // current km
 
            $table->timestamps();
            $table->softDeletes();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};