<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Model\UploadImageRequest;
use App\Models\ClothingModel;
use App\Models\ModelImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Laravel\Facades\Image;

class ModelImageController extends Controller
{
    public function store(UploadImageRequest $request, ClothingModel $model): JsonResponse
{
    $this->authorize('update', $model);

    $type = $request->input('type', 'gallery');

    if ($type === 'main') {
        $model->images()->where('type', 'main')->delete();
    }

    $file = $request->file('image');
    $filename = \Illuminate\Support\Str::uuid() . '.jpg';
    $folder = "models/{$model->shop_id}/{$model->id}";
    $path = "{$folder}/{$filename}";

    // On garde une bonne qualité pour les modèles (1000px max) mais on compresse
    $image = Image::read($file)
        ->scaleDown(width: 1000) // Garde le ratio, réduit si > 1000px
        ->toJpeg(85);

    \Illuminate\Support\Facades\Storage::disk('public')->put($path, (string) $image);

    $imageModel = ModelImage::create([
        'model_id'   => $model->id,
        'path'       => $path,
        'type'       => $type,
        'sort_order' => $model->images()->max('sort_order') + 1,
        'size_bytes' => \Illuminate\Support\Facades\Storage::disk('public')->size($path),
        'mime_type'  => 'image/jpeg',
    ]);

    return response()->json([
        'id'         => $imageModel->id,
        'path'       => $path,
        'url'        => \Illuminate\Support\Facades\Storage::url($path),
        'type'       => $imageModel->type,
        'sort_order' => $imageModel->sort_order,
    ], 201);
}

    public function destroy(ClothingModel $model, ModelImage $image): JsonResponse
    {
        $this->authorize('update', $model);

        // Vérification critique : l'image doit appartenir au modèle
        if ($image->model_id !== $model->id) {
            abort(404);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(null, 204);
    }
    public function setMain(ClothingModel $model, ModelImage $image): JsonResponse
{
    $this->authorize('update', $model);

    // Vérification critique : l'image appartient bien à ce modèle
    if ($image->model_id !== $model->id) {
        abort(404);
    }

    DB::transaction(function () use ($model, $image) {
        // Retire le statut "main" de toutes les autres images
        $model->images()
            ->where('id', '!=', $image->id)
            ->where('type', 'main')
            ->update(['type' => 'gallery']);

        // Celle-ci devient la principale
        $image->update(['type' => 'main']);
    });

    return response()->json(['ok' => true]);
}
}
