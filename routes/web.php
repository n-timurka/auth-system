<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});


Route::middleware('guest')->group(function () {
    Route::get('/signup', [AuthController::class, 'create'])->name('register');
    Route::post('/signup', [AuthController::class, 'store']);
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('App\Http\Middleware\CheckPermission:users.view')
        ->name('users.index');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('App\Http\Middleware\CheckPermission:users.delete')
        ->name('users.destroy');

    Route::put('/users/{user}/restore', [UserController::class, 'restore'])
        ->middleware('App\Http\Middleware\CheckPermission:users.restore')
        ->name('users.restore');

    Route::get('/settings', [SettingController::class, 'index'])
        ->middleware('App\Http\Middleware\CheckPermission:settings.view')
        ->name('settings.index');

    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware('App\Http\Middleware\CheckPermission:settings.edit')
        ->name('settings.update');

    // Email Verification Routes
    Route::get('/email/verify/{id}/{hash}', function (Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Google Integration
    Route::get('/channels', [GoogleController::class, 'index'])->name('channels.index');
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});