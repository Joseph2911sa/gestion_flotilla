<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware (role:Admin,Operador)
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'plate'        => ['sometimes', 'string', 'max:20', 'unique:vehicles,plate,' . $vehicleId],
            'brand'        => ['sometimes', 'string', 'max:100'],
            'model'        => ['sometimes', 'string', 'max:100'],
            'year'         => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vehicle_type' => ['sometimes', 'string', 'max:50'],
            'capacity'     => ['sometimes', 'integer', 'min:1'],
            'fuel_type'    => ['sometimes', 'string', 'max:30'],
            'image'        => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'       => ['sometimes', 'string', 'in:' . implode(',', Vehicle::STATUSES)],
            // mileage is intentionally excluded — updated only via the trip return flow
            'mileage'      => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate.unique'       => 'The plate number is already registered in the system.',
            'year.min'           => 'The year must be 1900 or later.',
            'year.max'           => 'The year cannot be in the future.',
            'capacity.min'       => 'Capacity must be at least 1 passenger.',
            'image.image'        => 'The file must be a valid image.',
            'image.mimes'        => 'Accepted image formats: jpg, jpeg, png, webp.',
            'image.max'          => 'The image must not exceed 2MB.',
            'status.in'          => 'The selected status is not valid.',
            'mileage.prohibited' => 'Mileage cannot be updated directly. It is managed exclusively by the trip return process.',
        ];
    }
}