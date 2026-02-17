<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'mobile' => $this->mobile,
            'job_title' => $this->job_title,
            'department' => $this->department,
            'description' => $this->description,
            'linkedin_url' => $this->linkedin_url,
            'twitter_handle' => $this->twitter_handle,
            'facebook_url' => $this->facebook_url,
            'address' => [
                'line_1' => $this->address_line_1,
                'line_2' => $this->address_line_2,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
            ],
            'source' => $this->source,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
