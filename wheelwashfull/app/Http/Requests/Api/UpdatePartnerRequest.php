<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $partner = $this->route('partner');
        $userId = $partner ? $partner->user_id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $userId],
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users,mobile_number,' . $userId],
            'password' => ['nullable', 'string', 'min:6'],
            'service_city_id' => ['nullable', 'exists:service_cities,id'],
            'service_zone_id' => ['nullable', 'exists:service_zones,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'location_lat' => ['nullable', 'numeric'],
            'location_lng' => ['nullable', 'numeric'],
            'service_area' => ['nullable', 'string'],
            'service_radius' => ['nullable', 'integer', 'min:0'],
            'current_status' => ['nullable', 'in:active,inactive'],
            'commission_type' => ['nullable', 'in:percentage,fixed'],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
