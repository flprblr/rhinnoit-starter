<?php

use App\Http\Controllers\Api\Passport\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Passport API Routes
|--------------------------------------------------------------------------
|
| API routes for external authentication using Laravel Passport (OAuth2).
| These routes are intended for external companies that connect.
|
*/

Route::prefix('passport')->name('passport.')->group(function () {
    // Public routes for authentication
    Route::post('/token', [AuthController::class, 'issueToken'])
        ->middleware('throttle:5,1')
        ->name('token');

    // Protected routes with token authentication
    Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::get('/verify', [AuthController::class, 'verify'])->name('verify');
        Route::post('/revoke', [AuthController::class, 'revokeToken'])->name('revoke');
        Route::post('/revoke-all', [AuthController::class, 'revokeAllTokens'])->name('revoke-all');
        Route::post('/revoke-others', [AuthController::class, 'revokeOthers'])->name('revoke-others');
        Route::post('/revoke-expired', [AuthController::class, 'revokeExpired'])->name('revoke-expired');
        Route::post('/revoke-refresh-token', [AuthController::class, 'revokeRefreshToken'])->name('revoke-refresh-token');
        Route::get('/tokens', [AuthController::class, 'listTokens'])->name('tokens');
        Route::get('/tokens/{id}', [AuthController::class, 'showToken'])->name('tokens.show');
        Route::delete('/tokens/{id}', [AuthController::class, 'revokeById'])->name('tokens.revoke-by-id');
    });
});
