<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and create token
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'string|nullable',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Nombre del dispositivo (útil para gestionar múltiples sesiones)
        $deviceName = $request->device_name ?? 'POS Device';

        // Crear token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    /**
     * Logout (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Revocar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada en todos los dispositivos',
        ]);
    }

    /**
     * Refresh token (revoke current and create new one)
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Obtener el nombre del dispositivo actual
        $deviceName = $request->user()->currentAccessToken()->name;
        
        // Revocar token actual
        $request->user()->currentAccessToken()->delete();
        
        // Crear nuevo token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token renovado exitosamente',
            'data' => [
                'token' => $token,
            ],
        ]);
    }
}
