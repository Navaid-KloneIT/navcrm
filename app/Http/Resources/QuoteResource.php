<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quote_number' => $this->quote_number,
            'status' => $this->status?->value,
            'valid_until' => $this->valid_until?->toDateString(),
            'subtotal' => $this->subtotal,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_amount' => $this->discount_amount,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'opportunity' => new OpportunityResource($this->whenLoaded('opportunity')),
            'account' => new AccountResource($this->whenLoaded('account')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'prepared_by' => new UserResource($this->whenLoaded('preparedBy')),
            'line_items' => QuoteLineItemResource::collection($this->whenLoaded('lineItems')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
