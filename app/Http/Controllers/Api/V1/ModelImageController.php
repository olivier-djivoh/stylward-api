<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Model\UploadImageRequest;
use App\Models\ClothingModel;
use App\Models\ModelImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModelImageController extends Controller
{
    public function store(UploadImageRequest $request, ClothingModel $model): JsonResponse
    {
        $this->authorize('update', $model);

        $type = $request->input('type', 'gallery');

        // Une seule image principale par modèle
        if ($type === 'main') {
            $model->images()->where('type', 'main')->delete();
        }

        $file = $request->file('image');

        // Nom de fichier aléatoire : impossible à deviner, impossible d'écraser
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . strtolower($extension);
        $folder    = "models/{$model->shop_id}/{$model->id}";

        // Stockage sur le disque public
        $path = $file->storeAs($folder, $filename, 'public');

        $image = ModelImage::create([
            'model_id'   => $model->id,
            'path'       => $path,
            'type'       => $type,
            'sort_order' => $model->images()->max('sort_order') + 1,
            'size_bytes' => $file->getSize(),
            'mime_type'  => $file->getMimeType(),
        ]);

        return response()->json([
            'id'         => $image->id,
            'path'       => $path,
            'url'        => Storage::disk('public')->url($path),
            'type'       => $image->type,
            'sort_order' => $image->sort_order,
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
}
