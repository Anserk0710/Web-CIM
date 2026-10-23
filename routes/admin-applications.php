<?php

use App\Http\Controllers\Admin\CareerApplicationController;
use App\Http\Controllers\Admin\CareerApplicationCvController;
use App\Http\Controllers\Admin\CareerApplicationHandoverController;
use App\Http\Controllers\Admin\CareerApplicationWorkflowController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware([
        'auth',
        'active',
        'can:access-admin',
    ])
    ->group(function (): void {
        Route::get(
            '/applications',
            [
                CareerApplicationController::class,
                'index',
            ],
        )->name(
            'admin.applications.index',
        );

        Route::get(
            '/applications/{application}',
            [
                CareerApplicationController::class,
                'show',
            ],
        )->name(
            'admin.applications.show',
        );

        Route::post(
            '/applications/{application}/status',
            [
                CareerApplicationWorkflowController::class,
                'status',
            ],
        )->name(
            'admin.applications.status',
        );

        Route::post(
            '/applications/{application}/notes',
            [
                CareerApplicationWorkflowController::class,
                'note',
            ],
        )->name(
            'admin.applications.notes',
        );

        Route::get(
            '/applications/{application}/cv',
            CareerApplicationCvController::class,
        )->name(
            'admin.applications.cv',
        );

        Route::post(
            '/applications/{application}/handover',
            CareerApplicationHandoverController::class,
        )->name('admin.applications.handover');
    });
