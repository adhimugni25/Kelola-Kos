<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'role:pengurus'])->prefix('pengurus')->name('pengurus.')->group(function (): void {
    Route::get('/dashboard', fn () => view('pengurus.dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:penghuni'])->prefix('penghuni')->name('penghuni.')->group(function (): void {
    Route::get('/dashboard', fn () => view('penghuni.dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', fn () => view('superadmin.dashboard'))->name('dashboard');
});
