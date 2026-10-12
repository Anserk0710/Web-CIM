<?php

use App\Http\Controllers\Health\ReadinessController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\ProductController;
use App\Http\Controllers\PublicSite\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    HomeController::class,
)->name('home');

Route::get(
    '/ready',
    ReadinessController::class,
)->name('health.ready');

Route::get(
    '/products',
    [ProductController::class, 'index'],
)->name('products.index');

Route::get(
    '/products/{slug}',
    [ProductController::class, 'show'],
)->name('products.show');

Route::get(
    '/sitemap.xml',
    SitemapController::class,
)->name('sitemap');

require __DIR__.'/admin.php';
