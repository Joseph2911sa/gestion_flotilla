<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
 
            $table->string('name', 150);                        // e.g. "San José - Heredia"
            $table->string('origin', 150);
            $table->string('destination', 150);
            $table->decimal('distance_km', 8, 2)->nullable();   // estimated distance
            $table->unsignedSmallInteger('estimated_minutes')->nullable(); // travel time estimate
            $table->text('description')->nullable();
 
            $table->timestamps();
            $table->softDeletes();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
 