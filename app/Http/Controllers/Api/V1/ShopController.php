<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreShopRequest;
use App\Http\Requests\Shop\UploadShopImageRequest;
use App\Models\Shop;
use App\Models\ShopVisit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

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

        if ($user->shop) {
            return response()->json([
                'message' => 'Vous avez déjà une boutique.',
            ], 409);
        }

        $data = $request->validated();

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

    public function activate(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique.'], 404);
        }

        $shop->update(['status' => 'active']);

        return response()->json($shop);
    }

    public function uploadLogo(UploadShopImageRequest $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique.'], 404);
        }

        if ($shop->logo_path) {
            Storage::disk('public')->delete($shop->logo_path);
        }

        $file = $request->file('image');
        $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("shops/{$shop->id}/branding", $filename, 'public');

        $shop->update(['logo_path' => $path]);

        return response()->json([
            'message'   => 'Logo mis à jour.',
            'logo_path' => $path,
            'url'       => Storage::disk('public')->url($path),
        ]);
    }

    public function uploadCover(UploadShopImageRequest $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique.'], 404);
        }

        if ($shop->cover_path) {
            Storage::disk('public')->delete($shop->cover_path);
        }

        $file = $request->file('image');
        $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("shops/{$shop->id}/branding", $filename, 'public');

        $shop->update(['cover_path' => $path]);

        return response()->json([
            'message'    => 'Couverture mise à jour.',
            'cover_path' => $path,
            'url'        => Storage::disk('public')->url($path),
        ]);
    }

    public function stats(Request $request): JsonResponse
{
    $shop = $request->user()->shop;

    if (!$shop) {
        return response()->json(['message' => 'Aucune boutique.'], 404);
    }

    $now = Carbon::now();
    $weekAgo = $now->copy()->subDays(7);
    $twoWeeksAgo = $now->copy()->subDays(14);

    // ----- VUES TOTALES (chaque visite compte) -----
    $totalViews = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'view_shop')
        ->count();

    $viewsThisWeek = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'view_shop')
        ->where('created_at', '>=', $weekAgo)
        ->count();

    $viewsLastWeek = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'view_shop')
        ->whereBetween('created_at', [$twoWeeksAgo, $weekAgo])
        ->count();

    // ----- SPECTATEURS UNIQUES (basé sur le hash IP) -----
    $uniqueVisitors = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'view_shop')
        ->whereNotNull('ip_hash')
        ->distinct('ip_hash')
        ->count('ip_hash');

    $uniqueVisitorsThisWeek = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'view_shop')
        ->where('created_at', '>=', $weekAgo)
        ->whereNotNull('ip_hash')
        ->distinct('ip_hash')
        ->count('ip_hash');

    $uniqueVisitorsLastWeek = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'view_shop')
        ->whereBetween('created_at', [$twoWeeksAgo, $weekAgo])
        ->whereNotNull('ip_hash')
        ->distinct('ip_hash')
        ->count('ip_hash');

    // ----- CLICS WHATSAPP -----
    $whatsappClicksThisWeek = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'whatsapp_click')
        ->where('created_at', '>=', $weekAgo)
        ->count();

    $whatsappClicksLastWeek = ShopVisit::where('shop_id', $shop->id)
        ->where('event', 'whatsapp_click')
        ->whereBetween('created_at', [$twoWeeksAgo, $weekAgo])
        ->count();

    // ----- TOP 5 MODÈLES -----
    $topModels = ShopVisit::query()
    ->where('shop_id', $shop->id)
    ->where('event', 'view_model')
    ->whereNotNull('model_id')
    ->selectRaw('model_id, COUNT(*) as views, COUNT(DISTINCT ip_hash) as visitors')
    ->groupBy('model_id')
    ->orderByDesc('views')
    ->limit(5)
    ->with('model:id,name')
    ->get()
    ->map(fn ($v) => [
        'id'       => $v->model_id,
        'name'     => $v->model?->name ?? 'Modèle supprimé',
        'views'    => $v->views,
        'visitors' => $v->visitors,
    ]);

    // ----- SÉRIE 7 JOURS -----
    $dailyViews = collect(range(6, 0))->map(function ($daysAgo) use ($shop) {
        $date = Carbon::now()->subDays($daysAgo)->startOfDay();
        $count = ShopVisit::where('shop_id', $shop->id)
            ->where('event', 'view_shop')
            ->whereDate('created_at', $date)
            ->count();

        return [
            'date'  => $date->format('Y-m-d'),
            'label' => $date->locale('fr')->isoFormat('ddd'),
            'count' => $count,
        ];
    });

    return response()->json([
        'views' => [
            'this_week' => $viewsThisWeek,
            'last_week' => $viewsLastWeek,
            'total'     => $totalViews,
            'evolution' => $viewsLastWeek > 0
                ? round((($viewsThisWeek - $viewsLastWeek) / $viewsLastWeek) * 100)
                : null,
        ],
        'visitors' => [
            'this_week' => $uniqueVisitorsThisWeek,
            'last_week' => $uniqueVisitorsLastWeek,
            'total'     => $uniqueVisitors,
            'evolution' => $uniqueVisitorsLastWeek > 0
                ? round((($uniqueVisitorsThisWeek - $uniqueVisitorsLastWeek) / $uniqueVisitorsLastWeek) * 100)
                : null,
        ],
        'whatsapp_clicks' => [
            'this_week' => $whatsappClicksThisWeek,
            'last_week' => $whatsappClicksLastWeek,
            'evolution' => $whatsappClicksLastWeek > 0
                ? round((($whatsappClicksThisWeek - $whatsappClicksLastWeek) / $whatsappClicksLastWeek) * 100)
                : null,
        ],
        'top_models'  => $topModels,
        'daily_views' => $dailyViews,
    ]);
}

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

    public function exportCatalogue(Request $request)
{
    $shop = $request->user()->shop;

    if (!$shop) {
        return response()->json(['message' => 'Aucune boutique.'], 404);
    }

    $models = \App\Models\ClothingModel::where('shop_id', $shop->id)
        ->where('status', 'published')
        ->with(['category', 'images'])
        ->orderBy('created_at')
        ->get();

    $pdf = Pdf::loadView('pdf.catalogue', [
        'shop'   => $shop,
        'models' => $models,
    ]);

    $pdf->setPaper('a4', 'portrait');
    $pdf->setOption('isRemoteEnabled', true);
    $pdf->setOption('isHtml5ParserEnabled', true);

    $filename = 'catalogue-' . $shop->slug . '.pdf';

    return $pdf->download($filename);
}
}
