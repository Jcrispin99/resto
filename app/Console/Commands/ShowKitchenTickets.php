<?php

namespace App\Console\Commands;

use App\Models\KitchenStation;
use App\Models\KitchenTicket;
use Illuminate\Console\Command;

class ShowKitchenTickets extends Command
{
    protected $signature = 'kitchen:tickets {--station= : Filter by station ID}';
    protected $description = 'Show pending kitchen tickets by station (simulates what each printer would receive)';

    public function handle(): int
    {
        $stations = KitchenStation::where('is_active', true)->orderBy('order')->get();

        if ($stations->isEmpty()) {
            $this->error('No hay estaciones de cocina configuradas.');
            return 1;
        }

        $stationFilter = $this->option('station');

        foreach ($stations as $station) {
            if ($stationFilter && $station->id != $stationFilter) {
                continue;
            }

            $this->newLine();
            $this->info("╔══════════════════════════════════════════════════════════════╗");
            $this->info("║  🍳 ESTACIÓN: {$station->name}");
            $this->info("║  🖨️  IMPRESORA: " . ($station->printer_ip ?: 'Sin asignar'));
            $this->info("╚══════════════════════════════════════════════════════════════╝");

            $tickets = KitchenTicket::with([
                'items.orderItem.productTemplate',
                'order.table',
                'order.waiter',
            ])
                ->where('station_id', $station->id)
                ->whereIn('status', [KitchenTicket::STATUS_PENDING, KitchenTicket::STATUS_PREPARING])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($tickets->isEmpty()) {
                $this->line("  📭 No hay tickets pendientes");
                continue;
            }

            foreach ($tickets as $ticket) {
                $this->newLine();
                $this->line("  ┌─────────────────────────────────────────────────────────┐");
                $this->line("  │ TICKET: {$ticket->ticket_number}");
                $this->line("  │ Mesa: " . ($ticket->order->table->number ?? 'Para llevar'));
                $this->line("  │ Mozo: " . ($ticket->order->waiter->name ?? 'N/A'));
                $this->line("  │ Estado: " . strtoupper($ticket->status));
                $this->line("  │ Hora: " . $ticket->created_at->format('H:i:s'));
                $this->line("  ├─────────────────────────────────────────────────────────┤");

                foreach ($ticket->items as $item) {
                    $productName = $item->orderItem->productTemplate->name ?? 'Producto';
                    $qty = $item->quantity;
                    $instructions = $item->orderItem->special_instructions;

                    $this->line("  │  {$qty}x {$productName}");
                    if ($instructions) {
                        $this->line("  │     ⚠️  {$instructions}");
                    }
                }

                $this->line("  └─────────────────────────────────────────────────────────┘");
            }
        }

        $this->newLine();
        $this->info("Total tickets pendientes: " . KitchenTicket::whereIn('status', ['pending', 'preparing'])->count());

        return 0;
    }
}
