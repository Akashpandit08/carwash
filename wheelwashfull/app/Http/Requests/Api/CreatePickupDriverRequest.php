<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreatePickupDriverRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users,mobile_number'],
            'password' => ['nullable', 'string', 'min:6'],
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'partner_id' => ['nullable', 'exists:users,id'],
            'vehicle_type' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string'],
            'service_area' => ['nullable', 'string'],
            'service_radius' => ['nullable', 'integer', 'min:0'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'location_lat' => ['nullable', 'numeric'],
            'location_lng' => ['nullable', 'numeric'],
            'current_status' => ['nullable', 'in:available,busy,offline,active,inactive'],
        ];
    }
}
