<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleController::class, 'redirect'])
        ->name('auth.google');

    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'callback'])
        ->name('auth.google.callback');
});
