<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_type' => $this->order_type,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'guests_count' => $this->guests_count,
            
            // Amounts
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'service_charge' => (float) $this->service_charge,
            'delivery_fee' => (float) $this->delivery_fee,
            'tip_amount' => (float) $this->tip_amount,
            'total' => (float) $this->total,
            
            // Relationships
            'table' => $this->when($this->table, [
                'id' => $this->table?->id,
                'number' => $this->table?->number,
                'area' => $this->when($this->table?->area, [
                    'id' => $this->table?->area?->id,
                    'name' => $this->table?->area?->name,
                ]),
            ]),
            
            'waiter' => $this->when($this->waiter, [
                'id' => $this->waiter?->id,
                'name' => $this->waiter?->name,
            ]),
            
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            
            'payments' => $this->when($this->relationLoaded('payments'), function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => (float) $payment->amount,
                        'payment_method' => [
                            'id' => $payment->paymentMethod->id,
                            'name' => $payment->paymentMethod->name,
                            'code' => $payment->paymentMethod->code,
                        ],
                        'reference_number' => $payment->reference_number,
                        'payment_date' => $payment->payment_date?->toISOString(),
                        'status' => $payment->status,
                    ];
                });
            }),
            
            // Timestamps
            'order_date' => $this->order_date?->toISOString(),
            'scheduled_time' => $this->scheduled_time?->toISOString(),
            'served_time' => $this->served_time?->toISOString(),
            'completed_time' => $this->completed_time?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Notes
            'notes' => $this->notes,
        ];
    }
}
