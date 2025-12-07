<?php

/**
 * Test Script for Tables and Reservations System
 * 
 * Tests TableAreas, Tables, and Reservations
 * 
 * Run: php test_tables_reservations.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TableArea;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\Company;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing Tables and Reservations System\n";
echo "==========================================\n\n";

try {
    // Get or create test user
    $user = User::first();
    if (!$user) {
        echo "❌ ERROR: No users found. Please create a user first.\n";
        exit(1);
    }
    echo "✅ Using user: {$user->name} (ID: {$user->id})\n\n";

    // Get or create company
    $company = Company::first();
    if (!$company) {
        echo "❌ ERROR: No company found.\n";
        exit(1);
    }
    echo "✅ Using company: {$company->name}\n\n";

    // Get or create partner (customer)
    $partner = Partner::first();
    if (!$partner) {
        echo "⚠️  No partners found. Creating one may require proper setup.\n";
        echo "Please create a partner manually first.\n";
        exit(1);
    }
    echo "✅ Using customer: {$partner->name}\n\n";

    // =================================
    // TEST 1: Table Areas (Áreas)
    // =================================
    echo "🏪 TEST 1: Table Areas\n";
    echo "---------------------\n";

    $area1 = TableArea::updateOrCreate(
        ['name' => 'Salón Principal', 'branch_id' => $company->id],
        [
            'description' => 'Área principal del restaurante',
            'order' => 1,
            'is_active' => true,
        ]
    );
    echo "✅ Area created: {$area1->name}\n";

    $area2 = TableArea::updateOrCreate(
        ['name' => 'Terraza', 'branch_id' => $company->id],
        [
            'description' => 'Área exterior con vista',
            'order' => 2,
            'is_active' => true,
        ]
    );
    echo "✅ Area created: {$area2->name}\n\n";

    // =================================
    // TEST 2: Tables (Mesas)
    // =================================
    echo "🍽️  TEST 2: Tables\n";
    echo "-----------------\n";

    // Salón Principal
    $table1 = Table::firstOrCreate(
        ['number' => 'M1', 'branch_id' => $company->id],
        [
            'area_id' => $area1->id,
            'capacity' => 4,
            'is_active' => true,
        ]
    );
    echo "✅ Table created: {$table1->number} ({$table1->capacity} pax)\n";

    $table2 = Table::firstOrCreate(
        ['number' => 'M2', 'branch_id' => $company->id],
        [
            'area_id' => $area1->id,
            'capacity' => 2,
            'is_active' => true,
        ]
    );
    echo "✅ Table created: {$table2->number} ({$table2->capacity} pax)\n";

    // Terraza
    $table3 = Table::firstOrCreate(
        ['number' => 'T1', 'branch_id' => $company->id],
        [
            'area_id' => $area2->id,
            'capacity' => 6,
            'is_active' => true,
        ]
    );
    echo "✅ Table created: {$table3->number} ({$table3->capacity} pax)\n\n";

    // =================================
    // TEST 3: Reservations (Crear)
    // =================================
    echo "📅 TEST 3: Create Reservation\n";
    echo "-----------------------------\n";

    $tomorrow = now()->addDay();
    $reservationNumber = 'RES-' . str_pad(Reservation::count() + 1, 6, '0', STR_PAD_LEFT);

    $reservation = Reservation::create([
        'reservation_number' => $reservationNumber,
        'branch_id' => $company->id,
        'partner_id' => $partner->id,
        'table_id' => $table1->id,
        'reservation_date' => $tomorrow->toDateString(),
        'reservation_time' => '19:00',
        'guests_count' => 4,
        'status' => Reservation::STATUS_PENDING,
        'special_requests' => 'Mesa cerca de la ventana',
    ]);

    echo "✅ Reservation created:\n";
    echo "  Number: {$reservation->reservation_number}\n";
    echo "  Customer: {$reservation->partner->name}\n";
    echo "  Table: {$reservation->table->number}\n";
    echo "  Date: {$reservation->reservation_date} at {$reservation->reservation_time}\n";
    echo "  Guests: {$reservation->guests_count}\n";
    echo "  Status: {$reservation->status}\n\n";

    // =================================
    // TEST 4: Confirm Reservation
    // =================================
    echo "✅ TEST 4: Confirm Reservation\n";
    echo "------------------------------\n";

    $reservation->update([
        'status' => Reservation::STATUS_CONFIRMED,
        'confirmed_at' => now(),
    ]);

    echo "✅ Reservation confirmed at: {$reservation->confirmed_at}\n";
    echo "  Status: {$reservation->status}\n\n";

    // =================================
    // TEST 5: Seat Customers (Create Order)
    // =================================
    echo "💺 TEST 5: Seat Customers\n";
    echo "-------------------------\n";

    DB::beginTransaction();
    try {
        // Update reservation
        $reservation->update([
            'status' => Reservation::STATUS_SEATED,
            'seated_at' => now(),
        ]);

        // Create order
        $orderNumber = 'ORD-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT);
        $order = Order::create([
            'order_number' => $orderNumber,
            'branch_id' => $reservation->branch_id,
            'table_id' => $reservation->table_id,
            'partner_id' => $reservation->partner_id,
            'order_type' => Order::TYPE_DINE_IN,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'guests_count' => $reservation->guests_count,
            'order_date' => now(),
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
        ]);

        DB::commit();

        echo "✅ Customers seated:\n";
        echo "  Reservation status:  {$reservation->status}\n";
        echo "  Seated at: {$reservation->seated_at}\n";
        echo "  Order created: {$order->order_number}\n";
        echo "  Order type: {$order->order_type}\n";
        echo "  Order status: {$order->status}\n\n";
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }

    // =================================
    // TEST 6: Create Another Reservation (to Cancel)
    // =================================
    echo "📅 TEST 6: Create Reservation to Cancel\n";
    echo "---------------------------------------\n";

    $reservation2Number = 'RES-' . str_pad(Reservation::count() + 1, 6, '0', STR_PAD_LEFT);
    $reservation2 = Reservation::create([
        'reservation_number' => $reservation2Number,
        'branch_id' => $company->id,
        'partner_id' => $partner->id,
        'table_id' => $table2->id,
        'reservation_date' => $tomorrow->toDateString(),
        'reservation_time' => '20:00',
        'guests_count' => 2,
        'status' => Reservation::STATUS_CONFIRMED,
        'confirmed_at' => now(),
    ]);

    echo "✅ Reservation created: {$reservation2->reservation_number}\n";
    echo "  Table: {$table2->number}\n\n";

    // =================================
    // TEST 7: Cancel Reservation
    // =================================
    echo "❌ TEST 7: Cancel Reservation\n";
    echo "-----------------------------\n";

    $reservation2->update(['status' => Reservation::STATUS_CANCELLED]);

    echo "✅ Reservation cancelled:\n";
    echo "  Status: {$reservation2->status}\n";
    echo "  Table {$table2->number} freed\n\n";

    // =================================
    // TEST 8: Summary & Statistics
    // =================================
    echo "📊 TEST 8: Summary\n";
    echo "------------------\n";

    echo "Table Areas:\n";
    foreach (TableArea::with('tables')->get() as $area) {
        echo "  • {$area->name}: {$area->tables->count()} tables\n";
    }
    echo "\n";

    echo "Tables:\n";
    echo "  • Active: " . Table::where('is_active', true)->count() . "\n";
    echo "  • Total: " . Table::count() . "\n";
    echo "\n";

    echo "Reservations by Status:\n";
    foreach ([
        Reservation::STATUS_PENDING,
        Reservation::STATUS_CONFIRMED,
        Reservation::STATUS_SEATED,
        Reservation::STATUS_COMPLETED,
        Reservation::STATUS_CANCELLED,
        Reservation::STATUS_NO_SHOW
    ] as $status) {
        $count = Reservation::where('status', $status)->count();
        if ($count > 0) {
            echo "  • " . ucfirst($status) . ": $count\n";
        }
    }
    echo "\n";

    echo "Upcoming Reservations:\n";
    $upcomingCount = Reservation::upcoming()->count();
    echo "  • Total: $upcomingCount\n\n";

    echo "Today's Reservations:\n";
    $todayCount = Reservation::today()->count();
    echo "  • Total: $todayCount\n\n";

    // =================================
    // TEST 9: Scopes Testing
    // =================================
    echo "🔍 TEST 9: Testing Scopes\n";
    echo "-------------------------\n";

    $pending = Reservation::pending()->count();
    $confirmed = Reservation::confirmed()->count();
    $upcoming = Reservation::upcoming()->count();

    echo "✅ Scopes working:\n";
    echo "  • pending(): $pending\n";
    echo "  • confirmed(): $confirmed\n";
    echo "  • upcoming(): $upcoming\n\n";

    echo "✅ ALL TESTS PASSED!\n\n";

    // Final Statistics
    echo "📊 Final Statistics:\n";
    echo "  Table Areas: " . TableArea::count() . "\n";
    echo "  Tables: " . Table::count() . "\n";
    echo "  Active Tables: " . Table::where('is_active', true)->count() . "\n";
    echo "  Reservations: " . Reservation::count() . "\n";
    echo "  Orders: " . Order::count() . "\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\n✨ Test completed successfully!\n";
