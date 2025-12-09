<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KitchenTicketResource;
use App\Models\KitchenStation;
use App\Models\KitchenTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    /**
     * Get all pending kitchen tickets
     */
    public function tickets(Request $request): JsonResponse
    {
        $query = KitchenTicket::with([
            'items.orderItem.productTemplate',
            'order.table',
            'order.waiter',
            'station',
        ])->whereIn('status', [
            KitchenTicket::STATUS_PENDING,
            KitchenTicket::STATUS_PREPARING,
        ]);

        // Filter by station if provided
        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Order by priority and creation time
        $tickets = $query->orderByRaw("
            CASE priority 
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'normal' THEN 3
                WHEN 'low' THEN 4
                ELSE 5
            END
        ")->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => KitchenTicketResource::collection($tickets),
        ]);
    }

    /**
     * Get tickets for a specific station
     */
    public function stationTickets(KitchenStation $station): JsonResponse
    {
        $tickets = KitchenTicket::with([
            'items.orderItem.productTemplate',
            'order.table',
            'order.waiter',
        ])->where('station_id', $station->id)
            ->whereIn('status', [
                KitchenTicket::STATUS_PENDING,
                KitchenTicket::STATUS_PREPARING,
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => KitchenTicketResource::collection($tickets),
        ]);
    }

    /**
     * Get all kitchen stations
     */
    public function stations(): JsonResponse
    {
        $stations = KitchenStation::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stations->map(function ($station) {
                return [
                    'id' => $station->id,
                    'name' => $station->name,
                    'description' => $station->description,
                    'order' => $station->order,
                    'pending_tickets_count' => KitchenTicket::where('station_id', $station->id)
                        ->whereIn('status', [
                            KitchenTicket::STATUS_PENDING,
                            KitchenTicket::STATUS_PREPARING,
                        ])
                        ->count(),
                ];
            }),
        ]);
    }

    /**
     * Start preparing a ticket
     */
    public function startTicket(KitchenTicket $ticket): JsonResponse
    {
        if ($ticket->status !== KitchenTicket::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'El ticket ya fue iniciado',
            ], 400);
        }

        $ticket->update([
            'status' => KitchenTicket::STATUS_PREPARING,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket iniciado',
            'data' => new KitchenTicketResource($ticket->load([
                'items.orderItem.productTemplate',
                'order.table',
                'order.waiter',
                'station',
            ])),
        ]);
    }

    /**
     * Mark ticket as completed
     */
    public function completeTicket(KitchenTicket $ticket): JsonResponse
    {
        if ($ticket->status === KitchenTicket::STATUS_READY) {
            return response()->json([
                'success' => false,
                'message' => 'El ticket ya está completo',
            ], 400);
        }

        $ticket->update([
            'status' => KitchenTicket::STATUS_READY,
            'completed_at' => now(),
        ]);

        // Update all items in the ticket to ready
        $ticket->items()->update([
            'status' => 'ready',
        ]);

        // Check if all items in the order are ready
        $order = $ticket->order;
        $allItemsReady = $order->items()
            ->where('status', '!=', 'ready')
            ->count() === 0;

        if ($allItemsReady) {
            $order->update([
                'status' => \App\Models\Order::STATUS_READY,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket completado',
            'data' => new KitchenTicketResource($ticket->load([
                'items.orderItem.productTemplate',
                'order.table',
                'order.waiter',
                'station',
            ])),
        ]);
    }

    /**
     * Mark ticket as delivered (served)
     */
    public function deliverTicket(KitchenTicket $ticket): JsonResponse
    {
        if ($ticket->status !== KitchenTicket::STATUS_READY) {
            return response()->json([
                'success' => false,
                'message' => 'El ticket debe estar listo para ser entregado',
            ], 400);
        }

        $ticket->update([
            'status' => KitchenTicket::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket entregado',
            'data' => new KitchenTicketResource($ticket),
        ]);
    }
}
