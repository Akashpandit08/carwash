<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'business_name' => $this->business_name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'service_area' => $this->service_area,
            'current_status' => $this->current_status,
            'commission_type' => $this->commission_type,
            'commission_value' => $this->commission_value,
        ];
    }
}
