<?php

namespace App\Http\Requests\Api;

use App\Constants\ServiceMode;
use App\Constants\WashType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'service_id' => ['required', 'exists:services,id'],
            'wash_type' => ['nullable', Rule::in(WashType::ALL)],
            'service_mode' => ['nullable', Rule::in(ServiceMode::ALL)],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required_without:slot_time'],
            'slot_time' => ['nullable'],
            'address' => ['required', 'string'],
            'latitude' => ['nullable', Rule::requiredIf(fn () => request()->filled('wash_type')), 'numeric'],
            'longitude' => ['nullable', Rule::requiredIf(fn () => request()->filled('wash_type')), 'numeric'],
            'payment_method' => ['required', 'in:cod,online,subscription'],
            'customer_subscription_id' => ['nullable', 'exists:customer_subscriptions,id'],
            'coupon_code' => ['nullable', 'string'],
            'address_id' => ['nullable', 'exists:addresses,id'],
            'pickup_address_id' => [
                'nullable',
                Rule::requiredIf(fn () => request('service_mode') === ServiceMode::PICKUP_DROP),
                'exists:addresses,id'
            ],
            'drop_address_id' => [
                'nullable',
                Rule::requiredIf(fn () => request('service_mode') === ServiceMode::PICKUP_DROP),
                'exists:addresses,id'
            ],
            'pickup_date' => [
                'nullable',
                Rule::requiredIf(fn () => request('service_mode') === ServiceMode::PICKUP_DROP),
                'date',
                'after_or_equal:today'
            ],
            'pickup_time_slot' => [
                'nullable',
                Rule::requiredIf(fn () => request('service_mode') === ServiceMode::PICKUP_DROP),
                'string'
            ],
            'notes' => ['nullable', 'string'],
        ];
    }
}
