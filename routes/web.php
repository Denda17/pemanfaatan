<?php

use App\Http\Controllers\AssetCatalogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegulasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Autentikasi Pemohon / Calon Mitra
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register'])->name('register.submit');
Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->name('login.submit');
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

Route::get('/katalog', [AssetCatalogController::class, 'index'])->name('catalog.index');
Route::get('/aset-siap-sewa', [AssetCatalogController::class, 'siapSewa'])->name('catalog.siap-sewa');
Route::get('/aset/{slug}', [AssetCatalogController::class, 'show'])->name('catalog.show');

Route::get('/dasar-hukum', [RegulasiController::class, 'index'])->name('regulasi.index');
Route::get('/dasar-hukum/preview/{slug}', [RegulasiController::class, 'preview'])->name('regulasi.preview');
Route::get('/dasar-hukum/unduh/{slug}', [RegulasiController::class, 'download'])->name('regulasi.download');

Route::post('/aset/{slug}/booking', [BookingController::class, 'store'])
    ->middleware('auth')
    ->name('booking.store');

Route::get('/pengajuan-saya', [BookingController::class, 'myBookings'])
    ->middleware('auth')
    ->name('booking.my');

Route::get('/status-booking', [BookingController::class, 'tracker'])->name('booking.tracker');
Route::get('/status-booking/{kode}', [BookingController::class, 'showStatus'])
    ->middleware('auth')
    ->name('booking.status');

Route::get('/status-booking/{kode}/surat-permohonan', [BookingController::class, 'downloadSuratPermohonan'])
    ->middleware('auth')
    ->name('booking.surat-permohonan');

Route::post('/status-booking/{kode}/upload-dokumen', [BookingController::class, 'uploadDocument'])
    ->middleware('auth')
    ->name('booking.upload-dokumen');

// Pengalihan Login Admin ke Portal Login Terpadu
Route::get('/admin/login', function () {
    return redirect()->route('login');
})->name('filament.admin.auth.login');

Route::post('/admin/login', [AuthController::class, 'login']);
