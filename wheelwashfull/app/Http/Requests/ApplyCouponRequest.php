<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'customer';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'coupon_code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'coupon_code.required' => 'Coupon code is required',
            'order_amount.required' => 'Order amount is required',
            'order_amount.min' => 'Order amount must be greater than or equal to 0',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('coupon_code')) {
            $this->merge([
                'coupon_code' => strtoupper($this->coupon_code),
            ]);
        }
    }
}
