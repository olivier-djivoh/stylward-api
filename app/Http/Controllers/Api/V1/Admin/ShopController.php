<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shops = Shop::query()
            ->with('user:id,first_name,last_name,email,status')
            ->withCount('models')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'ilike', $search)
                        ->orWhere('slug', 'ilike', $search);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($shops);
    }

    public function show(Shop $shop): JsonResponse
    {
        $shop->load('user:id,first_name,last_name,email,status');
        $shop->loadCount('models');

        return response()->json($shop);
    }

    public function suspend(Shop $shop): JsonResponse
    {
        $shop->update(['status' => 'suspended']);

        return response()->json([
            'message' => 'Boutique suspendue.',
            'shop'    => $shop->only(['id', 'name', 'slug', 'status']),
        ]);
    }

    public function activate(Shop $shop): JsonResponse
    {
        $shop->update(['status' => 'active']);

        return response()->json([
            'message' => 'Boutique activée.',
            'shop'    => $shop->only(['id', 'name', 'slug', 'status']),
        ]);
    }
}
