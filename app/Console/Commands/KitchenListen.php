<?php

namespace App\Console\Commands;

use App\Models\KitchenStation;
use App\Models\KitchenTicket;
use Illuminate\Console\Command;

class KitchenListen extends Command
{
    protected $signature = 'kitchen:listen {--station= : Filter by station ID} {--interval=3 : Polling interval in seconds}';

    protected $description = 'Listen for new kitchen tickets and print them in real-time (simulates printer output)';

    private array $printedTickets = [];

    public function handle(): int
    {
        $stationFilter = $this->option('station');
        $interval = (int) $this->option('interval');

        $this->info('🍳 KITCHEN PRINTER SIMULATOR');
        $this->info('════════════════════════════════════════');
        $this->info('Escuchando tickets nuevos... (Ctrl+C para salir)');
        $this->info("Intervalo de polling: {$interval}s");
        $this->newLine();

        // Show stations being monitored
        $stations = KitchenStation::where('is_active', true);
        if ($stationFilter) {
            $stations->where('id', $stationFilter);
        }

        foreach ($stations->get() as $station) {
            $this->info("📍 Monitoreando: {$station->name} (IP: {$station->printer_ip})");
        }
        $this->newLine();
        $this->line('─────────────────────────────────────────────────────────');
        $this->newLine();

        while (true) {
            $this->checkForNewTickets($stationFilter);
            sleep($interval);
        }

        return 0;
    }

    private function checkForNewTickets(?string $stationFilter): void
    {
        $query = KitchenTicket::with([
            'items.orderItem.productTemplate',
            'order.table',
            'order.waiter',
            'station',
        ])->whereIn('status', [KitchenTicket::STATUS_PENDING]);

        if ($stationFilter) {
            $query->where('station_id', $stationFilter);
        }

        $tickets = $query->orderBy('created_at', 'asc')->get();

        foreach ($tickets as $ticket) {
            if (in_array($ticket->id, $this->printedTickets)) {
                continue;
            }

            $this->printTicket($ticket);
            $this->printedTickets[] = $ticket->id;

            // Mark as printed
            $ticket->update(['printed_at' => now()]);
        }
    }

    private function printTicket(KitchenTicket $ticket): void
    {
        $station = $ticket->station;
        $order = $ticket->order;

        $this->alert("🖨️  NUEVO TICKET - {$station->name}");

        $this->line('╔════════════════════════════════════════════════════════╗');
        $this->line("║  TICKET: {$ticket->ticket_number}");
        $this->line('║  '.now()->format('d/m/Y H:i:s'));
        $this->line('╠════════════════════════════════════════════════════════╣');
        $this->line('║  Mesa: '.($order->table->number ?? 'PARA LLEVAR'));
        $this->line('║  Mozo: '.($order->waiter->name ?? 'N/A'));
        $this->line('╠════════════════════════════════════════════════════════╣');

        foreach ($ticket->items as $item) {
            $productName = $item->orderItem->productTemplate->name ?? 'Producto';
            $qty = $item->quantity;
            $instructions = $item->orderItem->special_instructions;

            $this->line("║  {$qty}x  {$productName}");
            if ($instructions) {
                $this->line("║       ⚠️  {$instructions}");
            }
        }

        $this->line('╚════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Play sound (beep)
        echo "\007"; // ASCII bell character
    }
}
