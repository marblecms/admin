<?php

use Illuminate\Support\Facades\Route;

Route::get('login', [\Marble\Admin\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('login', [\Marble\Admin\Http\Controllers\Auth\LoginController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.submit');

Route::post('logout', [\Marble\Admin\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('logout');

Route::get('two-factor', [\Marble\Admin\Http\Controllers\Auth\TwoFactorController::class, 'showChallenge'])
    ->name('two-factor');

Route::post('two-factor', [\Marble\Admin\Http\Controllers\Auth\TwoFactorController::class, 'verify'])
    ->middleware('throttle:10,1')
    ->name('two-factor.verify');
