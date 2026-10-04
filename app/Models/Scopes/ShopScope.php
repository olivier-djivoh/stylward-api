<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ShopScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // Pas connecté → pas de filtre (utile pour le public)
        if (!$user) {
            return;
        }

        // Admin SaaS → voit tout
        if ($user->role === 'admin') {
            return;
        }

        // Styliste → voit UNIQUEMENT les modèles de sa boutique
        if ($user->role === 'stylist') {
            $builder->whereHas('shop', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
    }
}
