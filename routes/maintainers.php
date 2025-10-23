<?php

use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('maintainers')->as('maintainers.')->middleware('auth')->group(function () {
    Route::get('users/import', [\App\Http\Controllers\Maintainers\UserController::class, 'importForm'])->name('users.import.form');
    Route::post('users/import', [\App\Http\Controllers\Maintainers\UserController::class, 'import'])->name('users.import');
    Route::get('users/export', [\App\Http\Controllers\Maintainers\UserController::class, 'export'])->name('users.export');
    Route::resource('users', \App\Http\Controllers\Maintainers\UserController::class)
        ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);

    Route::get('roles/import', [\App\Http\Controllers\Maintainers\RoleController::class, 'importForm'])->name('roles.import.form');
    Route::post('roles/import', [\App\Http\Controllers\Maintainers\RoleController::class, 'import'])->name('roles.import');
    Route::get('roles/export', [\App\Http\Controllers\Maintainers\RoleController::class, 'export'])->name('roles.export');
    Route::resource('roles', \App\Http\Controllers\Maintainers\RoleController::class)
        ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);

    Route::get('permissions/import', [\App\Http\Controllers\Maintainers\PermissionController::class, 'importForm'])->name('permissions.import.form');
    Route::post('permissions/import', [\App\Http\Controllers\Maintainers\PermissionController::class, 'import'])->name('permissions.import');
    Route::get('permissions/export', [\App\Http\Controllers\Maintainers\PermissionController::class, 'export'])->name('permissions.export');
    Route::resource('permissions', \App\Http\Controllers\Maintainers\PermissionController::class)
        ->middlewareFor(['store', 'update'], HandlePrecognitiveRequests::class);
});
