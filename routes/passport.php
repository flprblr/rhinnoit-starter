<?php

use App\Http\Controllers\Api\Passport\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Passport API Routes
|--------------------------------------------------------------------------
|
| Rutas de API para autenticación externa usando Laravel Passport.
| Estas rutas están destinadas para empresas externas que se conectan.
|
*/

Route::prefix('passport')->name('passport.')->group(function () {
    // Rutas públicas
    Route::post('/token', [AuthController::class, 'issueToken'])->name('token');

    // Rutas protegidas
    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::post('/revoke', [AuthController::class, 'revokeToken'])->name('revoke');
        Route::post('/revoke-all', [AuthController::class, 'revokeAllTokens'])->name('revoke-all');
    });
});
