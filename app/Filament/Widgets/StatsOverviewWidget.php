<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use App\Models\AssetPemanfaatanPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalAset = Asset::count();
        $sewaAktif = AssetPemanfaatan::where('kategori_status', 'sewa_aktif')->count();
        $dalamProses = AssetPemanfaatan::where('kategori_status', 'dalam_proses')->count();
        $berakhir = AssetPemanfaatan::where('kategori_status', 'berakhir')->count();
        $warningPerpanjangan = AssetPemanfaatan::where('status_perpanjangan', 'peringatan_jatuh_tempo')->count();
        $totalNilaiSewaAktif = AssetPemanfaatan::where('kategori_status', 'sewa_aktif')->sum('besaran_sewa_tahunan');
        $totalSetoranKasda = AssetPemanfaatanPayment::where('status_pembayaran', 'lunas')->sum('nominal_disetor');

        return [
            Stat::make('Total Rekap BMD', number_format($totalAset, 0, ',', '.'))
                ->description('100% Sesuai Rekap Excel BPKAD')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color('primary'),

            Stat::make('Sewa Aktif Berjalan', number_format($sewaAktif, 0, ',', '.'))
                ->description('Kontrak PKS Produktif Berjalan')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Dalam Proses Usulan', number_format($dalamProses, 0, ',', '.'))
                ->description('Kepgub / Bayar / Penandatanganan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Pemanfaatan Berakhir', number_format($berakhir, 0, ',', '.'))
                ->description('Siap Dipasarkan / Perpanjangan')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Jatuh Tempo (≤ 6 Bulan)', number_format($warningPerpanjangan, 0, ',', '.'))
                ->description('Perlu Notifikasi & Follow-up')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($warningPerpanjangan > 0 ? 'danger' : 'success'),

            Stat::make('Realisasi Masuk Kasda', 'Rp ' . number_format($totalSetoranKasda, 0, ',', '.'))
                ->description('Total Setoran Retribusi Ber-STS Resmi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
