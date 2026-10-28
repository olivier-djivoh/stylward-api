<?php

use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware('crawler')->group(function () {
    Route::get('/shops/{slug}', [SeoController::class, 'shop']);
    Route::get('/shops/{slug}/models/{modelId}', [SeoController::class, 'model']);
});

Route::get('/run-seeder', function () {
    // Exécute le seeder en forçant l'opération (car on est en production)
    Artisan::call('db:seed', ['--force' => true]);

    return 'Le seeder a été exécuté avec succès !';
});
