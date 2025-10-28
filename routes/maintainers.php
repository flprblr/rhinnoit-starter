<?php

use App\Http\Controllers\Maintainers\PermissionController;
use App\Http\Controllers\Maintainers\RoleController;
use App\Http\Controllers\Maintainers\UserController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('maintainers')
    ->as('maintainers.')
    ->middleware('auth')
    ->group(function () {
        // Users
        Route::get('users/import', [UserController::class, 'importForm'])
            ->middleware('can:users.import')
            ->name('users.import.form');
        Route::post('users/import', [UserController::class, 'import'])
            ->middleware('can:users.import')
            ->name('users.import');
        Route::get('users/export', [UserController::class, 'export'])
            ->middleware('can:users.export')
            ->name('users.export');
        Route::resource('users', UserController::class)
            ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);

        // Roles
        Route::get('roles/import', [RoleController::class, 'importForm'])
            ->middleware('can:roles.import')
            ->name('roles.import.form');
        Route::post('roles/import', [RoleController::class, 'import'])
            ->middleware('can:roles.import')
            ->name('roles.import');
        Route::get('roles/export', [RoleController::class, 'export'])
            ->middleware('can:roles.export')
            ->name('roles.export');
        Route::resource('roles', RoleController::class)
            ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);

        // Permissions
        Route::get('permissions/import', [PermissionController::class, 'importForm'])
            ->middleware('can:permissions.import')
            ->name('permissions.import.form');
        Route::post('permissions/import', [PermissionController::class, 'import'])
            ->middleware('can:permissions.import')
            ->name('permissions.import');
        Route::get('permissions/export', [PermissionController::class, 'export'])
            ->middleware('can:permissions.export')
            ->name('permissions.export');
        Route::resource('permissions', PermissionController::class)
            ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);
    });
