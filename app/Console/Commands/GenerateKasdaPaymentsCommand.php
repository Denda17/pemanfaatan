<?php

namespace App\Console\Commands;

use App\Models\AssetPemanfaatan;
use App\Models\AssetPemanfaatanPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateKasdaPaymentsCommand extends Command
{
    protected $signature = 'bmd:generate-kasda-payments';
    protected $description = 'Menghasilkan termin setoran Kasda (STS / NTPN) untuk kontrak sewa BMD aktif';

    public function handle(): int
    {
        $this->info("=== GENERATE TERMIN PEMBAYARAN KASDA SEWA BMD ===");

        $adminUser = User::first();
        $adminId = $adminUser?->id;

        $activeLeases = AssetPemanfaatan::where('kategori_status', 'sewa_aktif')
            ->whereNotNull('besaran_sewa_tahunan')
            ->get();

        $countCreated = 0;

        foreach ($activeLeases as $lease) {
            // Hindari duplikasi jika sudah memiliki pembayaran
            if ($lease->payments()->count() > 0) {
                continue;
            }

            $jangkaWaktu = $lease->jangka_waktu_tahun ?: 5;
            $besaranTahunan = (float) $lease->besaran_sewa_tahunan;
            $startDate = $lease->tanggal_awal ? Carbon::parse($lease->tanggal_awal) : Carbon::now()->subYears(1);

            for ($termin = 1; $termin <= $jangkaWaktu; $termin++) {
                $periodeTahun = $startDate->copy()->addYears($termin - 1);
                $jatuhTempo = $periodeTahun->copy()->addDays(30);

                // Jika periode tahun sudah lewat atau tahun ini, tandai lunas
                if ($jatuhTempo->lte(Carbon::today())) {
                    AssetPemanfaatanPayment::create([
                        'asset_pemanfaatan_id' => $lease->id,
                        'termin_ke' => $termin,
                        'tahun_ke' => 'Tahun ' . $termin . ' (' . $periodeTahun->year . ')',
                        'nominal_kewajiban' => $besaranTahunan,
                        'nominal_disetor' => $besaranTahunan,
                        'tanggal_jatuh_tempo' => $jatuhTempo,
                        'tanggal_bayar' => $jatuhTempo->copy()->subDays(rand(2, 10)),
                        'nomor_sts' => 'STS-' . $periodeTahun->year . '/BPKAD-BMD/' . str_pad($lease->id . $termin, 4, '0', STR_PAD_LEFT),
                        'status_pembayaran' => 'lunas',
                        'catatan' => 'Setoran retribusi sewa diterima di Kasda Jabar via Bank BJB.',
                        'verified_by' => $adminId,
                    ]);
                    $countCreated++;
                } else {
                    // Termin mendatang (menunggu pembayaran)
                    AssetPemanfaatanPayment::create([
                        'asset_pemanfaatan_id' => $lease->id,
                        'termin_ke' => $termin,
                        'tahun_ke' => 'Tahun ' . $termin . ' (' . $periodeTahun->year . ')',
                        'nominal_kewajiban' => $besaranTahunan,
                        'nominal_disetor' => null,
                        'tanggal_jatuh_tempo' => $jatuhTempo,
                        'tanggal_bayar' => null,
                        'nomor_sts' => null,
                        'status_pembayaran' => 'menunggu_pembayaran',
                        'catatan' => 'Jadwal penagihan tahunan sesuai PKS.',
                        'verified_by' => null,
                    ]);
                    $countCreated++;
                }
            }
        }

        $this->info("Berhasil membuat {$countCreated} catatan termin setoran Kasda untuk seluruh sewa aktif!");
        return 0;
    }
}
