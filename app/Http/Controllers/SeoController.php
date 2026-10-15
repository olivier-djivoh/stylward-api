<?php

namespace App\Http\Controllers;

use App\Models\ClothingModel;
use App\Models\Shop;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    /**
     * Aperçu Open Graph pour une boutique.
     */
    public function shop(string $slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $title = $shop->name . ' — Boutique en ligne';
        $description = $shop->description
            ?: 'Découvrez les créations de ' . $shop->name;

        $image = $shop->cover_path
            ? asset('storage/' . $shop->cover_path)
            : asset('images/og-default.jpg');

        $canonicalUrl = config('app.frontend_url') . '/shops/' . $shop->slug;

        return view('seo.shop', compact(
            'shop',
            'title',
            'description',
            'image',
            'canonicalUrl'
        ));
    }

    /**
     * Aperçu Open Graph pour un modèle.
     */
    public function model(string $slug, string $modelId)
    {
        $shop = Shop::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $model = ClothingModel::where('id', $modelId)
            ->where('shop_id', $shop->id)
            ->where('status', 'published')
            ->with('images')
            ->firstOrFail();

        $mainImage = $model->images->firstWhere('type', 'main')
            ?? $model->images->first();

        $image = $mainImage
            ? asset('storage/' . $mainImage->path)
            : asset('images/og-default.jpg');

        $title = $model->name . ' — ' . $shop->name;
        $description = $model->description
            ?: 'Découvrez ' . $model->name . ' chez ' . $shop->name;

        $canonicalUrl = config('app.frontend_url')
            . '/shops/' . $shop->slug
            . '/models/' . $model->id;

        return view('seo.model', compact(
            'shop',
            'model',
            'title',
            'description',
            'image',
            'canonicalUrl'
        ));
    }
}
