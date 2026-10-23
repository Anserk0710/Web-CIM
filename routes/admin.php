<?php

use App\Http\Controllers\Admin\ActivityCategoryController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AdminShellController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Auth\ChangePasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\EcatalogController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\InquiryWorkflowController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NotificationOutboxController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductPreviewController;
use App\Http\Controllers\Admin\ProductPublicationController;
use App\Http\Controllers\Admin\ProductTaxonomyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SettingsController;
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
        'can:access-admin',
    ])
    ->group(function (): void {
        Route::get(
            '/',
            [
                AdminShellController::class,
                'dashboard',
            ],
        )->name('admin.dashboard');

        Route::get(
            '/change-password',
            [
                ChangePasswordController::class,
                'edit',
            ],
        )->name('admin.password.edit');

        Route::put(
            '/change-password',
            [
                ChangePasswordController::class,
                'update',
            ],
        )->name('admin.password.update');

        Route::get(
            '/products',
            [
                ProductController::class,
                'index',
            ],
        )->name('admin.products.index');

        Route::get(
            '/products/create',
            [
                ProductController::class,
                'create',
            ],
        )->name('admin.products.create');

        Route::post(
            '/products',
            [
                ProductController::class,
                'store',
            ],
        )->name('admin.products.store');

        Route::get(
            '/products/{product}/edit',
            [
                ProductController::class,
                'edit',
            ],
        )->name('admin.products.edit');

        Route::put(
            '/products/{product}',
            [
                ProductController::class,
                'update',
            ],
        )->name('admin.products.update');

        Route::post(
            '/products/{product}/publish',
            [
                ProductPublicationController::class,
                'publish',
            ],
        )->name('admin.products.publish');

        Route::post(
            '/products/{product}/unpublish',
            [
                ProductPublicationController::class,
                'unpublish',
            ],
        )->name('admin.products.unpublish');

        Route::post(
            '/products/{product}/archive',
            [
                ProductPublicationController::class,
                'archive',
            ],
        )->name('admin.products.archive');

        Route::get(
            '/products/{product}/preview',
            ProductPreviewController::class,
        )->name('admin.products.preview');

        Route::get(
            '/ecatalog',
            [
                EcatalogController::class,
                'index',
            ],
        )->name('admin.ecatalog.index');

        Route::get(
            '/ecatalog/create',
            [
                EcatalogController::class,
                'create',
            ],
        )->name('admin.ecatalog.create');

        Route::post(
            '/ecatalog',
            [
                EcatalogController::class,
                'store',
            ],
        )->name('admin.ecatalog.store');

        Route::get(
            '/ecatalog/{ecatalog}/edit',
            [
                EcatalogController::class,
                'edit',
            ],
        )->name('admin.ecatalog.edit');

        Route::put(
            '/ecatalog/{ecatalog}',
            [
                EcatalogController::class,
                'update',
            ],
        )->name('admin.ecatalog.update');

        Route::post(
            '/ecatalog/{ecatalog}/publish',
            [
                EcatalogController::class,
                'publish',
            ],
        )->name('admin.ecatalog.publish');

        Route::post(
            '/ecatalog/{ecatalog}/archive',
            [
                EcatalogController::class,
                'archive',
            ],
        )->name('admin.ecatalog.archive');

        Route::delete(
            '/ecatalog/{ecatalog}',
            [
                EcatalogController::class,
                'destroy',
            ],
        )->name('admin.ecatalog.destroy');

        Route::get(
            '/activities',
            [
                ActivityController::class,
                'index',
            ],
        )->name('admin.activities.index');

        Route::get(
            '/activities/categories',
            [
                ActivityCategoryController::class,
                'index',
            ],
        )->name(
            'admin.activities.categories.index',
        );

        Route::post(
            '/activities/categories',
            [
                ActivityCategoryController::class,
                'store',
            ],
        )->name(
            'admin.activities.categories.store',
        );

        Route::patch(
            '/activities/categories/{category}',
            [
                ActivityCategoryController::class,
                'update',
            ],
        )->name(
            'admin.activities.categories.update',
        );

        Route::get(
            '/activities/create',
            [
                ActivityController::class,
                'create',
            ],
        )->name('admin.activities.create');

        Route::post(
            '/activities',
            [
                ActivityController::class,
                'store',
            ],
        )->name('admin.activities.store');

        Route::get(
            '/activities/{activity}/edit',
            [
                ActivityController::class,
                'edit',
            ],
        )->name('admin.activities.edit');

        Route::put(
            '/activities/{activity}',
            [
                ActivityController::class,
                'update',
            ],
        )->name('admin.activities.update');

        Route::post(
            '/activities/{activity}/publish',
            [
                ActivityController::class,
                'publish',
            ],
        )->name('admin.activities.publish');

        Route::post(
            '/activities/{activity}/archive',
            [
                ActivityController::class,
                'archive',
            ],
        )->name('admin.activities.archive');

        Route::get(
            '/careers',
            [
                CareerController::class,
                'index',
            ],
        )->name('admin.careers.index');

        Route::get(
            '/careers/create',
            [
                CareerController::class,
                'create',
            ],
        )->name('admin.careers.create');

        Route::post(
            '/careers',
            [
                CareerController::class,
                'store',
            ],
        )->name('admin.careers.store');

        Route::get(
            '/careers/{career}/edit',
            [
                CareerController::class,
                'edit',
            ],
        )->name('admin.careers.edit');

        Route::put(
            '/careers/{career}',
            [
                CareerController::class,
                'update',
            ],
        )->name('admin.careers.update');

        Route::post(
            '/careers/{career}/open',
            [
                CareerController::class,
                'open',
            ],
        )->name('admin.careers.open');

        Route::post(
            '/careers/{career}/close',
            [
                CareerController::class,
                'close',
            ],
        )->name('admin.careers.close');

        Route::post(
            '/careers/{career}/archive',
            [
                CareerController::class,
                'archive',
            ],
        )->name('admin.careers.archive');

        Route::get(
            '/taxonomies',
            [
                ProductTaxonomyController::class,
                'index',
            ],
        )->name('admin.taxonomies.index');

        Route::post(
            '/taxonomies/{kind}',
            [
                ProductTaxonomyController::class,
                'store',
            ],
        )->name('admin.taxonomies.store');

        Route::patch(
            '/taxonomies/{kind}/{id}',
            [
                ProductTaxonomyController::class,
                'update',
            ],
        )->name('admin.taxonomies.update');

        Route::get(
            '/media',
            [
                MediaController::class,
                'index',
            ],
        )->name('admin.media.index');

        Route::post(
            '/media',
            [
                MediaController::class,
                'store',
            ],
        )->name('admin.media.store');

        Route::put(
            '/media/{media}',
            [
                MediaController::class,
                'update',
            ],
        )->name('admin.media.update');

        Route::delete(
            '/media/{media}',
            [
                MediaController::class,
                'destroy',
            ],
        )->name('admin.media.destroy');

        Route::get(
            '/users',
            [
                UserController::class,
                'index',
            ],
        )->name('admin.users.index');

        Route::post(
            '/users',
            [
                UserController::class,
                'store',
            ],
        )->name('admin.users.store');

        Route::put(
            '/users/{user}',
            [
                UserController::class,
                'update',
            ],
        )->name('admin.users.update');

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

        Route::post(
            '/roles',
            [
                RoleController::class,
                'store',
            ],
        )->name('admin.roles.store');

        Route::put(
            '/roles/{role}',
            [
                RoleController::class,
                'update',
            ],
        )->name('admin.roles.update');

        Route::put(
            '/roles/{role}/permissions',
            [
                RolePermissionController::class,
                'update',
            ],
        )->name(
            'admin.roles.permissions',
        );

        Route::get(
            '/settings',
            [
                SettingsController::class,
                'index',
            ],
        )->name('admin.settings.index');

        Route::put(
            '/settings',
            [
                SettingsController::class,
                'update',
            ],
        )->name('admin.settings.update');

        Route::post(
            '/settings/offices',
            [
                SettingsController::class,
                'storeOffice',
            ],
        )->name(
            'admin.settings.offices.store',
        );

        Route::put(
            '/settings/offices/{office}',
            [
                SettingsController::class,
                'updateOffice',
            ],
        )->name(
            'admin.settings.offices.update',
        );

        Route::get(
            '/settings/pages/{page}',
            [
                PageContentController::class,
                'edit',
            ],
        )->name(
            'admin.settings.pages.edit',
        );

        Route::put(
            '/settings/pages/{page}',
            [
                PageContentController::class,
                'update',
            ],
        )->name(
            'admin.settings.pages.update',
        );

        Route::get(
            '/audit',
            [
                AuditLogController::class,
                'index',
            ],
        )->name('admin.audit.index');

        Route::get(
            '/audit/{audit}',
            [
                AuditLogController::class,
                'show',
            ],
        )->name('admin.audit.show');

        Route::get(
            '/inquiries/export',
            [InquiryController::class, 'export'],
        )->name('admin.inquiries.export');

        Route::get(
            '/inquiries',
            [InquiryController::class, 'index'],
        )->name('admin.inquiries.index');

        Route::get(
            '/inquiries/{inquiry}',
            [InquiryController::class, 'show'],
        )->name('admin.inquiries.show');

        Route::post(
            '/inquiries/{inquiry}/status',
            [InquiryWorkflowController::class, 'status'],
        )->name('admin.inquiries.status');

        Route::post(
            '/inquiries/{inquiry}/notes',
            [InquiryWorkflowController::class, 'note'],
        )->name('admin.inquiries.notes');

        Route::get(
            '/operations/notifications',
            [NotificationOutboxController::class, 'index'],
        )->name('admin.operations.notifications.index');

        Route::post(
            '/operations/notifications/{notification}/retry',
            [NotificationOutboxController::class, 'retry'],
        )->name('admin.operations.notifications.retry');
    });

require __DIR__.'/admin-applications.php';
