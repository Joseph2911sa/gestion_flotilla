<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DirectAssignTripRequest extends FormRequest
{
    /**
     * Solo Admin y Operador pueden ejecutar asignación directa.
     * El middleware de ruta ya lo garantiza, pero authorize()
     * agrega una segunda capa de seguridad explícita.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ! $user->isDriver();
    }

    public function rules(): array
    {
        return [
            // ── Chofer destinatario ───────────────────────────────────────
            'user_id' => [
                'required',
                'integer',
                // Debe existir en la tabla users y no estar soft-deleted.
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],

            // ── Vehículo ──────────────────────────────────────────────────
            'vehicle_id' => [
                'required',
                'integer',
                Rule::exists('vehicles', 'id')->whereNull('deleted_at'),
            ],

            // ── Ruta (opcional) ───────────────────────────────────────────
            'route_id' => [
                'nullable',
                'integer',
                Rule::exists('routes', 'id')->whereNull('deleted_at'),
            ],

            // ── Fechas ────────────────────────────────────────────────────
            'departure_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'return_date' => [
                'required',
                'date',
                'after:departure_date',
            ],

            // ── Motivo (opcional) ─────────────────────────────────────────
            'reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'        => 'El chofer es obligatorio.',
            'user_id.exists'          => 'El chofer seleccionado no existe o fue eliminado.',
            'vehicle_id.required'     => 'El vehículo es obligatorio.',
            'vehicle_id.exists'       => 'El vehículo seleccionado no existe o fue eliminado.',
            'route_id.exists'         => 'La ruta seleccionada no existe o fue eliminada.',
            'departure_date.required' => 'La fecha de salida es obligatoria.',
            'departure_date.date'     => 'La fecha de salida no tiene un formato válido.',
            'departure_date.after_or_equal' => 'La fecha de salida no puede ser anterior a hoy.',
            'return_date.required'    => 'La fecha de retorno es obligatoria.',
            'return_date.date'        => 'La fecha de retorno no tiene un formato válido.',
            'return_date.after'       => 'La fecha de retorno debe ser posterior a la fecha de salida.',
        ];
    }
}