<?php

namespace App\Http\Requests;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El middleware de ruta ya restringe a Admin y Operador.
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Vehículo ──────────────────────────────────────────────────
            'vehicle_id' => [
                'required',
                'integer',
                Rule::exists('vehicles', 'id')->whereNull('deleted_at'),
            ],

            // ── Tipo de mantenimiento ─────────────────────────────────────
            'type' => [
                'required',
                'string',
                Rule::in(Maintenance::TYPES),
            ],

            // ── Descripción (opcional) ────────────────────────────────────
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            // ── Fecha de inicio ───────────────────────────────────────────
            'start_date' => [
                'required',
                'date',
            ],

            // ── Costo (opcional) ──────────────────────────────────────────
            'cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            // ── Kilometraje al servicio (opcional) ────────────────────────
            'mileage_at_service' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required'  => 'El vehículo es obligatorio.',
            'vehicle_id.exists'    => 'El vehículo seleccionado no existe o fue eliminado.',
            'type.required'        => 'El tipo de mantenimiento es obligatorio.',
            'type.in'              => 'El tipo debe ser: ' . implode(', ', Maintenance::TYPES) . '.',
            'start_date.required'  => 'La fecha de inicio es obligatoria.',
            'start_date.date'      => 'La fecha de inicio no tiene un formato válido.',
            'cost.numeric'         => 'El costo debe ser un valor numérico.',
            'cost.min'             => 'El costo no puede ser negativo.',
            'mileage_at_service.integer' => 'El kilometraje debe ser un número entero.',
            'mileage_at_service.min'     => 'El kilometraje no puede ser negativo.',
        ];
    }
}