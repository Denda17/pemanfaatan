<?php

use App\Http\Controllers\AssetCatalogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/katalog', [AssetCatalogController::class, 'index'])->name('catalog.index');
Route::get('/aset-siap-sewa', [AssetCatalogController::class, 'siapSewa'])->name('catalog.siap-sewa');
Route::get('/aset/{slug}', [AssetCatalogController::class, 'show'])->name('catalog.show');

Route::post('/aset/{slug}/booking', [BookingController::class, 'store'])->name('booking.store');

Route::get('/status-booking', [BookingController::class, 'tracker'])->name('booking.tracker');
Route::get('/status-booking/{kode}', [BookingController::class, 'showStatus'])->name('booking.status');

// Fallback login POST handler for Filament admin
Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
    $email = $request->input('email') ?? $request->input('data.email');
    $password = $request->input('password') ?? $request->input('data.password');
    $remember = (bool) ($request->input('remember') ?? $request->input('data.remember', false));

    if ($email && $password && \Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/admin');
    }

    return back()->withErrors([
        'data.email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
    ]);
});
