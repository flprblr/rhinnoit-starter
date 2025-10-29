<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/sanctum.php';
require __DIR__.'/passport.php';

Route::prefix('sanctum')->group(function () {
    Route::post('/token', [\App\Http\Controllers\Api\Sanctum\AuthController::class, 'issueToken']);
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::prefix('passport')->group(function () {
    Route::post('/token', [\App\Http\Controllers\Api\Passport\AuthController::class, 'issueToken']);
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
});
