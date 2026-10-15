<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClothingModel;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    /**
     * Liste des collections de l'artisan de la mode  connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique.'], 404);
        }

        $collections = Collection::where('shop_id', $shop->id)
            ->withCount('models')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $collections]);
    }

    /**
     * Créer une collection.
     */
    public function store(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique.'], 404);
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $slug = $this->ensureUniqueSlug($shop->id, Str::slug($data['name']));

        $collection = Collection::create([
            'shop_id'     => $shop->id,
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'status'      => 'draft',
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);

        return response()->json($collection, 201);
    }

    /**
     * Détail d'une collection avec ses modèles.
     */
    public function show(Request $request, Collection $collection): JsonResponse
    {
        $this->authorizeCollection($request, $collection);

        $collection->load(['models' => function ($q) {
            $q->with(['category', 'images']);
        }]);

        return response()->json($collection);
    }

    /**
     * Mettre à jour une collection.
     */
    public function update(Request $request, Collection $collection): JsonResponse
    {
        $this->authorizeCollection($request, $collection);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        if (isset($data['name']) && $data['name'] !== $collection->name) {
            $data['slug'] = $this->ensureUniqueSlug($collection->shop_id, Str::slug($data['name']), $collection->id);
        }

        $collection->update($data);

        return response()->json($collection);
    }

    /**
     * Supprimer une collection.
     */
    public function destroy(Request $request, Collection $collection): JsonResponse
    {
        $this->authorizeCollection($request, $collection);

        // Supprime la couverture du disque
        if ($collection->cover_path) {
            Storage::disk('public')->delete($collection->cover_path);
        }

        $collection->delete();

        return response()->json(null, 204);
    }

    /**
     * Publier une collection.
     */
    public function publish(Request $request, Collection $collection): JsonResponse
    {
        $this->authorizeCollection($request, $collection);

        $collection->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return response()->json($collection);
    }

    /**
     * Dépublier une collection.
     */
    public function unpublish(Request $request, Collection $collection): JsonResponse
    {
        $this->authorizeCollection($request, $collection);

        $collection->update([
            'status'       => 'draft',
            'published_at' => null,
        ]);

        return response()->json($collection);
    }

    /**
     * Ajouter un modèle à une collection.
     */
    public function addModel(Request $request, Collection $collection): JsonResponse
{
    $this->authorizeCollection($request, $collection);

    $data = $request->validate([
        'model_id' => ['required', 'uuid', 'exists:models,id'],
    ]);

    // Vérifie que le modèle appartient à la même boutique
    $model = ClothingModel::findOrFail($data['model_id']);
    if ($model->shop_id !== $collection->shop_id) {
        abort(403, 'Ce modèle n\'appartient pas à votre boutique.');
    }

    // Vérifie si déjà dans la collection
    $alreadyIn = $collection->models()
        ->where('models.id', $model->id)
        ->exists();

    if ($alreadyIn) {
        return response()->json(['ok' => true, 'message' => 'Déjà dans la collection.']);
    }

    // Attache (l'id sera généré par PostgreSQL)
    $collection->models()->attach($model->id, [
        'sort_order' => $collection->models()->count(),
        'created_at' => now(),
    ]);

    return response()->json(['ok' => true]);
}

    /**
     * Retirer un modèle d'une collection.
     */
    public function removeModel(Request $request, Collection $collection, string $modelId): JsonResponse
    {
        $this->authorizeCollection($request, $collection);

        $collection->models()->detach($modelId);

        return response()->json(null, 204);
    }

    /**
     * Upload de la couverture.
     */
    public function uploadCover(Request $request, Collection $collection): JsonResponse
    {
        $this->authorizeCollection($request, $collection);

        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($collection->cover_path) {
            Storage::disk('public')->delete($collection->cover_path);
        }

        $file = $request->file('image');
        $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("shops/{$collection->shop_id}/collections", $filename, 'public');

        $collection->update(['cover_path' => $path]);

        return response()->json([
            'cover_path' => $path,
            'url'        => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Vérifie l'appartenance.
     */
    private function authorizeCollection(Request $request, Collection $collection): void
    {
        $shop = $request->user()->shop;
        if (!$shop || $collection->shop_id !== $shop->id) {
            abort(403, 'Accès refusé.');
        }
    }

    private function ensureUniqueSlug(string $shopId, string $slug, ?string $ignoreId = null): string
    {
        $base = $slug ?: 'collection';
        $slug = $base;
        $i = 2;

        while (
            Collection::withTrashed()
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
