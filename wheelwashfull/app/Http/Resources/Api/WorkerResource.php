<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'partner_id' => $this->partner_id,
            'skills' => $this->skills,
            'service_area' => $this->service_area,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'current_status' => $this->current_status,
            'rating' => $this->rating,
            'total_jobs' => $this->total_jobs,
        ];
    }
}
