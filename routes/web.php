<?php

use App\Http\Controllers\Health\ReadinessController;
use App\Http\Controllers\PublicSite\ActivityController;
use App\Http\Controllers\PublicSite\CareerApplicationController;
use App\Http\Controllers\PublicSite\CareerController;
use App\Http\Controllers\PublicSite\CompanyController;
use App\Http\Controllers\PublicSite\ContactController;
use App\Http\Controllers\PublicSite\EcatalogPublicController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\InquirySubmissionController;
use App\Http\Controllers\PublicSite\PrivacyController;
use App\Http\Controllers\PublicSite\ProductController;
use App\Http\Controllers\PublicSite\RobotsController;
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
    '/company',
    CompanyController::class,
)->name('company');

Route::get(
    '/products',
    [ProductController::class, 'index'],
)->name('products.index');

Route::get(
    '/products/{slug}',
    [ProductController::class, 'show'],
)->name('products.show');

Route::get(
    '/e-catalog',
    [EcatalogPublicController::class, 'index'],
)->name('ecatalog.public.index');

Route::get(
    '/activities',
    [ActivityController::class, 'index'],
)->name('activities.index');

Route::get(
    '/activities/{slug}',
    [ActivityController::class, 'show'],
)->name('activities.show');

Route::get(
    '/careers',
    [CareerController::class, 'index'],
)->name('careers.index');

Route::get(
    '/careers/{slug}',
    [CareerController::class, 'show'],
)->name('careers.show');

Route::post(
    '/careers/{slug}/apply',
    [CareerApplicationController::class, 'store'],
)->middleware('throttle:5,60')->name('careers.apply');

Route::get(
    '/contact',
    [ContactController::class, 'show'],
)->name('contact');

Route::post(
    '/contact/inquiries',
    [InquirySubmissionController::class, 'store'],
)
    ->middleware('throttle:5,10')
    ->name('contact.inquiries.store');

Route::get(
    '/privacy',
    PrivacyController::class,
)->name('privacy');

Route::get(
    '/sitemap.xml',
    SitemapController::class,
)->name('sitemap');

Route::get(
    '/robots.txt',
    RobotsController::class,
)->name('robots');

require __DIR__.'/admin.php';
