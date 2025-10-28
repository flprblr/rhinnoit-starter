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
        Route::get('users/import', [UserController::class, 'importForm'])->name('users.import.form');
        Route::post('users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('users', UserController::class)
            ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);

        // Roles
        Route::get('roles/import', [RoleController::class, 'importForm'])->name('roles.import.form');
        Route::post('roles/import', [RoleController::class, 'import'])->name('roles.import');
        Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export');
        Route::resource('roles', RoleController::class)
            ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);

        // Permissions
        Route::get('permissions/import', [PermissionController::class, 'importForm'])->name('permissions.import.form');
        Route::post('permissions/import', [PermissionController::class, 'import'])->name('permissions.import');
        Route::get('permissions/export', [PermissionController::class, 'export'])->name('permissions.export');
        Route::resource('permissions', PermissionController::class)
            ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);
    });
