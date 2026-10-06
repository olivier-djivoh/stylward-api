<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrackEventRequest;
use App\Models\Shop;
use App\Models\ShopVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class TrackController extends Controller
{
    public function track(TrackEventRequest $request, Shop $shop): JsonResponse
    {
        // Rate limiting : max 60 événements / minute / IP
        $key = 'track:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json(['message' => 'Trop de requêtes.'], 429);
        }
        RateLimiter::hit($key, 60);

        // Anonymisation de l'IP (RGPD-friendly)
        $ipHash = $request->ip() ? hash('sha256', $request->ip() . config('app.key')) : null;

        ShopVisit::create([
            'shop_id'    => $shop->id,
            'model_id'   => $request->input('model_id'),
            'event'      => $request->input('event'),
            'ip_hash'    => $ipHash,
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return response()->json(['ok' => true]);
    }
}
