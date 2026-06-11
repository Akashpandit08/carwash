<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'partner_id' => ['nullable', 'exists:users,id'],
            'skills' => ['nullable', 'array'],
            'service_area' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'current_status' => ['nullable', 'in:available,busy,offline'],
        ];
    }
}
