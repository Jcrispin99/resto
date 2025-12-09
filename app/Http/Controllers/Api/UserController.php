<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all active users/employees
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by role if provided
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Get waiters only
     */
    public function waiters(): JsonResponse
    {
        // For now, return all users
        // TODO: Filter by actual role when roles are implemented
        $users = User::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Get cashiers only
     */
    public function cashiers(): JsonResponse
    {
        // For now, return all users
        // TODO: Filter by actual role when roles are implemented
        $users = User::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Get single user
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }
}
