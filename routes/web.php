<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Home - Redirect to Dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Additional Route Files
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/maintainers.php';
