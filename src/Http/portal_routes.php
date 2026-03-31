<?php

use Illuminate\Support\Facades\Route;

Route::get('login', [\Marble\Admin\Http\Controllers\Portal\PortalAuthController::class, 'loginForm'])->name('login');
Route::post('login', [\Marble\Admin\Http\Controllers\Portal\PortalAuthController::class, 'login'])->name('login.submit');
Route::post('logout', [\Marble\Admin\Http\Controllers\Portal\PortalAuthController::class, 'logout'])->name('logout');
Route::get('register', [\Marble\Admin\Http\Controllers\Portal\PortalAuthController::class, 'registerForm'])->name('register');
Route::post('register', [\Marble\Admin\Http\Controllers\Portal\PortalAuthController::class, 'register'])->name('register.submit');
