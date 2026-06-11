<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:coupons,code|max:50',
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Coupon code is required',
            'code.unique' => 'This coupon code already exists',
            'discount_type.required' => 'Discount type is required',
            'discount_type.in' => 'Discount type must be either percentage or fixed',
            'discount_value.required' => 'Discount value is required',
            'discount_value.min' => 'Discount value must be greater than or equal to 0',
            'valid_until.after' => 'Expiry date must be after start date',
            'usage_limit.min' => 'Usage limit must be at least 1',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }
    }
}
