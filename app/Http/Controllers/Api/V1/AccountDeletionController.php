<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\RequestDeletionRequest;
use App\Mail\AccountDeletionConfirmedMail;
use App\Mail\AccountDeletionRequestMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountDeletionController extends Controller
{
    public function request(RequestDeletionRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isPendingDeletion()) {
            return response()->json([
                'message' => 'Une demande de suppression est déjà en cours.',
            ], 409);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Mot de passe incorrect.'],
            ]);
        }

        $token = Str::random(64);

        $user->update([
            'deletion_token'         => hash('sha256', $token),
            'deletion_reason'        => $request->reason,
            'deletion_reason_detail' => $request->reason_detail,
        ]);

        Mail::to($user->email)->send(new AccountDeletionRequestMail($user, $token));

        return response()->json([
            'message' => 'Un email de confirmation vous a été envoyé.',
        ]);
    }

    public function confirm(string $token): JsonResponse
    {
        $hashed = hash('sha256', $token);

        $user = User::where('deletion_token', $hashed)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Lien invalide ou expiré.',
            ], 410);
        }

        if ($user->isPendingDeletion()) {
            // Si déjà en cours de suppression (même token), on renvoie succès
            // car c'est probablement un double-clic ou un rechargement.
            return response()->json([
                'message' => 'Votre compte sera supprimé définitivement dans 30 jours.',
            ]);
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'deletion_requested_at' => now(),
                'deletion_token'        => null,
            ]);

            if ($user->shop) {
                $user->shop->update(['status' => 'suspended']);
            }

            $user->tokens()->delete();
        });

        Mail::to($user->email)->send(new AccountDeletionConfirmedMail($user));

        return response()->json([
            'message' => 'Votre compte sera supprimé définitivement dans 30 jours.',
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isPendingDeletion()) {
            return response()->json([
                'message' => 'Aucune suppression en cours.',
            ], 404);
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'deletion_requested_at'  => null,
                'deletion_token'         => null,
                'deletion_reason'        => null,
                'deletion_reason_detail' => null,
            ]);

            if ($user->shop) {
                $user->shop->update(['status' => 'active']);
            }
        });

        return response()->json([
            'message' => 'Votre compte a été restauré.',
        ]);
    }
}
