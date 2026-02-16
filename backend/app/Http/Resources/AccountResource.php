<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'industry' => $this->industry,
            'website' => $this->website,
            'phone' => $this->phone,
            'email' => $this->email,
            'annual_revenue' => $this->annual_revenue,
            'employee_count' => $this->employee_count,
            'tax_id' => $this->tax_id,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'parent' => new AccountResource($this->whenLoaded('parent')),
            'children' => AccountResource::collection($this->whenLoaded('children')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
