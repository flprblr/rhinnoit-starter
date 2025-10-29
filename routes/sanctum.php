<?php

use App\Http\Controllers\Api\Sanctum\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sanctum API Routes
|--------------------------------------------------------------------------
|
| Rutas de API para autenticación interna usando Laravel Sanctum.
| Estas rutas están destinadas para uso interno dentro de la empresa.
|
*/

Route::prefix('sanctum')->name('sanctum.')->group(function () {
    // Rutas públicas
    Route::post('/token', [AuthController::class, 'issueToken'])->name('token');

    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::post('/revoke', [AuthController::class, 'revokeToken'])->name('revoke');
        Route::post('/revoke-all', [AuthController::class, 'revokeAllTokens'])->name('revoke-all');
        Route::get('/tokens', [AuthController::class, 'listTokens'])->name('tokens');
        Route::delete('/tokens/{id}', [AuthController::class, 'revokeById'])->name('tokens.revoke-by-id');
        Route::post('/tokens/revoke-by-name', [AuthController::class, 'revokeByName'])->name('tokens.revoke-by-name');
        Route::post('/tokens/revoke-others', [AuthController::class, 'revokeOthers'])->name('tokens.revoke-others');
        Route::post('/tokens/revoke-expired', [AuthController::class, 'revokeExpired'])->name('tokens.revoke-expired');
    });
});
