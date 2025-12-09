<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenTicketItemResource extends JsonResource
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
            'quantity' => $this->quantity,
            'status' => $this->status,
            'order_item' => $this->when($this->orderItem, [
                'id' => $this->orderItem?->id,
                'product' => [
                    'id' => $this->orderItem?->productTemplate?->id,
                    'name' => $this->orderItem?->productTemplate?->name,
                ],
                'special_instructions' => $this->orderItem?->special_instructions,
            ]),
        ];
    }
}
