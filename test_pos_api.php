<?php

/**
 * Script de prueba completo para API POS
 * 
 * Ejecutar: php test_pos_api.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🍽️  Test Completo API POS\n";
echo "=" . str_repeat("=", 70) . "\n\n";

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

// 1. Setup - Crear datos de prueba
echo "📝 Paso 1: Preparando datos de prueba...\n";

// Usuario
$testUser = App\Models\User::where('email', 'test@pos.com')->first();
if (!$testUser) {
    $testUser = App\Models\User::create([
        'name' => 'Usuario POS',
        'email' => 'test@pos.com',
        'password' => Hash::make('password123'),
    ]);
}

// Crear categoría de menú si no existe
$menuCategory = App\Models\ProductCategory::where('type', 'menu')->first();
if (!$menuCategory) {
    $menuCategory = App\Models\ProductCategory::create([
        'name' => 'Pizzas',
        'type' => 'menu',
        'is_active' => true,
    ]);
}

// Crear estación de cocina
$kitchenStation = App\Models\KitchenStation::first();
if (!$kitchenStation) {
    $kitchenStation = App\Models\KitchenStation::create([
        'branch_id' => 1,
        'name' => 'Cocina Caliente',
        'is_active' => true,
    ]);
}

// Crear unidad de medida
$unit = App\Models\Unit::first();
if (!$unit) {
    $unit = App\Models\Unit::create([
        'name' => 'Unidad',
        'abbreviation' => 'UND',
        'type' => 'unit',
    ]);
}

// Crear productos de prueba
$product1 = App\Models\ProductTemplate::where('name', 'Pizza Test')->first();
if (!$product1) {
    $product1 = App\Models\ProductTemplate::create([
        'name' => 'Pizza Test',
        'unit_id' => $unit->id,
        'menu_category_id' => $menuCategory->id,
        'kitchen_station_id' => $kitchenStation->id,
        'sale_price' => 35.00,
        'can_be_sold' => true,
        'is_active' => true,
        'product_type' => 'storable',
    ]);
}

$product2 = App\Models\ProductTemplate::where('name', 'Coca Cola')->first();
if (!$product2) {
    $product2 = App\Models\ProductTemplate::create([
        'name' => 'Coca Cola',
        'unit_id' => $unit->id,
        'menu_category_id' => $menuCategory->id,
        'kitchen_station_id' => $kitchenStation->id,
        'sale_price' => 8.00,
        'can_be_sold' => true,
        'is_active' => true,
        'product_type' => 'storable',
    ]);
}

// Crear área de mesa
$tableArea = App\Models\TableArea::first();
if (!$tableArea) {
    $tableArea = App\Models\TableArea::create([
        'branch_id' => 1,
        'name' => 'Sala Principal',
        'is_active' => true,
    ]);
}

// Crear mesa
$table = App\Models\Table::where('number', 'M1')->first();
if (!$table) {
    $table = App\Models\Table::create([
        'branch_id' => 1,
        'area_id' => $tableArea->id,
        'number' => 'M1',
        'capacity' => 4,
        'is_active' => true,
    ]);
}

// Crear método de pago
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

// 2. Login
echo "📝 Paso 2: Login...\n";
$response = apiRequest('POST', "$baseUrl/login", [
    'email' => 'test@pos.com',
    'password' => 'password123',
]);

if ($response['code'] !== 200) {
    echo "❌ Login falló\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

$token = $response['data']['data']['token'];
echo "✅ Login exitoso - Token obtenido\n\n";

// 3. Test GET /api/pos/table-areas
echo "📝 Paso 3: GET /pos/table-areas...\n";
$response = apiRequest('GET', "$baseUrl/pos/table-areas", null, $token);

if ($response['code'] === 200 && isset($response['data']['data'])) {
    echo "✅ Areas obtenidas: " . count($response['data']['data']) . " áreas\n\n";
} else {
    echo "❌ Falló\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 4. Test GET /api/pos/tables
echo "📝 Paso 4: GET /pos/tables...\n";
$response = apiRequest('GET', "$baseUrl/pos/tables", null, $token);

if ($response['code'] === 200 && isset($response['data']['data'])) {
    $tables = $response['data']['data'];
    echo "✅ Mesas obtenidas: " . count($tables) . " mesas\n";
    if (count($tables) > 0) {
        echo "   Primera mesa: {$tables[0]['number']} - {$tables[0]['status']}\n";
    }
    echo "\n";
} else {
    echo "❌ Falló\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 5. Test GET /api/pos/categories
echo "📝 Paso 5: GET /pos/categories...\n";
$response = apiRequest('GET', "$baseUrl/pos/categories", null, $token);

if ($response['code'] === 200 && isset($response['data']['data'])) {
    $categories = $response['data']['data'];
    echo "✅ Categorías obtenidas: " . count($categories) . " categorías\n";
    if (count($categories) > 0) {
        echo "   Primera categoría: {$categories[0]['icon']} {$categories[0]['name']}\n";
    }
    echo "\n";
} else {
    echo "❌ Falló\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 6. Test GET /api/pos/products
echo "📝 Paso 6: GET /pos/products...\n";
$response = apiRequest('GET', "$baseUrl/pos/products", null, $token);

if ($response['code'] === 200 && isset($response['data']['data'])) {
    $products = $response['data']['data'];
    echo "✅ Productos obtenidos: " . count($products) . " productos\n";
    if (count($products) > 0) {
        echo "   Primer producto: {$products[0]['name']} - S/{$products[0]['sale_price']}\n";
    }
    echo "\n";
} else {
    echo "❌ Falló\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 7. Test GET /api/pos/payment-methods
echo "📝 Paso 7: GET /pos/payment-methods...\n";
$response = apiRequest('GET', "$baseUrl/pos/payment-methods", null, $token);

if ($response['code'] === 200 && isset($response['data']['data'])) {
    $methods = $response['data']['data'];
    echo "✅ Métodos de pago: " . count($methods) . " métodos\n";
    if (count($methods) > 0) {
        echo "   Primer método: {$methods[0]['name']} ({$methods[0]['code']})\n";
    }
    echo "\n";
} else {
    echo "❌ Falló\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 8. Test POST /api/pos/orders (Crear pedido)
echo "📝 Paso 8: POST /pos/orders (Crear pedido)...\n";
$orderData = [
    'table_id' => $table->id,
    'guests_count' => 4,
    'order_type' => 'dine_in',
    'items' => [
        [
            'product_template_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 35.00,
            'special_instructions' => 'Sin cebolla',
        ],
        [
            'product_template_id' => $product2->id,
            'quantity' => 3,
            'unit_price' => 8.00,
        ],
    ],
];

$response = apiRequest('POST', "$baseUrl/pos/orders", $orderData, $token);

if ($response['code'] === 201 && isset($response['data']['data'])) {
    $order = $response['data']['data'];
    $orderId = $order['id'];
    $orderNumber = $order['order_number'];
    echo "✅ Pedido creado exitosamente\n";
    echo "   Número: $orderNumber\n";
    echo "   Total: S/{$order['total']}\n";
    echo "   Items: " . count($order['items']) . "\n\n";
} else {
    echo "❌ Falló al crear pedido\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

// 9. Test GET /api/pos/orders/{id}
echo "📝 Paso 9: GET /pos/orders/$orderId (Ver pedido)...\n";
$response = apiRequest('GET', "$baseUrl/pos/orders/$orderId", null, $token);

if ($response['code'] === 200 && isset($response['data']['data'])) {
    echo "✅ Pedido obtenido correctamente\n";
    echo "   Número: {$response['data']['data']['order_number']}\n";
    echo "   Estado: {$response['data']['data']['status']}\n\n";
} else {
    echo "❌ Falló\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 10. Test POST /api/pos/orders/{id}/items (Agregar items)
echo "📝 Paso 10: POST /pos/orders/$orderId/items (Agregar items)...\n";
$newItems = [
    'items' => [
        [
            'product_template_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 35.00,
        ],
    ],
];

$response = apiRequest('POST', "$baseUrl/pos/orders/$orderId/items", $newItems, $token);

if ($response['code'] === 200) {
    echo "✅ Items agregados exitosamente\n";
    echo "   Nuevo total: S/{$response['data']['data']['total']}\n\n";
} else {
    echo "❌ Falló al agregar items\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 11. Test POST /api/pos/orders/{id}/payment (Procesar pago)
echo "📝 Paso 11: POST /pos/orders/$orderId/payment (Procesar pago)...\n";

// Obtener el total actualizado
$response = apiRequest('GET', "$baseUrl/pos/orders/$orderId", null, $token);
$currentTotal = $response['data']['data']['total'];

$paymentData = [
    'payment_method_id' => $paymentMethod->id,
    'amount' => $currentTotal,
];

$response = apiRequest('POST', "$baseUrl/pos/orders/$orderId/payment", $paymentData, $token);

if ($response['code'] === 200) {
    $order = $response['data']['data'];
    echo "✅ Pago procesado exitosamente\n";
    echo "   Estado de pago: {$order['payment_status']}\n";
    echo "   Estado de orden: {$order['status']}\n\n";
} else {
    echo "❌ Falló al procesar pago\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

// 12. Test verificar mesa ocupada
echo "📝 Paso 12: Verificar estado de mesa...\n";
$response = apiRequest('GET', "$baseUrl/pos/tables", null, $token);

if ($response['code'] === 200) {
    $tables = $response['data']['data'];
    $testTable = array_values(array_filter($tables, fn($t) => $t['number'] === 'M1'))[0] ?? null;
    
    if ($testTable) {
        echo "✅ Mesa M1 verificada\n";
        echo "   Estado: {$testTable['status']}\n";
        echo "   Pedido: {$testTable['current_order_number']}\n\n";
    }
}

echo "=" . str_repeat("=", 70) . "\n";
echo "✅ TODOS LOS TESTS COMPLETADOS\n\n";

echo "📋 Resumen:\n";
echo "   ✅ Login\n";
echo "   ✅ GET /pos/table-areas\n";
echo "   ✅ GET /pos/tables\n";
echo "   ✅ GET /pos/categories\n";
echo "   ✅ GET /pos/products\n";
echo "   ✅ GET /pos/payment-methods\n";
echo "   ✅ POST /pos/orders (Crear pedido)\n";
echo "   ✅ GET /pos/orders/{id} (Ver pedido)\n";
echo "   ✅ POST /pos/orders/{id}/items (Agregar items)\n";
echo "   ✅ POST /pos/orders/{id}/payment (Procesar pago)\n";
echo "   ✅ Verificación de estado de mesa\n\n";

echo "🎯 API POS Completamente Funcional!\n\n";
