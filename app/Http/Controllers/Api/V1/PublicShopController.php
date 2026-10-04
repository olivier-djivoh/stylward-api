<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModelResource;
use App\Models\ClothingModel;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

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
}
