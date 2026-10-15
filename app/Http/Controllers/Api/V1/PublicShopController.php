<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModelResource;
use App\Models\ClothingModel;
use App\Models\Shop;
use App\Models\ShopVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Collection;

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

    // Notifications pour l'artisan de la mode
    if ($shop->user) {
        if ($data['event'] === 'view_shop') {
            // 1 seule notification par heure max (anti-spam)
            $lastVisit = \App\Models\Notification::where('user_id', $shop->user->id)
                ->where('type', 'new_visit')
                ->where('created_at', '>=', now()->subHour())
                ->exists();

            if (!$lastVisit) {
                \App\Models\Notification::notify(
                    $shop->user,
                    'new_visit',
                    'Nouvelle visite sur votre boutique',
                    'Quelqu\'un vient de découvrir vos créations.',
                );
            }
        } elseif ($data['event'] === 'whatsapp_click') {
            \App\Models\Notification::notify(
                $shop->user,
                'whatsapp_click',
                'Nouveau contact WhatsApp',
                'Un client souhaite commander une création.',
                ['model_id' => $data['model_id'] ?? null],
            );
        }
    }

    return response()->json(['ok' => true]);
}

/**
 * Liste des collections publiques de la boutique.
 */
public function collections(string $slug): JsonResponse
{
    $shop = Shop::where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

    $collections = Collection::where('shop_id', $shop->id)
        ->where('status', 'published')
        ->withCount('models')
        ->orderBy('sort_order')
        ->orderByDesc('published_at')
        ->get();

    return response()->json(['data' => $collections]);
}

/**
 * Détail public d'une collection avec ses modèles.
 */
public function collection(string $slug, string $collectionSlug): JsonResponse
{
    $shop = Shop::where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

    $collection = Collection::where('shop_id', $shop->id)
        ->where('slug', $collectionSlug)
        ->where('status', 'published')
        ->with(['models' => function ($q) {
            $q->where('status', 'published')
                ->with(['category', 'images']);
        }])
        ->firstOrFail();

    return response()->json([
        'id'          => $collection->id,
        'name'        => $collection->name,
        'slug'        => $collection->slug,
        'description' => $collection->description,
        'cover_path'  => $collection->cover_path,
        'models'      => $collection->models->map(fn ($m) => [
            'id'         => $m->id,
            'name'       => $m->name,
            'slug'       => $m->slug,
            'price'      => $m->price,
            'currency'   => $m->currency,
            'color'      => $m->color,
            'category'   => $m->category ? [
                'id'   => $m->category->id,
                'name' => $m->category->name,
                'slug' => $m->category->slug,
            ] : null,
            'images'     => $m->images->map(fn ($i) => [
                'id'   => $i->id,
                'path' => $i->path,
                'type' => $i->type,
            ]),
        ]),
    ]);
}
}
