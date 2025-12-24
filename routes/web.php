<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/signup', [AuthController::class, 'create'])->name('register');
    Route::post('/signup', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'user' => auth()->user()
        ]);
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});