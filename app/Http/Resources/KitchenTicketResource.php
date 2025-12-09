<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenTicketResource extends JsonResource
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
            'ticket_number' => $this->ticket_number,
            'status' => $this->status,
            'priority' => $this->priority,

            'order' => $this->when($this->order, [
                'id' => $this->order?->id,
                'order_number' => $this->order?->order_number,
                'table' => $this->when($this->order?->table, [
                    'id' => $this->order?->table?->id,
                    'number' => $this->order?->table?->number,
                ]),
                'waiter' => $this->when($this->order?->waiter, [
                    'id' => $this->order?->waiter?->id,
                    'name' => $this->order?->waiter?->name,
                ]),
                'guests_count' => $this->order?->guests_count,
            ]),

            'station' => $this->when($this->station, [
                'id' => $this->station?->id,
                'name' => $this->station?->name,
            ]),

            'items' => KitchenTicketItemResource::collection($this->whenLoaded('items')),

            'printed_at' => $this->printed_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),

            // Tiempo transcurrido en minutos
            'elapsed_minutes' => $this->created_at ?
                (int) $this->created_at->diffInMinutes(now()) : 0,
        ];
    }
}
