<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PembayaranController as PembayaranBuktiController;
use App\Http\Controllers\Penghuni\PemesananController as PenghuniPemesananController;
use App\Http\Controllers\Penghuni\TagihanController as PenghuniTagihanController;
use App\Http\Controllers\Pengurus\DashboardController as PengurusDashboardController;
use App\Http\Controllers\Pengurus\ImportController as PengurusImportController;
use App\Http\Controllers\Pengurus\KamarController;
use App\Http\Controllers\Pengurus\LaporanController;
use App\Http\Controllers\Pengurus\MetodePembayaranController;
use App\Http\Controllers\Pengurus\PembayaranController as PengurusPembayaranController;
use App\Http\Controllers\Pengurus\PemesananController as PengurusPemesananController;
use App\Http\Controllers\Pengurus\TagihanController as PengurusTagihanController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\KosPublicController;
use App\Http\Controllers\SuperAdmin\DashboardController as AdminDashboardController;
use App\Http\Controllers\SuperAdmin\KosController as AdminKosController;
use App\Http\Controllers\SuperAdmin\PengurusController as AdminPengurusController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/k/{slug}', [KosPublicController::class, 'show'])->name('public.kos');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/pembayaran/{pembayaran}/bukti', [PembayaranBuktiController::class, 'bukti'])
    ->middleware(['auth', 'throttle:upload'])
    ->name('pembayaran.bukti');

Route::middleware(['auth', 'role:pengurus'])->prefix('pengurus')->name('pengurus.')->group(function (): void {
    Route::get('/dashboard', [PengurusDashboardController::class, 'index'])->name('dashboard');
    Route::resource('kamar', KamarController::class)->except('show');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/csv', [LaporanController::class, 'csv'])->name('laporan.csv');
    Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');

    Route::get('/impor', [PengurusImportController::class, 'index'])->name('impor.index');
    Route::get('/impor/kamar/template', [PengurusImportController::class, 'templateKamar'])->name('impor.kamar.template');
    Route::post('/impor/kamar', [PengurusImportController::class, 'imporKamar'])
        ->middleware('throttle:import')
        ->name('impor.kamar');
    Route::get('/impor/penghuni/template', [PengurusImportController::class, 'templatePenghuni'])->name('impor.penghuni.template');
    Route::post('/impor/penghuni', [PengurusImportController::class, 'imporPenghuni'])
        ->middleware('throttle:import')
        ->name('impor.penghuni');

    Route::get('/pemesanan', [PengurusPemesananController::class, 'index'])->name('pemesanan.index');
    Route::get('/pemesanan/{pemesanan}', [PengurusPemesananController::class, 'show'])->name('pemesanan.show');
    Route::post('/pemesanan/{pemesanan}/setujui', [PengurusPemesananController::class, 'setujui'])->name('pemesanan.setujui');
    Route::post('/pemesanan/{pemesanan}/tolak', [PengurusPemesananController::class, 'tolak'])->name('pemesanan.tolak');
    Route::post('/pemesanan/{pemesanan}/selesai', [PengurusPemesananController::class, 'selesai'])->name('pemesanan.selesai');

    Route::get('/tagihan', [PengurusTagihanController::class, 'index'])->name('tagihan.index');
    Route::get('/tagihan/create', [PengurusTagihanController::class, 'create'])->name('tagihan.create');
    Route::post('/tagihan', [PengurusTagihanController::class, 'store'])->name('tagihan.store');
    Route::post('/tagihan/bulan-ini', [PengurusTagihanController::class, 'bulanIni'])->name('tagihan.bulan-ini');
    Route::get('/tagihan/{tagihan}/edit', [PengurusTagihanController::class, 'edit'])->name('tagihan.edit');
    Route::put('/tagihan/{tagihan}', [PengurusTagihanController::class, 'update'])->name('tagihan.update');
    Route::post('/tagihan/{tagihan}/batal', [PengurusTagihanController::class, 'batal'])->name('tagihan.batal');

    Route::get('/pembayaran', [PengurusPembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/{pembayaran}/terima', [PengurusPembayaranController::class, 'terima'])->name('pembayaran.terima');
    Route::post('/pembayaran/{pembayaran}/tolak', [PengurusPembayaranController::class, 'tolak'])->name('pembayaran.tolak');

    Route::get('/metode-pembayaran', [MetodePembayaranController::class, 'index'])->name('metode-pembayaran.index');
    Route::post('/metode-pembayaran', [MetodePembayaranController::class, 'store'])->name('metode-pembayaran.store');
    Route::post('/metode-pembayaran/{metodePembayaran}/toggle', [MetodePembayaranController::class, 'toggle'])->name('metode-pembayaran.toggle');
    Route::delete('/metode-pembayaran/{metodePembayaran}', [MetodePembayaranController::class, 'destroy'])->name('metode-pembayaran.destroy');
});

Route::middleware(['auth', 'role:penghuni'])->prefix('penghuni')->name('penghuni.')->group(function (): void {
    Route::get('/dashboard', fn () => view('penghuni.dashboard'))->name('dashboard');
    Route::get('/pemesanan', [PenghuniPemesananController::class, 'index'])->name('pemesanan.index');
    Route::get('/pemesanan/create', [PenghuniPemesananController::class, 'create'])->name('pemesanan.create');
    Route::post('/pemesanan', [PenghuniPemesananController::class, 'store'])->name('pemesanan.store');
    Route::post('/pemesanan/{pemesanan}/batal', [PenghuniPemesananController::class, 'batal'])->name('pemesanan.batal');

    Route::get('/tagihan', [PenghuniTagihanController::class, 'index'])->name('tagihan.index');
    Route::get('/tagihan/{tagihan}', [PenghuniTagihanController::class, 'show'])->name('tagihan.show');
    Route::post('/tagihan/{tagihan}/bayar', [PenghuniTagihanController::class, 'bayar'])
        ->middleware('throttle:upload')
        ->name('tagihan.bayar');
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
