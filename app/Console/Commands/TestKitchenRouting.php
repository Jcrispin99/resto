<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Branch;
use App\Models\KitchenStation;
use App\Models\ProductTemplate;
use App\Models\ProductProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Unit;
use App\Models\ProductCategory;

class TestKitchenRouting extends Command
{
    protected $signature = 'test:kitchen-routing';
    protected $description = 'Verify kitchen station routing logic';

    public function handle()
    {
        $this->info('Starting Kitchen Routing Test...');

        // 1. Setup Data
        $company = Company::first() ?? Company::factory()->create(['name' => 'Test Company']);
        $branch = Branch::first() ?? Branch::create(['company_id' => $company->id, 'name' => 'Test Branch', 'code' => 'TB01']);
        
        $unit = Unit::firstOrCreate(
            ['name' => 'Unit'], 
            ['abbreviation' => 'u']
        );
        $category = ProductCategory::firstOrCreate(['name' => 'General']);

        // 2. Create Stations
        $this->info('Creating Stations...');
        $bar = KitchenStation::create([
            'branch_id' => $branch->id,
            'name' => 'Bar Station',
            'printer_ip' => '192.168.1.100'
        ]);
        
        $kitchen = KitchenStation::create([
            'branch_id' => $branch->id,
            'name' => 'Kitchen Station',
            'printer_ip' => '192.168.1.101'
        ]);

        // 3. Create Products linked to Stations
        $this->info('Creating Products...');
        $beer = ProductTemplate::create([
            'name' => 'Cerveza Test',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'kitchen_station_id' => $bar->id,
            'sale_price' => 10.00
        ]);
        
        $burger = ProductTemplate::create([
            'name' => 'Hamburguesa Test',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'kitchen_station_id' => $kitchen->id,
            'sale_price' => 25.00
        ]);

        // 4. Create Order
        $this->info('Creating Order...');
        $order = Order::create([
            'branch_id' => $branch->id,
            'order_number' => 'ORD-' . time(),
            'status' => 'pending',
            'total' => 35.00,
            'subtotal' => 35.00,
            'tax' => 0
        ]);

        // Add items
        OrderItem::create([
            'order_id' => $order->id,
            'product_template_id' => $beer->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'subtotal' => 20.00,
            'total' => 20.00
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_template_id' => $burger->id,
            'quantity' => 1,
            'unit_price' => 15.00,
            'subtotal' => 15.00,
            'total' => 15.00
        ]);

        // 5. Run Routing Logic
        $this->info('Generating Kitchen Tickets...');
        $order->refresh(); // Ensure items are loaded
        
        $this->info("Order Items Count: " . $order->items->count());
        foreach($order->items as $item) {
            $this->info("Item: " . $item->productTemplate->name . " - Station: " . ($item->productTemplate->kitchen_station_id ?? 'NULL'));
        }

        $order->generateKitchenTickets();

        // 6. Verify Results
        $tickets = $order->kitchenTickets()->with('items')->get();
        
        $this->info('Verifying Results:');
        $this->info("Total Tickets Created: " . $tickets->count());

        foreach ($tickets as $ticket) {
            $stationName = $ticket->station->name;
            $itemCount = $ticket->items->count();
            $this->info("- Ticket {$ticket->ticket_number} for [{$stationName}]: {$itemCount} items");
            
            foreach ($ticket->items as $item) {
                $productName = $item->orderItem->productTemplate->name;
                $this->line("  * {$item->quantity}x {$productName}");
            }
        }

        if ($tickets->count() === 2) {
            $this->info('SUCCESS: Routing worked correctly!');
        } else {
            $this->error('FAILURE: Expected 2 tickets.');
        }
    }
}
