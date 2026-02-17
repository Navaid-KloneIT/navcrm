<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteLineItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_percent' => $this->discount_percent,
            'subtotal' => $this->subtotal,
            'sort_order' => $this->sort_order,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
