<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreTripRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El control de acceso por rol se maneja en las rutas y el controlador.
    }

    public function rules(): array
    {
        return [
            'departure_date' => ['required', 'date'],
            'return_date'    => ['required', 'date', 'after:departure_date'],
            'vehicle_id'     => ['nullable', 'integer', Rule::exists('vehicles', 'id')->whereNull('deleted_at')],
            'route_id'       => ['nullable', 'integer', Rule::exists('routes', 'id')->whereNull('deleted_at')],
            'reason'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'departure_date.required' => 'La fecha de salida es obligatoria.',
            'departure_date.date'     => 'La fecha de salida no tiene un formato válido.',
            'return_date.required'    => 'La fecha de retorno es obligatoria.',
            'return_date.date'        => 'La fecha de retorno no tiene un formato válido.',
            'return_date.after'       => 'La fecha de retorno debe ser posterior a la fecha de salida.',
            'vehicle_id.integer'      => 'El vehículo seleccionado no es válido.',
            'vehicle_id.exists'       => 'El vehículo seleccionado no existe.',
            'route_id.integer'        => 'La ruta seleccionada no es válida.',
            'route_id.exists'         => 'La ruta seleccionada no existe.',
            'reason.string'           => 'El motivo debe ser texto.',
            'reason.max'              => 'El motivo no puede superar los 1000 caracteres.',
        ];
    }

    /**
     * Devuelve respuesta JSON consistente ante errores de validación.
     * Evita que Laravel redirija en contexto API.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Los datos enviados no son válidos.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}