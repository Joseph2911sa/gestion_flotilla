<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware (role:Admin,Operador)
    }

    public function rules(): array
    {
        return [
            'plate'        => ['required', 'string', 'max:20', 'unique:vehicles,plate'],
            'brand'        => ['required', 'string', 'max:100'],
            'model'        => ['required', 'string', 'max:100'],
            'year'         => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'capacity'     => ['required', 'integer', 'min:1'],
            'fuel_type'    => ['required', 'string', 'max:30'],
            'image'        => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'       => ['nullable', 'string', 'in:' . implode(',', Vehicle::STATUSES)],
            'mileage'      => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate.unique'       => 'The plate number is already registered in the system.',
            'year.min'           => 'The year must be 1900 or later.',
            'year.max'           => 'The year cannot be in the future.',
            'capacity.min'       => 'Capacity must be at least 1 passenger.',
            'mileage.min'        => 'Mileage cannot be negative.',
            'image.image'        => 'The file must be a valid image.',
            'image.mimes'        => 'Accepted image formats: jpg, jpeg, png, webp.',
            'image.max'          => 'The image must not exceed 2MB.',
            'status.in'          => 'The selected status is not valid.',
        ];
    }
}