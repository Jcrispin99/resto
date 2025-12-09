<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get current active order
        $currentOrder = Order::where('table_id', $this->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->latest()
            ->first();

        return [
            'id' => $this->id,
            'number' => $this->number,
            'capacity' => $this->capacity,
            'is_active' => (bool) $this->is_active,
            'area' => [
                'id' => $this->area->id,
                'name' => $this->area->name,
            ],
            'status' => $this->getTableStatus($currentOrder),
            'current_order' => $currentOrder ? [
                'id' => $currentOrder->id,
                'order_number' => $currentOrder->order_number,
                'guests_count' => $currentOrder->guests_count,
                'status' => $currentOrder->status,
                'total' => (float) $currentOrder->total,
            ] : null,
        ];
    }

    private function getTableStatus($currentOrder): string
    {
        if (!$currentOrder) {
            return 'available';
        }

        return match ($currentOrder->status) {
            Order::STATUS_PENDING, Order::STATUS_CONFIRMED => 'occupied',
            Order::STATUS_PREPARING => 'preparing',
            Order::STATUS_READY => 'ready',
            Order::STATUS_SERVED => 'serving',
            default => 'available'
        };
    }
}
