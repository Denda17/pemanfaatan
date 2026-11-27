<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssetPemanfaatanResource;
use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use App\Models\AssetPemanfaatanPayment;
use App\Models\BookingPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Ringkasan Portofolio BMD & Realisasi Kasda';

    protected ?string $description = 'Statistik aset daerah, kontrak pemanfaatan berjalan, dan penerimaan retribusi terverifikasi.';

    protected function getStats(): array
    {
        $totalAset = Asset::count();
        $asetTersedia = Asset::where('status_ketersediaan', 'tersedia')->count();
        $sewaAktif = AssetPemanfaatan::where('kategori_status', 'sewa_aktif')->count();
        $dalamProses = AssetPemanfaatan::where('kategori_status', 'dalam_proses')->count();
        $berakhir = AssetPemanfaatan::where('kategori_status', 'berakhir')->count();
        $warningPerpanjangan = AssetPemanfaatan::where('status_perpanjangan', 'peringatan_jatuh_tempo')->count();

        $setoranRekap = (float) AssetPemanfaatanPayment::where('status_pembayaran', 'lunas')->sum('nominal_disetor');
        $setoranPortal = (float) BookingPayment::where('status_pembayaran', 'lunas')->sum('nominal_disetor');
        $totalSetoranKasda = $setoranRekap + $setoranPortal;

        return [
            Stat::make('Total Katalog BMD', number_format($totalAset, 0, ',', '.'))
                ->description("{$asetTersedia} objek siap ditawarkan")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->url(AssetResource::getUrl('index')),

            Stat::make('Sewa Aktif Berjalan', number_format($sewaAktif, 0, ',', '.'))
                ->description('Kontrak PKS produktif aktif')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(AssetPemanfaatanResource::getUrl('index')),

            Stat::make('Dalam Proses Usulan', number_format($dalamProses, 0, ',', '.'))
                ->description('Kepgub / Pembayaran / PKS')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(AssetPemanfaatanResource::getUrl('index')),

            Stat::make('Pemanfaatan Berakhir', number_format($berakhir, 0, ',', '.'))
                ->description('Siap dipasarkan ulang')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info')
                ->url(AssetPemanfaatanResource::getUrl('index')),

            Stat::make('Jatuh Tempo (≤ 6 Bulan)', number_format($warningPerpanjangan, 0, ',', '.'))
                ->description($warningPerpanjangan > 0 ? 'Perlu pengingat perpanjangan' : 'Semua masa sewa aman')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($warningPerpanjangan > 0 ? 'danger' : 'gray')
                ->url(AssetPemanfaatanResource::getUrl('index')),

            Stat::make('Realisasi Masuk Kasda', 'Rp '.number_format($totalSetoranKasda, 0, ',', '.'))
                ->description('Akumulasi setoran ber-STS lunas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(AssetPemanfaatanResource::getUrl('index')),
        ];
    }
}
