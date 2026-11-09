<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringLeases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bmd:check-expiring-leases {--dry-run : Simulasi tanpa menyimpan perubahan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa masa berlaku sewa BMD sesuai Permendagri No. 19/2016 (Peringatan H-6 Bulan & Pembebasan Status Aset Kedaluwarsa)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("=== PEMERIKSAAN JATUH TEMPO SEWA BMD JABAR (PERMENDAGRI 19/2016) ===");
        $this->line("Tanggal Evaluasi Sistem: " . $today->translatedFormat('d F Y'));
        if ($isDryRun) {
            $this->warn("[MODE DRY-RUN AKTIF] Perubahan tidak akan disimpan ke database.");
        }

        $records = AssetPemanfaatan::with('asset')
            ->whereNotNull('tanggal_akhir')
            ->where('status_perpanjangan', '!=', 'diperpanjang')
            ->get();

        $countWarning = 0;
        $countExpired = 0;
        $countFreedAssets = 0;
        $countActive = 0;

        foreach ($records as $item) {
            $endDate = Carbon::parse($item->tanggal_akhir)->startOfDay();
            $hMinus6Months = $endDate->copy()->subMonths(6)->startOfDay();

            // Kasus 1: Kontrak sudah melewati tanggal akhir (Kedaluwarsa / Selesai)
            if ($today->gt($endDate)) {
                if ($item->status_perpanjangan !== 'selesai') {
                    $this->warn("[-] Kontrak Selesai: ID #{$item->id} ({$item->nama_mitra}) berakhir pada {$endDate->format('d/m/Y')}");
                    if (!$isDryRun) {
                        $item->update(['status_perpanjangan' => 'selesai']);
                    }
                    $countExpired++;

                    // Periksa apakah aset perlu dikembalikan statusnya ke 'tersedia'
                    $activeLeasesCount = AssetPemanfaatan::where('asset_id', $item->asset_id)
                        ->where('id', '!=', $item->id)
                        ->where('status_perpanjangan', '!=', 'selesai')
                        ->where('tanggal_akhir', '>=', $today)
                        ->count();

                    if ($activeLeasesCount === 0 && $item->asset && $item->asset->status_ketersediaan !== 'tersedia') {
                        $this->info("    -> Mengembalikan aset '{$item->asset->nama_objek}' ke status TERSEDIA.");
                        if (!$isDryRun) {
                            $item->asset->update(['status_ketersediaan' => 'tersedia']);
                        }
                        $countFreedAssets++;
                    }
                }
            }
            // Kasus 2: Kontrak berada dalam jendela H-6 Bulan sebelum jatuh tempo (Pasal 100 Permendagri 19/2016)
            elseif ($today->gte($hMinus6Months) && $today->lte($endDate)) {
                $daysRemaining = $today->diffInDays($endDate, false);
                if ($item->status_perpanjangan !== 'peringatan_jatuh_tempo') {
                    $this->comment("[!] Peringatan Dini (H-{$daysRemaining} hari): ID #{$item->id} ({$item->nama_mitra}) batas jatuh tempo {$endDate->format('d/m/Y')}");
                    if (!$isDryRun) {
                        $item->update(['status_perpanjangan' => 'peringatan_jatuh_tempo']);
                    }
                }
                $countWarning++;
            }
            // Kasus 3: Kontrak masih aman (> 6 bulan)
            else {
                if ($item->status_perpanjangan === 'peringatan_jatuh_tempo') {
                    if (!$isDryRun) {
                        $item->update(['status_perpanjangan' => 'aktif']);
                    }
                }
                $countActive++;
            }
        }

        $this->newLine();
        $this->table(
            ['Metrik Evaluasi', 'Jumlah'],
            [
                ['Masa Sewa Aktif Aman (> 6 bulan)', $countActive],
                ['Peringatan Dini Jatuh Tempo (H-6 bulan)', $countWarning],
                ['Kontrak Berakhir / Kedaluwarsa', $countExpired],
                ['Aset BMD Dikembalikan ke Status Siap Sewa', $countFreedAssets],
            ]
        );

        $this->info("Pemeriksaan jatuh tempo sewa BMD berhasil dijalankan.");
        return Command::SUCCESS;
    }
}
