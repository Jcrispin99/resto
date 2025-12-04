<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse?->name,
            'partner_id' => $this->partner_id,
            'partner_name' => $this->partner?->name,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'expected_delivery_date' => $this->expected_delivery_date?->format('Y-m-d'),
            'received_date' => $this->received_date?->format('Y-m-d'),
            'paid_date' => $this->paid_date?->format('Y-m-d'),
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'notes' => $this->notes,
            'items' => $this->when($this->relationLoaded('productables'), function () {
                return $this->productables->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name ?? 'Unknown',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'discount' => $item->discount ?? 0,
                        'tax_id' => $item->tax_id,
                        'tax_amount' => $item->tax_amount ?? 0,
                        'total' => $item->total,
                        'notes' => $item->notes,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
