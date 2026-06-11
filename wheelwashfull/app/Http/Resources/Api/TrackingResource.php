<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'role' => $this->role,
            'user' => new UserResource($this->whenLoaded('user')),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'heading' => $this->heading,
            'speed' => $this->speed,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
