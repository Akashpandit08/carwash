<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'final_price' => $this->final_price,
            'total_amount' => $this->total_amount,
            'payable_amount' => $this->payable_amount ?? $this->total_amount,
            'coupon_id' => $this->coupon_id,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'is_subscription_booking' => ($this->booking_source === 'subscription' || $this->payment_method === 'subscription'),
            'status' => $this->status,
            'wash_type' => $this->wash_type,
            'service_mode' => $this->service_mode,
            'booking_date' => optional($this->booking_date)->toDateString(),
            'slot_time' => $this->slot_time,
            'address' => $this->address,
            'pickup_address' => $this->pickupAddress?->full_address ?? $this->address,
            'drop_address' => $this->dropAddress?->full_address ?? $this->address,
            'pickup_latitude' => $this->pickupAddress?->latitude ?? $this->latitude,
            'pickup_longitude' => $this->pickupAddress?->longitude ?? $this->longitude,
            'drop_latitude' => $this->dropAddress?->latitude ?? $this->latitude,
            'drop_longitude' => $this->dropAddress?->longitude ?? $this->longitude,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'total_amount' => $this->total_amount ?? $this->final_price,
            'customer' => new UserResource($this->whenLoaded('user')),
            'partner' => new UserResource($this->whenLoaded('partner')),
            'worker' => new UserResource($this->whenLoaded('worker')),
            'pickup_driver' => new UserResource($this->whenLoaded('pickupDriver')),
            'delivery_driver' => new UserResource($this->whenLoaded('deliveryDriver')),
            'vehicle' => $this->whenLoaded('vehicle'),
            'service' => $this->whenLoaded('service'),
            'created_at_ist' => $this->created_at?->timezone('Asia/Kolkata')->format('Y-m-d h:i A'),
            'updated_at_ist' => $this->updated_at?->timezone('Asia/Kolkata')->format('Y-m-d h:i A'),
        ];
    }
}
