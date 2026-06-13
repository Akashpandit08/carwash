<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePickupDriverRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $pickupDriver = $this->route('pickup_driver');
        $userId = $pickupDriver ? $pickupDriver->user_id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $userId],
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users,mobile_number,' . $userId],
            'password' => ['nullable', 'string', 'min:6'],
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'partner_id' => ['nullable', 'exists:users,id'],
            'vehicle_type' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string'],
            'service_area' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'location_lat' => ['nullable', 'numeric'],
            'location_lng' => ['nullable', 'numeric'],
            'service_radius' => ['nullable', 'integer', 'min:0'],
            'current_status' => ['nullable', 'in:available,busy,offline,active,inactive'],
        ];
    }
}
