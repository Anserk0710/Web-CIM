<?php

use App\Http\Controllers\Admin\AdminShellController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/login', [AdminShellController::class, 'login'])
            ->name('login');

        Route::get('/', [AdminShellController::class, 'dashboard'])
            ->name('dashboard');
    });
