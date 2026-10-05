<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->where('role', 'stylist')
            ->with('shop')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'ilike', $search)
                        ->orWhere('last_name', 'ilike', $search)
                        ->orWhere('email', 'ilike', $search);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        if ($user->role !== 'stylist') {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $user->load('shop');

        return response()->json($user);
    }

    public function suspend(User $user): JsonResponse
    {
        if ($user->role !== 'stylist') {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $user->update(['status' => 'suspended']);

        return response()->json([
            'message' => 'Compte styliste suspendu.',
            'user'    => $user->only(['id', 'first_name', 'last_name', 'email', 'status']),
        ]);
    }

    public function activate(User $user): JsonResponse
    {
        if ($user->role !== 'stylist') {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $user->update(['status' => 'active']);

        return response()->json([
            'message' => 'Compte styliste réactivé.',
            'user'    => $user->only(['id', 'first_name', 'last_name', 'email', 'status']),
        ]);
    }
}
