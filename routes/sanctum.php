<?php

use App\Http\Controllers\Api\Sanctum\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sanctum API Routes
|--------------------------------------------------------------------------
|
| API routes for internal authentication using Laravel Sanctum.
| These routes are intended for internal use within the company.
|
*/

Route::prefix('sanctum')->name('sanctum.')->group(function () {
    // Public routes for authentication
    Route::post('/token', [AuthController::class, 'issueToken'])
        ->middleware('throttle:5,1')
        ->name('token');

    // Public routes for SPA authentication (session-based)
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

    // Protected routes with token authentication
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::get('/verify', [AuthController::class, 'verify'])->name('verify');
        Route::post('/revoke', [AuthController::class, 'revokeToken'])->name('revoke');
        Route::post('/revoke-all', [AuthController::class, 'revokeAllTokens'])->name('revoke-all');

        // Specific token routes (must go before routes with parameters)
        Route::post('/tokens/revoke-by-name', [AuthController::class, 'revokeByName'])->name('tokens.revoke-by-name');
        Route::post('/tokens/revoke-others', [AuthController::class, 'revokeOthers'])->name('tokens.revoke-others');
        Route::post('/tokens/revoke-expired', [AuthController::class, 'revokeExpired'])->name('tokens.revoke-expired');

        // Token routes with parameters (must go after specific routes)
        Route::get('/tokens', [AuthController::class, 'listTokens'])->name('tokens');
        Route::get('/tokens/{id}', [AuthController::class, 'showToken'])->name('tokens.show');
        Route::patch('/tokens/{id}', [AuthController::class, 'updateToken'])->name('tokens.update');
        Route::delete('/tokens/{id}', [AuthController::class, 'revokeById'])->name('tokens.revoke-by-id');
    });
});
