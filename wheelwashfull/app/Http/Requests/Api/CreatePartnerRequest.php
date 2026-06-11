<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreatePartnerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'business_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'service_area' => ['nullable', 'string'],
            'current_status' => ['nullable', 'in:active,inactive'],
            'commission_type' => ['nullable', 'in:percentage,fixed'],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
