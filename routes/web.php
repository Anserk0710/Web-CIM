<?php

use App\Http\Controllers\Health\ReadinessController;
use App\Http\Controllers\PublicSite\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)
    ->name('home');

Route::get('/ready', ReadinessController::class)
    ->name('health.ready');

require __DIR__.'/admin.php';
