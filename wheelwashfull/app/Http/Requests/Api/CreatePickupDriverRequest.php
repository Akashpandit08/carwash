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
            'email' => ['nullable', 'email'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'partner_id' => ['nullable', 'exists:users,id'],
            'vehicle_type' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string'],
            'service_area' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'current_status' => ['nullable', 'in:available,busy,offline'],
        ];
    }
}
