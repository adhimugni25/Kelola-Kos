<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as AdminDashboardController;
use App\Http\Controllers\SuperAdmin\KosController as AdminKosController;
use App\Http\Controllers\SuperAdmin\PengurusController as AdminPengurusController;
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
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/kos', [AdminKosController::class, 'index'])->name('kos.index');
    Route::get('/kos/create', [AdminKosController::class, 'create'])->name('kos.create');
    Route::post('/kos', [AdminKosController::class, 'store'])->name('kos.store');
    Route::get('/kos/{kos}/edit', [AdminKosController::class, 'edit'])->name('kos.edit');
    Route::put('/kos/{kos}', [AdminKosController::class, 'update'])->name('kos.update');
    Route::post('/kos/{kos}/toggle', [AdminKosController::class, 'toggle'])->name('kos.toggle');

    Route::get('/pengurus', [AdminPengurusController::class, 'index'])->name('pengurus.index');
    Route::get('/pengurus/create', [AdminPengurusController::class, 'create'])->name('pengurus.create');
    Route::post('/pengurus', [AdminPengurusController::class, 'store'])->name('pengurus.store');
    Route::post('/pengurus/{pengurus}/reset-password', [AdminPengurusController::class, 'reset'])->name('pengurus.reset');
    Route::post('/pengurus/{pengurus}/toggle', [AdminPengurusController::class, 'toggle'])->name('pengurus.toggle');
});
