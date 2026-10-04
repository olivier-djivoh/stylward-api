<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Model\StoreModelRequest;
use App\Http\Requests\Model\UpdateModelRequest;
use App\Http\Resources\ModelResource;
use App\Models\ClothingModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Le Global Scope filtre automatiquement par shop du user
        $models = ClothingModel::query()
            ->with(['category', 'images'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->orderByDesc('created_at')
            ->paginate(20);

        return ModelResource::collection($models)->response();
    }

    public function show(ClothingModel $model): JsonResponse
    {
        $this->authorize('view', $model);

        $model->load(['category', 'images']);

        return (new ModelResource($model))->response();
    }

    public function store(StoreModelRequest $request): JsonResponse
    {
        $this->authorize('create', ClothingModel::class);

        $user = $request->user();
        $shop = $user->shop;

        if (!$shop) {
            return response()->json([
                'message' => 'Créez votre boutique avant d\'ajouter des modèles.',
            ], 409);
        }

        $data = $request->validated();

        $model = ClothingModel::create([
            'shop_id'     => $shop->id,
            'category_id' => $data['category_id'] ?? null,
            'name'        => $data['name'],
            'slug'        => $this->ensureUniqueSlug($shop->id, Str::slug($data['name'])),
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? null,
            'currency'    => $data['currency'] ?? 'XOF',
            'color'       => $data['color'] ?? null,
            'reference'   => $data['reference'] ?? null,
            'status'      => 'draft',
        ]);

        return (new ModelResource($model->load(['category', 'images'])))->response()->setStatusCode(201);
    }

    public function update(UpdateModelRequest $request, ClothingModel $model): JsonResponse
    {
        $this->authorize('update', $model);

        $data = $request->validated();

        if (isset($data['name']) && $data['name'] !== $model->name) {
            $data['slug'] = $this->ensureUniqueSlug($model->shop_id, Str::slug($data['name']), $model->id);
        }

        $model->update($data);

        return (new ModelResource($model->load(['category', 'images'])))->response();
    }

    public function publish(ClothingModel $model): JsonResponse
    {
        $this->authorize('update', $model);

        $model->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return (new ModelResource($model))->response();
    }

    public function unpublish(ClothingModel $model): JsonResponse
    {
        $this->authorize('update', $model);

        $model->update([
            'status'       => 'draft',
            'published_at' => null,
        ]);

        return (new ModelResource($model))->response();
    }

    public function destroy(ClothingModel $model): JsonResponse
    {
        $this->authorize('delete', $model);

        $model->delete();

        return response()->json(null, 204);
    }

    private function ensureUniqueSlug(string $shopId, string $slug, ?string $ignoreId = null): string
    {
        $base = $slug ?: 'modele';
        $slug = $base;
        $i = 2;

        while (
            ClothingModel::withTrashed()
                ->where('shop_id', $shopId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
