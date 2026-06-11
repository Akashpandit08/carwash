<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class BookingDetailResource extends BookingResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'media' => BookingMediaResource::collection($this->whenLoaded('media')),
            'status_logs' => BookingStatusLogResource::collection($this->whenLoaded('statusLogs')),
            'payments' => $this->whenLoaded('payments'),
            'payouts' => PayoutResource::collection($this->whenLoaded('payouts')),
        ]);
    }
}
