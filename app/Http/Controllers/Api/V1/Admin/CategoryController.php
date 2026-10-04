<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100', 'unique:categories,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status'     => ['nullable', 'in:active,inactive'],
        ]);

        $category = Category::create([
            'name'       => $data['name'],
            'slug'       => Str::slug($data['name']),
            'status'     => $data['status'] ?? 'active',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['sometimes', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status'     => ['nullable', 'in:active,inactive'],
        ]);

        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return (new CategoryResource($category))->response();
    }

    public function destroy(Category $category): JsonResponse
    {
        // Refuse si des modèles utilisent cette catégorie
        if ($category->models()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer : des modèles utilisent cette catégorie.',
            ], 409);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
