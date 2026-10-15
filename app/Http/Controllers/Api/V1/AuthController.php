<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\UpdatePasswordRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addDays(7)
        )->plainTextToken;

        return response()->json([
            'user'  => $user->only(['id', 'first_name', 'last_name', 'email', 'role']),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Compte suspendu.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        // Révoque les anciens tokens (sécurité)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        $pendingDeletion = $user->isPendingDeletion();

        return response()->json([
            'user'  => $user->only(['id', 'first_name', 'last_name', 'email', 'role', 'profession', 'gender']),
            'token'               => $token,
            'pending_deletion'    => $pendingDeletion,
            'deletion_due_at'     => $pendingDeletion ? $user->deletionDueAt()?->toIso8601String() : null,
            'days_until_deletion' => $pendingDeletion ? $user->daysUntilDeletion() : null,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->only([
            'id', 'first_name', 'last_name', 'email', 'phone', 'role', 'profession', 'gender', 'status',
        ]));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
{
    $user = $request->user();

    if (!\Hash::check($request->current_password, $user->password)) {
        throw ValidationException::withMessages([
            'current_password' => ['Le mot de passe actuel est incorrect.'],
        ]);
    }

    $user->update([
        'password' => \Hash::make($request->password),
    ]);

    // Déconnecte tous les autres appareils par sécurité
    $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

    return response()->json(['message' => 'Mot de passe mis à jour.']);
}

public function deleteAccount(Request $request): JsonResponse
{
    $user = $request->user();

    $request->validate([
        'password' => ['required', 'string'],
    ]);

    if (!\Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'password' => ['Mot de passe incorrect.'],
        ]);
    }

    // Supprime tous les tokens et le compte (soft delete)
    $user->tokens()->delete();
    $user->delete();

    return response()->json(['message' => 'Compte supprimé.']);
}

}

