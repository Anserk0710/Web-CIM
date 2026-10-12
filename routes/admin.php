<?php

use App\Http\Controllers\Admin\AdminShellController;
use App\Http\Controllers\Admin\Auth\ChangePasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware('guest')
    ->group(function (): void {
        Route::get(
            '/login',
            [LoginController::class, 'create'],
        )->name('login');

        Route::post(
            '/login',
            [LoginController::class, 'store'],
        )->name('login.store');

        Route::get(
            '/forgot-password',
            [ForgotPasswordController::class, 'create'],
        )->name('password.request');

        Route::post(
            '/forgot-password',
            [ForgotPasswordController::class, 'store'],
        )
            ->middleware('throttle:5,1')
            ->name('password.email');

        Route::get(
            '/reset-password/{token}',
            [ResetPasswordController::class, 'create'],
        )->name('password.reset');

        Route::post(
            '/reset-password',
            [ResetPasswordController::class, 'store'],
        )->name('password.update');
    });

Route::post(
    '/admin/logout',
    [LoginController::class, 'destroy'],
)
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')
    ->middleware([
        'auth',
        'active',
    ])
    ->group(function (): void {
        Route::get(
            '/change-password',
            [ChangePasswordController::class, 'edit'],
        )->name('admin.password.edit');

        Route::put(
            '/change-password',
            [ChangePasswordController::class, 'update'],
        )->name('admin.password.update');

        Route::middleware(
            'can:access-admin',
        )->group(function (): void {
            Route::get(
                '/',
                [
                    AdminShellController::class,
                    'dashboard',
                ],
            )->name('admin.dashboard');

            Route::get(
                '/users',
                [
                    UserController::class,
                    'index',
                ],
            )->name('admin.users.index');

            Route::patch(
                '/users/{user}/active',
                [
                    UserAccessController::class,
                    'update',
                ],
            )->name('admin.users.active');

            Route::put(
                '/users/{user}/roles',
                [
                    UserRoleController::class,
                    'update',
                ],
            )->name('admin.users.roles');

            Route::get(
                '/roles',
                [
                    RoleController::class,
                    'index',
                ],
            )->name('admin.roles.index');

            Route::put(
                '/roles/{role}/permissions',
                [
                    RolePermissionController::class,
                    'update',
                ],
            )->name(
                'admin.roles.permissions',
            );
        });
    });
