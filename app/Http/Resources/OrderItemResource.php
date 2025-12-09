<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_template_id' => $this->product_template_id,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount' => (float) $this->discount,
            'tax_amount' => (float) $this->tax_amount,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'status' => $this->status,
            'special_instructions' => $this->special_instructions,
            'product' => $this->when($this->productTemplate, [
                'id' => $this->productTemplate?->id,
                'name' => $this->productTemplate?->name,
                'kitchen_station' => $this->when($this->productTemplate?->kitchenStation, [
                    'id' => $this->productTemplate?->kitchenStation?->id,
                    'name' => $this->productTemplate?->kitchenStation?->name,
                ]),
            ]),
            'sent_to_kitchen_at' => $this->sent_to_kitchen_at?->toISOString(),
            'ready_at' => $this->ready_at?->toISOString(),
            'served_at' => $this->served_at?->toISOString(),
        ];
    }
}
