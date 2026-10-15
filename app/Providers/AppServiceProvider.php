<?php

namespace App\Providers;

use App\Models\ClothingModel;
use App\Models\Shop;
use App\Policies\ClothingModelPolicy;
use App\Policies\ShopPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    Gate::policy(ClothingModel::class, ClothingModelPolicy::class);
    Gate::policy(Shop::class, ShopPolicy::class);

    // Redirige le lien de reset vers le frontend React
    ResetPassword::createUrlUsing(function ($user, string $token) {
        return config('app.frontend_url')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($user->email);
    });
}
}
