<?php

namespace App\Http\Requests\Api;

use App\Constants\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignPartnerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'partner_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', UserRole::PARTNER),
            ],
        ];
    }
}
