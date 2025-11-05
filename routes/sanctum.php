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
    // Rutas públicas para autenticación
    Route::post('/token', [AuthController::class, 'issueToken'])
        ->middleware('throttle:5,1')
        ->name('token');

    // Rutas públicas para autenticación SPA (basada en sesión)
    Route::middleware('web')->group(function () {
        Route::get('/csrf-cookie', [AuthController::class, 'csrfCookie'])
            ->middleware('throttle:10,1')
            ->name('csrf-cookie');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login');
        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth:web')
            ->name('logout');
    });

    // Rutas protegidas con autenticación de token
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::get('/verify', [AuthController::class, 'verify'])->name('verify');
        Route::post('/revoke', [AuthController::class, 'revokeToken'])->name('revoke');
        Route::post('/revoke-all', [AuthController::class, 'revokeAllTokens'])->name('revoke-all');
        
        // Rutas específicas de tokens (deben ir antes de las rutas con parámetros)
        Route::post('/tokens/revoke-by-name', [AuthController::class, 'revokeByName'])->name('tokens.revoke-by-name');
        Route::post('/tokens/revoke-others', [AuthController::class, 'revokeOthers'])->name('tokens.revoke-others');
        Route::post('/tokens/revoke-expired', [AuthController::class, 'revokeExpired'])->name('tokens.revoke-expired');
        
        // Rutas de tokens con parámetros (deben ir después de las rutas específicas)
        Route::get('/tokens', [AuthController::class, 'listTokens'])->name('tokens');
        Route::get('/tokens/{id}', [AuthController::class, 'showToken'])->name('tokens.show');
        Route::patch('/tokens/{id}', [AuthController::class, 'updateToken'])->name('tokens.update');
        Route::delete('/tokens/{id}', [AuthController::class, 'revokeById'])->name('tokens.revoke-by-id');
    });
});
