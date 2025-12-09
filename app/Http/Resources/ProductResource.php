<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sale_price' => (float) $this->sale_price,
            'internal_reference' => $this->internal_reference,
            'barcode' => $this->barcode,
            'product_type' => $this->product_type,
            'image' => $this->image ?? null,
            'category' => $this->when($this->menuCategory, [
                'id' => $this->menuCategory?->id,
                'name' => $this->menuCategory?->name,
            ]),
            'kitchen_station' => $this->when($this->kitchenStation, [
                'id' => $this->kitchenStation?->id,
                'name' => $this->kitchenStation?->name,
            ]),
        ];
    }
}
