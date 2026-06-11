<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'role' => $this->role,
            'gross_amount' => $this->gross_amount,
            'commission_amount' => $this->commission_amount,
            'net_amount' => $this->net_amount,
            'payout_status' => $this->payout_status,
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }
}
