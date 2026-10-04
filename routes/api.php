<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ModelController;
use App\Http\Controllers\Api\V1\ShopController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PublicShopController;
use App\Http\Controllers\Api\V1\ModelImageController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;

Route::prefix('v1')->group(function () {

    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });


    // 🌍 Routes publiques (pas d'auth)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/public/shops/{slug}',           [PublicShopController::class, 'show']);
        Route::get('/public/shops/{slug}/models',    [PublicShopController::class, 'models']);
        Route::get('/public/shops/{slug}/models/{modelId}', [PublicShopController::class, 'model']);
        Route::get('/categories', [CategoryController::class, 'index']);
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::patch('/shop/activate', [ShopController::class, 'activate']);
        Route::post('/models/{model}/images', [ModelImageController::class, 'store']);
        Route::delete('/models/{model}/images/{image}', [ModelImageController::class, 'destroy']);

        // Boutique
        Route::get('/shop',         [ShopController::class, 'show']);
        Route::post('/shop',        [ShopController::class, 'store']);
        Route::put('/shop/{shop}',  [ShopController::class, 'update']);

        // Modèles
        Route::get('/models',                   [ModelController::class, 'index']);
        Route::post('/models',                  [ModelController::class, 'store']);
        Route::get('/models/{model}',           [ModelController::class, 'show']);
        Route::put('/models/{model}',           [ModelController::class, 'update']);
        Route::patch('/models/{model}/publish', [ModelController::class, 'publish']);
        Route::patch('/models/{model}/unpublish', [ModelController::class, 'unpublish']);
        Route::delete('/models/{model}',        [ModelController::class, 'destroy']);

        // Admin SaaS (rôle admin uniquement)
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/categories',              [AdminCategoryController::class, 'index']);
            Route::post('/categories',             [AdminCategoryController::class, 'store']);
            Route::put('/categories/{category}',   [AdminCategoryController::class, 'update']);
            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);
        });
    });
});
