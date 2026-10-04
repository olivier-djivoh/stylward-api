<?php

namespace App\Providers;

use App\Models\ClothingModel;
use App\Models\Shop;
use App\Policies\ClothingModelPolicy;
use App\Policies\ShopPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
    }
}
