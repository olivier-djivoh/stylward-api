<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ModelController;
use App\Http\Controllers\Api\V1\ShopController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PublicShopController;
use App\Http\Controllers\Api\V1\ModelImageController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Api\V1\TrackController;
use App\Http\Controllers\Api\V1\AccountDeletionController;
use App\Http\Controllers\Api\V1\Admin\ArchiveController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\PasswordResetController;

Route::prefix('v1')->group(function () {

    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);

        Route::post('/forgot-password', [PasswordResetController::class, 'sendLink']);
        Route::post('/reset-password',  [PasswordResetController::class, 'reset']);
    });


    // 🌍 Routes publiques (pas d'auth)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/public/shops/{slug}',           [PublicShopController::class, 'show']);
        Route::get('/public/shops/{slug}/models',    [PublicShopController::class, 'models']);
        Route::get('/public/shops/{slug}/models/{modelId}', [PublicShopController::class, 'model']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/public/shops/{shop:slug}/track', [PublicShopController::class, 'track']);
        Route::post('/account/confirm-deletion/{token}', [AccountDeletionController::class, 'confirm']);
        Route::get('/public/shops/{slug}/collections',                    [PublicShopController::class, 'collections']);
        Route::get('/public/shops/{slug}/collections/{collectionSlug}',   [PublicShopController::class, 'collection']);
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/shop/catalogue-pdf', [ShopController::class, 'exportCatalogue']);
        Route::patch('/shop/activate', [ShopController::class, 'activate']);
        Route::post('/models/{model}/images', [ModelImageController::class, 'store']);
        Route::delete('/models/{model}/images/{image}', [ModelImageController::class, 'destroy']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::delete('/account', [AuthController::class, 'deleteAccount']);
        Route::post('/account/request-deletion', [AccountDeletionController::class, 'request']);
        Route::post('/account/cancel-deletion',  [AccountDeletionController::class, 'cancel']);

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
        Route::post('/models/{model}/duplicate', [ModelController::class, 'duplicate']);
        Route::patch('/models/{model}/unpublish', [ModelController::class, 'unpublish']);
        Route::delete('/models/{model}',        [ModelController::class, 'destroy']);
        Route::patch('/models/{model}/images/{image}/set-main', [ModelImageController::class, 'setMain']);

        // Collections (styliste)
        Route::get('/collections',                          [CollectionController::class, 'index']);
        Route::post('/collections',                         [CollectionController::class, 'store']);
        Route::get('/collections/{collection}',             [CollectionController::class, 'show']);
        Route::put('/collections/{collection}',             [CollectionController::class, 'update']);
        Route::delete('/collections/{collection}',          [CollectionController::class, 'destroy']);
        Route::patch('/collections/{collection}/publish',   [CollectionController::class, 'publish']);
        Route::patch('/collections/{collection}/unpublish', [CollectionController::class, 'unpublish']);
        Route::post('/collections/{collection}/models',     [CollectionController::class, 'addModel']);
        Route::delete('/collections/{collection}/models/{modelId}', [CollectionController::class, 'removeModel']);
        Route::post('/collections/{collection}/cover',      [CollectionController::class, 'uploadCover']);
    

        // Admin SaaS (rôle admin uniquement)
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/categories',              [AdminCategoryController::class, 'index']);
            Route::post('/categories',             [AdminCategoryController::class, 'store']);
            Route::put('/categories/{category}',   [AdminCategoryController::class, 'update']);
            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

            // Admin : Stylistes
            Route::get('/stylists',                    [AdminUserController::class, 'index']);
            Route::get('/stylists/{user}',             [AdminUserController::class, 'show']);
            Route::patch('/stylists/{user}/suspend',   [AdminUserController::class, 'suspend']);
            Route::patch('/stylists/{user}/activate',  [AdminUserController::class, 'activate']);

            // Admin : Boutiques
            Route::get('/shops',                       [AdminShopController::class, 'index']);
            Route::get('/shops/{shop}',                [AdminShopController::class, 'show']);
            Route::patch('/shops/{shop}/suspend',      [AdminShopController::class, 'suspend']);
            Route::patch('/shops/{shop}/activate',     [AdminShopController::class, 'activate']);
            Route::get('/archives',       [ArchiveController::class, 'index']);
            Route::get('/archives/stats', [ArchiveController::class, 'stats']);
            Route::delete('/archives', [ArchiveController::class, 'clear']);
        });

            Route::post('/shop/logo',  [ShopController::class, 'uploadLogo']);
            Route::post('/shop/cover', [ShopController::class, 'uploadCover']);

            Route::get('/shop/stats', [ShopController::class, 'stats']);


            // Notifications
            Route::get('/notifications',                    [NotificationController::class, 'index']);
            Route::get('/notifications/unread-count',       [NotificationController::class, 'unreadCount']);
            Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
            Route::patch('/notifications/read-all',         [NotificationController::class, 'markAllAsRead']);
            Route::delete('/notifications/{notification}',  [NotificationController::class, 'destroy']);
    });
});
