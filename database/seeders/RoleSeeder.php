<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'Admin',
                'description' => 'Administrador del sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operador',
                'description' => 'Gestiona solicitudes y viajes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chofer',
                'description' => 'Conduce vehículos asignados',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}