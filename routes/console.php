<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks (Pengingat Jatuh Tempo BMD & Pembebasan Aset Otomatis)
|--------------------------------------------------------------------------
| Sesuai Permendagri No. 19 Tahun 2016 (Pasal 100), sistem secara harian
| mengevaluasi jatuh tempo kontrak sewa H-6 bulan dan mengembalikan aset
| yang masa sewanya telah berakhir ke status 'tersedia'.
*/
Schedule::command('bmd:check-expiring-leases')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/bmd-expiring-leases.log'));
