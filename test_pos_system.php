<?php

/**
 * Test Script for POS Controllers
 * 
 * Tests PaymentMethods, PosTerminals, and CashRegisters
 * 
 * Run: php test_pos_system.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PaymentMethod;
use App\Models\PosTerminal;
use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing POS System Controllers\n";
echo "==================================\n\n";

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
        echo "⚠️  No company found, creating one...\n";
        $company = Company::create([
            'name' => 'Restaurante Test',
            'identity_document_type_id' => 6,
            'number' => '20123456789',
            'business_name' => 'Restaurante Test SAC',
        ]);
    }
    echo "✅ Using company: {$company->name}\n\n";

    // =================================
    // TEST 1: Payment Methods
    // =================================
    echo "📊 TEST 1: Payment Methods\n";
    echo "-------------------------\n";

    // Run seeder
    echo "Running PaymentMethodSeeder...\n";
    Artisan::call('db:seed', ['--class' => 'PaymentMethodSeeder']);
    
    $paymentMethods = PaymentMethod::all();
    echo "✅ Payment Methods created: " . $paymentMethods->count() . "\n";
    
    foreach ($paymentMethods as $method) {
        $refRequired = $method->requires_reference ? '(requires ref)' : '';
        echo "  - {$method->code}: {$method->name} [{$method->type}] {$refRequired}\n";
    }
    echo "\n";

    // =================================
    // TEST 2: POS Terminals
    // =================================
    echo "📱 TEST 2: POS Terminals\n";
    echo "-------------------------\n";

    // Create test terminals
    $terminal1 = PosTerminal::updateOrCreate(
        ['code' => 'POS-001'],
        [
            'branch_id' => $company->id,
            'name' => 'Caja Principal',
            'ip_address' => '192.168.1.100',
            'printer_ip' => '192.168.1.200',
            'is_active' => true,
        ]
    );
    echo "✅ Terminal created: {$terminal1->code} - {$terminal1->name}\n";
    echo "  Branch: {$terminal1->branch->name}\n";
    echo "  IP: {$terminal1->ip_address}\n";
    echo "  Printer: {$terminal1->printer_ip}\n\n";

    $terminal2 = PosTerminal::updateOrCreate(
        ['code' => 'POS-002'],
        [
            'branch_id' => $company->id,
            'name' => 'Mesa Rápida 1',
            'ip_address' => '192.168.1.101',
            'is_active' => true,
        ]
    );
    echo "✅ Terminal created: {$terminal2->code} - {$terminal2->name}\n\n";

    // =================================
    // TEST 3: Cash Register - Opening
    // =================================
    echo "💵 TEST 3: Cash Register Operations\n";
    echo "-----------------------------------\n";

    // Close any existing open registers for this terminal
    $existingOpen = CashRegister::where('terminal_id', $terminal1->id)
        ->where('status', CashRegister::STATUS_OPEN)
        ->first();
    
    if ($existingOpen) {
        echo "⚠️  Closing existing open register...\n";
        $existingOpen->update([
            'closing_balance' => 0,
            'expected_balance' => 0,
            'difference' => 0,
            'closed_by' => $user->id,
            'closed_at' => now(),
            'status' => CashRegister::STATUS_CLOSED,
        ]);
    }

    // Open new cash register
    DB::beginTransaction();
    try {
        $cashRegister = CashRegister::create([
            'terminal_id' => $terminal1->id,
            'opening_balance' => 200.00,
            'opened_by' => $user->id,
            'opened_at' => now(),
            'status' => CashRegister::STATUS_OPEN,
            'notes' => 'Test opening - Turno mañana',
        ]);

        // Register opening movement
        $cashRegister->movements()->create([
            'type' => CashMovement::TYPE_OPENING,
            'concept' => 'Apertura de caja',
            'amount' => 200.00,
            'payment_method' => 'cash',
            'user_id' => $user->id,
        ]);

        DB::commit();
        echo "✅ Cash Register opened:\n";
        echo "  ID: {$cashRegister->id}\n";
        echo "  Terminal: {$cashRegister->terminal->name}\n";
        echo "  Opening Balance: S/ {$cashRegister->opening_balance}\n";
        echo "  Opened by: {$cashRegister->openedBy->name}\n";
        echo "  Status: {$cashRegister->status}\n\n";
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }

    // =================================
    // TEST 4: Cash Movements
    // =================================
    echo "💰 TEST 4: Cash Movements\n";
    echo "-------------------------\n";

    // Income - Sale
    $movement1 = $cashRegister->movements()->create([
        'type' => CashMovement::TYPE_INCOME,
        'concept' => 'Venta - Mesa 5',
        'amount' => 85.50,
        'payment_method' => 'cash',
        'user_id' => $user->id,
    ]);
    echo "✅ Income added: S/ {$movement1->amount} - {$movement1->concept}\n";

    // Income - Sale with Yape
    $yape = PaymentMethod::where('code', 'yape')->first();
    $movement2 = $cashRegister->movements()->create([
        'type' => CashMovement::TYPE_INCOME,
        'concept' => 'Venta - Mesa 3',
        'amount' => 120.00,
        'payment_method' => 'yape',
        'reference' => '123456789',
        'user_id' => $user->id,
    ]);
    echo "✅ Income added: S/ {$movement2->amount} - {$movement2->concept} (Yape)\n";

    // Expense - Purchase change
    $movement3 = $cashRegister->movements()->create([
        'type' => CashMovement::TYPE_EXPENSE,
        'concept' => 'Compra de sencillo',
        'amount' => -50.00,
        'payment_method' => 'cash',
        'user_id' => $user->id,
    ]);
    echo "✅ Expense added: S/ " . abs($movement3->amount) . " - {$movement3->concept}\n";

    // Deposit to bank
    $movement4 = $cashRegister->movements()->create([
        'type' => CashMovement::TYPE_DEPOSIT,
        'concept' => 'Depósito BCP',
        'amount' => -100.00,
        'payment_method' => 'transfer',
        'reference' => 'OP-98765',
        'user_id' => $user->id,
    ]);
    echo "✅ Deposit added: S/ " . abs($movement4->amount) . " - {$movement4->concept}\n\n";

    // =================================
    // TEST 5: Close Cash Register
    // =================================
    echo "🔒 TEST 5: Closing Cash Register\n";
    echo "--------------------------------\n";

    // Calculate totals
    $totalMovements = $cashRegister->movements()
        ->whereIn('type', [
            CashMovement::TYPE_OPENING,
            CashMovement::TYPE_INCOME,
            CashMovement::TYPE_EXPENSE,
            CashMovement::TYPE_DEPOSIT,
            CashMovement::TYPE_WITHDRAWAL,
        ])
        ->sum('amount');

    echo "Expected Balance Calculation:\n";
    echo "  Opening: S/ 200.00\n";
    echo "  Income 1: S/ +85.50\n";
    echo "  Income 2: S/ +120.00\n";
    echo "  Expense: S/ -50.00\n";
    echo "  Deposit: S/ -100.00\n";
    echo "  -------------------\n";
    echo "  Expected: S/ " . number_format($totalMovements, 2) . "\n\n";

    // Close with exact balance
    $closingBalance = $totalMovements; // Exact balance
    // $closingBalance = $totalMovements + 10; // Simulate surplus
    // $closingBalance = $totalMovements - 20; // Simulate shortage

    DB::beginTransaction();
    try {
        $difference = $closingBalance - $totalMovements;

        $cashRegister->update([
            'closing_balance' => $closingBalance,
            'expected_balance' => $totalMovements,
            'difference' => $difference,
            'closed_by' => $user->id,
            'closed_at' => now(),
            'status' => CashRegister::STATUS_CLOSED,
        ]);

        // Register closing movement
        $cashRegister->movements()->create([
            'type' => CashMovement::TYPE_CLOSING,
            'concept' => 'Cierre de caja',
            'amount' => $closingBalance,
            'payment_method' => 'cash',
            'user_id' => $user->id,
            'notes' => $difference != 0 
                ? "Diferencia: S/ " . number_format(abs($difference), 2) . " (" . ($difference > 0 ? 'Sobrante' : 'Faltante') . ")"
                : "Sin diferencia",
        ]);

        DB::commit();

        echo "✅ Cash Register closed:\n";
        echo "  Closing Balance: S/ " . number_format($closingBalance, 2) . "\n";
        echo "  Expected Balance: S/ " . number_format($totalMovements, 2) . "\n";
        echo "  Difference: S/ " . number_format($difference, 2);
        
        if ($difference > 0) {
            echo " (SOBRANTE) ✨\n";
        } elseif ($difference < 0) {
            echo " (FALTANTE) ⚠️\n";
        } else {
            echo " (CUADRADO) ✅\n";
        }

        echo "  Closed by: {$cashRegister->closedBy->name}\n\n";
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }

    // =================================
    // TEST 6: Summary
    // =================================
    echo "📋 TEST 6: Summary\n";
    echo "------------------\n";

    $allMovements = $cashRegister->movements()->orderBy('created_at')->get();
    
    echo "Cash Register #{$cashRegister->id} - {$cashRegister->terminal->name}\n";
    echo "All Movements:\n";
    
    $runningTotal = 0;
    foreach ($allMovements as $mov) {
        $runningTotal += $mov->amount;
        $sign = $mov->amount >= 0 ? '+' : '';
        echo sprintf(
            "  %s | %s | %s%s | Balance: S/ %s\n",
            $mov->created_at->format('H:i:s'),
            str_pad($mov->type, 10),
            $sign,
            number_format($mov->amount, 2),
            number_format($runningTotal, 2)
        );
    }

    echo "\n✅ ALL TESTS PASSED!\n\n";

    // Statistics
    echo "📊 Statistics:\n";
    echo "  Payment Methods: " . PaymentMethod::count() . "\n";
    echo "  POS Terminals: " . PosTerminal::count() . "\n";
    echo "  Cash Registers: " . CashRegister::count() . "\n";
    echo "  Cash Movements: " . CashMovement::count() . "\n";
    echo "  Open Registers: " . CashRegister::open()->count() . "\n";
    echo "  Closed Registers: " . CashRegister::closed()->count() . "\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\n✨ Test completed successfully!\n";
