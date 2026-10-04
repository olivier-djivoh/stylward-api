<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreShopRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique.'], 404);
        }

        return response()->json($shop);
    }

    public function store(StoreShopRequest $request): JsonResponse
    {
        $user = $request->user();

        // Un styliste ne peut avoir qu'une boutique (règle MVP)
        if ($user->shop) {
            return response()->json([
                'message' => 'Vous avez déjà une boutique.',
            ], 409);
        }

        $data = $request->validated();

        // Slug : généré si non fourni, toujours unique
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $slug = $this->ensureUniqueSlug($slug);

        $shop = Shop::create([
            'user_id'     => $user->id,
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'whatsapp'    => $data['whatsapp'] ?? null,
            'status'      => 'draft',
        ]);

        return response()->json($shop, 201);
    }

    public function update(StoreShopRequest $request, Shop $shop): JsonResponse
    {
        $this->authorize('update', $shop);

        $data = $request->validated();

        if (!empty($data['slug']) && $data['slug'] !== $shop->slug) {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $shop->id);
        } else {
            unset($data['slug']);
        }

        $shop->update($data);

        return response()->json($shop);
    }

    /**
     * Génère un slug unique. Ajoute -2, -3... si déjà pris.
     */
    private function ensureUniqueSlug(string $slug, ?string $ignoreId = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            Shop::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function activate(Request $request): JsonResponse
    {
    $shop = $request->user()->shop;

    if (!$shop) {
        return response()->json(['message' => 'Aucune boutique.'], 404);
    }

    $shop->update(['status' => 'active']);

    return response()->json($shop);
   }
}
