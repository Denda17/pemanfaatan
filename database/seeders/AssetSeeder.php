<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Import dan sinkronisasi 100% data resmi dari docs/data/Rekap Pemanfaatan BMD (terbaru).xlsx
        Artisan::call('bmd:import-excel', ['--clean' => true]);
        Artisan::call('bmd:generate-kasda-payments');
    }
}
