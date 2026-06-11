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
            'wash_type' => $this->wash_type,
            'service_mode' => $this->service_mode,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'booking_date' => optional($this->booking_date)->toDateString(),
            'slot_time' => $this->slot_time,
            'address' => $this->address,
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
        ];
    }
}
