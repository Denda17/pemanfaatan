<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetUnit;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckBookingDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bmd:check-booking-deadlines {--dry-run : Simulasi tanpa menyimpan perubahan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluasi batas waktu booking BMD 5 hari kerja (Pembatalan otomatis peminat belum lengkap, pelepasan aset kembali ke tersedia, dan seleksi beauty contest)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('=== PEMERIKSAAN BATAS WAKTU BOOKING BMD JABAR (PERMENDAGRI 7/2024) ===');
        $this->line('Waktu Evaluasi Sistem: '.$now->locale('id')->translatedFormat('l, d F Y H:i:s').' WIB');
        if ($isDryRun) {
            $this->warn('[MODE DRY-RUN AKTIF] Perubahan tidak akan disimpan ke database.');
        }

        // 1. Evaluasi Aset Reguler
        $expiredAssets = Asset::whereNotNull('booking_deadline_at')
            ->where('booking_deadline_at', '<', $now)
            ->get();

        $this->line("Ditemukan {$expiredAssets->count()} aset reguler yang telah melewati batas waktu.");

        foreach ($expiredAssets as $asset) {
            $this->line("Memeriksa Aset #{$asset->id}: {$asset->nama_objek} (Batas: {$asset->booking_deadline_at})");
            if (! $isDryRun) {
                Booking::evaluateDeadlineForAsset($asset);
                $asset->refresh();
                $this->info(" -> Status akhir aset: {$asset->status_ketersediaan}");
            }
        }

        // 2. Evaluasi Unit Aset (Multi-Unit Ruko/Kios)
        $expiredUnits = AssetUnit::whereNotNull('booking_deadline_at')
            ->where('booking_deadline_at', '<', $now)
            ->get();

        $this->line("Ditemukan {$expiredUnits->count()} unit ruko/kios yang telah melewati batas waktu.");

        foreach ($expiredUnits as $unit) {
            $this->line("Memeriksa Unit #{$unit->id}: Blok {$unit->nomor_unit} (Batas: {$unit->booking_deadline_at})");
            if (! $isDryRun) {
                Booking::evaluateDeadlineForUnit($unit);
                $unit->refresh();
                $this->info(" -> Status akhir unit: {$unit->status_ketersediaan}");
            }
        }

        // Ringkasan hasil
        $totalExpiredBookings = Booking::where('status', 'expired')->count();
        $totalBeautyContests = Booking::where('status', 'beauty_contest')->count();

        $this->newLine();
        $this->info('=== RINGKASAN EVALUASI BATAS WAKTU ===');
        $this->line("- Total Permohonan Gugur (Expired): {$totalExpiredBookings}");
        $this->line("- Total Permohonan Masuk Beauty Contest: {$totalBeautyContests}");

        return Command::SUCCESS;
    }
}
