<?php

namespace App\Http\Requests;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El middleware de ruta ya restringe a Admin y Operador.
        return true;
    }

    public function rules(): array
    {
        /*
         * Campos explícitamente permitidos para edición:
         *   type, description, start_date, cost, mileage_at_service
         *
         * Campos explícitamente excluidos (no deben llegar):
         *   vehicle_id  → no se permite reasignar el vehículo de un mantenimiento existente.
         *   status      → el estado se gestiona solo mediante store() y close().
         *   end_date    → la fecha de cierre solo la fija close().
         *
         * Todos los campos son opcionales (sometimes) para soportar PATCH parcial.
         */
        return [
            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(Maintenance::TYPES),
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],

            'start_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'cost' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'mileage_at_service' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'              => 'El tipo debe ser: ' . implode(', ', Maintenance::TYPES) . '.',
            'start_date.date'      => 'La fecha de inicio no tiene un formato válido.',
            'cost.numeric'         => 'El costo debe ser un valor numérico.',
            'cost.min'             => 'El costo no puede ser negativo.',
            'mileage_at_service.integer' => 'El kilometraje debe ser un número entero.',
            'mileage_at_service.min'     => 'El kilometraje no puede ser negativo.',
        ];
    }
}