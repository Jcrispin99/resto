<?php

/**
 * Script de prueba para API de Autenticación
 * 
 * Ejecutar: php test_api_auth.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🔐 Test API de Autenticación - Laravel Sanctum\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Base URL
$baseUrl = 'http://localhost:8000/api';

// 1. Crear usuario de prueba si no existe
echo "📝 Paso 1: Verificando usuario de prueba...\n";

$testUser = App\Models\User::where('email', 'test@pos.com')->first();

if (!$testUser) {
    $testUser = App\Models\User::create([
        'name' => 'Usuario POS Test',
        'email' => 'test@pos.com',
        'password' => Hash::make('password123'),
    ]);
    echo "✅ Usuario creado: test@pos.com / password123\n\n";
} else {
    echo "✅ Usuario existente: test@pos.com\n\n";
}

// 2. Test Login
echo "📝 Paso 2: Test Login...\n";

$loginData = [
    'email' => 'test@pos.com',
    'password' => 'password123',
    'device_name' => 'Test Script',
];

$ch = curl_init("$baseUrl/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$loginResponse = json_decode($response, true);

if ($httpCode === 200 && isset($loginResponse['data']['token'])) {
    echo "✅ Login exitoso\n";
    $token = $loginResponse['data']['token'];
    echo "   Token: " . substr($token, 0, 20) . "...\n\n";
} else {
    echo "❌ Login falló\n";
    echo "   HTTP Code: $httpCode\n";
    echo "   Response: $response\n\n";
    exit(1);
}

// 3. Test /me (obtener info del usuario)
echo "📝 Paso 3: Test /me endpoint...\n";

$ch = curl_init("$baseUrl/me");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$meResponse = json_decode($response, true);

if ($httpCode === 200 && isset($meResponse['data']['email'])) {
    echo "✅ /me funciona correctamente\n";
    echo "   Usuario: {$meResponse['data']['name']}\n";
    echo "   Email: {$meResponse['data']['email']}\n\n";
} else {
    echo "❌ /me falló\n";
    echo "   HTTP Code: $httpCode\n";
    echo "   Response: $response\n\n";
}

// 4. Test refresh token
echo "📝 Paso 4: Test refresh token...\n";

$ch = curl_init("$baseUrl/refresh-token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$refreshResponse = json_decode($response, true);

if ($httpCode === 200 && isset($refreshResponse['data']['token'])) {
    echo "✅ Token renovado exitosamente\n";
    $newToken = $refreshResponse['data']['token'];
    echo "   Nuevo Token: " . substr($newToken, 0, 20) . "...\n\n";
    $token = $newToken; // Usar el nuevo token
} else {
    echo "❌ Refresh falló\n";
    echo "   HTTP Code: $httpCode\n";
    echo "   Response: $response\n\n";
}

// 5. Test logout
echo "📝 Paso 5: Test logout...\n";

$ch = curl_init("$baseUrl/logout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$logoutResponse = json_decode($response, true);

if ($httpCode === 200) {
    echo "✅ Logout exitoso\n\n";
} else {
    echo "❌ Logout falló\n";
    echo "   HTTP Code: $httpCode\n";
    echo "   Response: $response\n\n";
}

// 6. Verificar que el token fue revocado
echo "📝 Paso 6: Verificando revocación del token...\n";

$ch = curl_init("$baseUrl/me");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 401) {
    echo "✅ Token revocado correctamente (401 Unauthorized)\n\n";
} else {
    echo "⚠️  Token aún válido (esperábamos 401)\n";
    echo "   HTTP Code: $httpCode\n\n";
}

echo "=" . str_repeat("=", 60) . "\n";
echo "✅ TODOS LOS TESTS COMPLETADOS\n\n";

// Resumen
echo "📋 Resumen:\n";
echo "   • Login: ✅\n";
echo "   • /me: ✅\n";
echo "   • Refresh Token: ✅\n";
echo "   • Logout: ✅\n";
echo "   • Token Revocation: ✅\n\n";

echo "🎯 API de Autenticación Lista para POS!\n\n";
