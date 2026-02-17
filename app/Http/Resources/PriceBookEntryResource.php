<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceBookEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price_book_id' => $this->price_book_id,
            'product_id' => $this->product_id,
            'unit_price' => $this->unit_price,
            'min_quantity' => $this->min_quantity,
            'is_active' => $this->is_active,
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
