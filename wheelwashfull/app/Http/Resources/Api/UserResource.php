<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'role' => $this->role,
            'status' => $this->status,
            'service_city_id' => $this->service_city_id,
            'service_city_name' => $this->whenLoaded('serviceCity', fn () => $this->serviceCity?->name),
            'service_zone_id' => $this->service_zone_id,
            'service_zone_name' => $this->whenLoaded('serviceZone', fn () => $this->serviceZone?->name),
        ];
    }
}
