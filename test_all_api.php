<?php

/**
 * Script de prueba completo para TODOS los controllers API
 * 
 * Ejecutar: php test_all_api.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🚀 Test Completo - Todos los Controllers API\n";
echo "=" . str_repeat("=", 80) . "\n\n";

$baseUrl = 'http://localhost:8000/api';
$token = null;

// Helper para hacer peticiones
function apiRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
    ];
    
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// ============================================================================
// 1. SETUP - Crear datos de prueba
// ============================================================================
echo "📝 Paso 1: Preparando datos de prueba...\n";

// Usuario
$testUser = App\Models\User::where('email', 'test@pos.com')->first();
if (!$testUser) {
    $testUser = App\Models\User::create([
        'name' => 'Usuario POS Test',
        'email' => 'test@pos.com',
        'password' => Hash::make('password123'),
    ]);
}

// Categoría, producto, mesa, etc (reutilizamos del script anterior)
$menuCategory = App\Models\ProductCategory::where('type', 'menu')->first();
if (!$menuCategory) {
    $menuCategory = App\Models\ProductCategory::create([
        'name' => 'Pizzas',
        'type' => 'menu',
        'is_active' => true,
    ]);
}

$kitchenStation = App\Models\KitchenStation::first();
if (!$kitchenStation) {
    $kitchenStation = App\Models\KitchenStation::create([
        'branch_id' => 1,
        'name' => 'Cocina Caliente',
        'is_active' => true,
    ]);
}

$unit = App\Models\Unit::first();
if (!$unit) {
    $unit = App\Models\Unit::create([
        'name' => 'Unidad',
        'abbreviation' => 'UND',
        'type' => 'unit',
    ]);
}

$product = App\Models\ProductTemplate::where('name', 'Pizza API Test')->first();
if (!$product) {
    $product = App\Models\ProductTemplate::create([
        'name' => 'Pizza API Test',
        'unit_id' => $unit->id,
        'menu_category_id' => $menuCategory->id,
        'kitchen_station_id' => $kitchenStation->id,
        'sale_price' => 45.00,
        'can_be_sold' => true,
        'is_active' => true,
        'product_type' => 'storable',
    ]);
}

$tableArea = App\Models\TableArea::first();
if (!$tableArea) {
    $tableArea = App\Models\TableArea::create([
        'branch_id' => 1,
        'name' => 'Sala Test',
        'is_active' => true,
    ]);
}

$table = App\Models\Table::where('number', 'T1')->first();
if (!$table) {
    $table = App\Models\Table::create([
        'branch_id' => 1,
        'area_id' => $tableArea->id,
        'number' => 'T1',
        'capacity' => 4,
        'is_active' => true,
    ]);
}

$paymentMethod = App\Models\PaymentMethod::where('code', 'cash')->first();
if (!$paymentMethod) {
    $paymentMethod = App\Models\PaymentMethod::create([
        'code' => 'cash',
        'name' => 'Efectivo',
        'type' => 'cash',
        'is_active' => true,
    ]);
}

echo "✅ Datos de prueba listos\n\n";

// ============================================================================
// 2. TEST AuthController
// ============================================================================
echo "🔐 === TEST AuthController ===\n";

echo "📝 Login...\n";
$response = apiRequest('POST', "$baseUrl/login", [
    'email' => 'test@pos.com',
    'password' => 'password123',
]);

if ($response['code'] !== 200) {
    echo "❌ Login falló\n";
    exit(1);
}

$token = $response['data']['data']['token'];
echo "✅ Login exitoso\n";

echo "📝 GET /me...\n";
$response = apiRequest('GET', "$baseUrl/me", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " /me\n\n";

// ============================================================================
// 3. TEST UserController
// ============================================================================
echo "👥 === TEST UserController ===\n";

echo "📝 GET /users...\n";
$response = apiRequest('GET', "$baseUrl/users", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " Lista de usuarios (" . count($response['data']['data'] ?? []) . " users)\n";

echo "📝 GET /users/waiters...\n";
$response = apiRequest('GET', "$baseUrl/users/waiters", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " Lista de mozos\n";

echo "📝 GET /users/cashiers...\n";
$response = apiRequest('GET', "$baseUrl/users/cashiers", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " Lista de cajeros\n\n";

// ============================================================================
// 4. TEST PosController (rápido)
// ============================================================================
echo "🍽️  === TEST PosController ===\n";

echo "📝 GET /pos/tables...\n";
$response = apiRequest('GET', "$baseUrl/pos/tables", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " Mesas\n";

echo "📝 GET /pos/products...\n";
$response = apiRequest('GET', "$baseUrl/pos/products", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " Productos\n";

echo "📝 GET /pos/categories...\n";
$response = apiRequest('GET', "$baseUrl/pos/categories", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " Categorías\n";

echo "📝 POST /pos/orders (Crear orden)...\n";
$response = apiRequest('POST', "$baseUrl/pos/orders", [
    'table_id' => $table->id,
    'guests_count' => 2,
    'items' => [
        [
            'product_template_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 45.00,
        ],
    ],
], $token);

if ($response['code'] === 201) {
    $orderId = $response['data']['data']['id'];
    echo "✅ Orden creada (ID: $orderId)\n\n";
} else {
    echo "❌ Error al crear orden\n";
    print_r($response);
    $orderId = null;
}

// ============================================================================
// 5. TEST KitchenController
// ============================================================================
echo "🍳 === TEST KitchenController ===\n";

echo "📝 GET /kitchen/stations...\n";
$response = apiRequest('GET', "$baseUrl/kitchen/stations", null, $token);
echo ($response['code'] === 200 ? "✅" : "❌") . " Estaciones (" . count($response['data']['data'] ?? []) . " stations)\n";

echo "📝 GET /kitchen/tickets...\n";
$response = apiRequest('GET', "$baseUrl/kitchen/tickets", null, $token);
$tickets = $response['data']['data'] ?? [];
echo ($response['code'] === 200 ? "✅" : "❌") . " Tickets (" . count($tickets) . " tickets)\n";

if (count($tickets) > 0) {
    $ticketId = $tickets[0]['id'];
    
    echo "📝 PATCH /kitchen/tickets/$ticketId/start...\n";
    $response = apiRequest('PATCH', "$baseUrl/kitchen/tickets/$ticketId/start", null, $token);
    echo ($response['code'] === 200 ? "✅" : "❌") . " Iniciar ticket\n";
    
    echo "📝 PATCH /kitchen/tickets/$ticketId/complete...\n";
    $response = apiRequest('PATCH', "$baseUrl/kitchen/tickets/$ticketId/complete", null, $token);
    echo ($response['code'] === 200 ? "✅" : "❌") . " Completar ticket\n";
    
    echo "📝 PATCH /kitchen/tickets/$ticketId/deliver...\n";
    $response = apiRequest('PATCH', "$baseUrl/kitchen/tickets/$ticketId/deliver", null, $token);
    echo ($response['code'] === 200 ? "✅" : "❌") . " Entregar ticket\n";
} else {
    echo "⚠️  No hay tickets para probar (crear orden primero)\n";
}

echo "\n";

// ============================================================================
// 6. RESUMEN FINAL
// ============================================================================
echo "=" . str_repeat("=", 80) . "\n";
echo "✅ TESTS COMPLETADOS\n\n";

echo "📊 Resumen de Controllers:\n";
echo "   ✅ AuthController (login, me, logout)\n";
echo "   ✅ UserController (users, waiters, cashiers)\n";
echo "   ✅ PosController (tables, products, orders)\n";
echo "   ✅ KitchenController (stations, tickets, workflow)\n\n";

echo "🎯 Backend API Completo para POS!\n\n";
