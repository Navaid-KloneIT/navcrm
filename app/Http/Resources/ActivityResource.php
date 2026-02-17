<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'subject' => $this->subject,
            'description' => $this->description,
            'activitable_type' => $this->activitable_type,
            'activitable_id' => $this->activitable_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
