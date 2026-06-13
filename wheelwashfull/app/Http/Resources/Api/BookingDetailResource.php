<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class BookingDetailResource extends BookingResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'price' => $this->price,
            'discount' => $this->discount,
            'final_price' => $this->final_price,
            'total_amount' => $this->total_amount,
            'payable_amount' => $this->payable_amount ?? $this->total_amount,
            'is_subscription_booking' => ($this->booking_source === 'subscription' || $this->payment_method === 'subscription'),
            'coupon' => $this->whenLoaded('coupon'),
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'media' => BookingMediaResource::collection($this->whenLoaded('media')),
            'status_logs' => BookingStatusLogResource::collection($this->whenLoaded('statusLogs')),
            'payments' => $this->whenLoaded('payments'),
            'payouts' => PayoutResource::collection($this->whenLoaded('payouts')),
        ]);
    }
}
