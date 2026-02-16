<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'job_title' => $this->job_title,
            'website' => $this->website,
            'description' => $this->description,
            'status' => $this->status?->value,
            'score' => $this->score?->value,
            'source' => $this->source,
            'is_converted' => $this->is_converted,
            'converted_at' => $this->converted_at?->toIso8601String(),
            'converted_contact_id' => $this->converted_contact_id,
            'converted_account_id' => $this->converted_account_id,
            'address' => [
                'line_1' => $this->address_line_1,
                'line_2' => $this->address_line_2,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
            ],
            'owner' => new UserResource($this->whenLoaded('owner')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
