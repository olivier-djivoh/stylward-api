<?php

use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('crawler')->group(function () {
    Route::get('/shops/{slug}', [SeoController::class, 'shop']);
    Route::get('/shops/{slug}/models/{modelId}', [SeoController::class, 'model']);
});
