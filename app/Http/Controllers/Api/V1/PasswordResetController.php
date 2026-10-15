<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Envoie le lien de réinitialisation par email.
     */
    public function sendLink(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Anti-énumération : on renvoie TOUJOURS un succès,
        // sauf si throttlé.
        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'message' => 'Veuillez patienter avant de réessayer.',
            ], 429);
        }

        return response()->json([
            'message' => 'Si un compte existe avec cette adresse, un email vient de vous être envoyé.',
        ]);
    }

    /**
     * Réinitialise le mot de passe avec le token.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();

                // Sécurité : déconnecte tous les appareils
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Votre mot de passe a été réinitialisé avec succès.',
            ]);
        }

        return response()->json([
            'message' => 'Ce lien de réinitialisation est invalide ou expiré.',
        ], 422);
    }
}
