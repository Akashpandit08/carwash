<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'type' => $this->type,
            'side' => $this->side,
            'file_path' => $this->file_path,
            'uploaded_by' => new UserResource($this->whenLoaded('uploadedBy')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
