<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModelResource;
use App\Models\ClothingModel;
use App\Models\Shop;
use App\Models\ShopVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicShopController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $shop = Shop::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return response()->json([
            'id'          => $shop->id,
            'name'        => $shop->name,
            'slug'        => $shop->slug,
            'description' => $shop->description,
            'phone'       => $shop->phone,
            'whatsapp'    => $shop->whatsapp,
            'logo_path'   => $shop->logo_path,
            'cover_path'  => $shop->cover_path,
        ]);
    }

    public function models(string $slug): JsonResponse
    {
        $shop = Shop::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $models = ClothingModel::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where('status', 'published')
            ->with(['category', 'images'])
            ->orderByDesc('published_at')
            ->paginate(20);

        return ModelResource::collection($models)->response();
    }

    public function model(string $slug, string $modelId): JsonResponse
    {
        $shop = Shop::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $model = ClothingModel::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where('id', $modelId)
            ->where('status', 'published')
            ->with(['category', 'images'])
            ->firstOrFail();

        return (new ModelResource($model))->response();
    }

    /**
     * Endpoint de tracking (appelé par le frontend).
     */
    public function track(Request $request, string $slug): JsonResponse
{
    $shop = Shop::where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

    $data = $request->validate([
        'event'    => ['required', 'in:view_shop,view_model,whatsapp_click'],
        'model_id' => ['nullable', 'uuid', 'exists:models,id'],
    ]);

    // EMPREINTE UNIQUE = IP + User Agent
    // → PC Chrome, PC Edge, Téléphone = 3 empreintes différentes
    // → Même personne qui revient = 1 seule empreinte
    $ip = $request->header('X-Forwarded-For')
        ?? $request->ip()
        ?? 'unknown';

    $userAgent = (string) $request->userAgent();

    $fingerprint = hash(
        'sha256',
        $ip . '|' . $userAgent . '|' . config('app.key')
    );

    ShopVisit::create([
        'shop_id'    => $shop->id,
        'model_id'   => $data['model_id'] ?? null,
        'event'      => $data['event'],
        'ip_hash'    => $fingerprint,
        'user_agent' => substr($userAgent, 0, 255),
    ]);

    return response()->json(['ok' => true]);
}
}
