<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
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
            'transfer_number' => $this->transfer_number,
            'from_warehouse_id' => $this->from_warehouse_id,
            'from_warehouse_name' => $this->fromWarehouse?->name,
            'to_warehouse_id' => $this->to_warehouse_id,
            'to_warehouse_name' => $this->toWarehouse?->name,
            'transfer_date' => $this->transfer_date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => $this->when($this->relationLoaded('productables'), function () {
                return $this->productables->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->template?->name ?? 'Unknown',
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
