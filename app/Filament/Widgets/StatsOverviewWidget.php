<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalAset = Asset::count();
        $asetTersedia = Asset::where('status_ketersediaan', 'tersedia')->count();
        $bookingPending = Booking::where('status', 'pending')->count();
        $warningPerpanjangan = AssetPemanfaatan::where('status_perpanjangan', 'peringatan_jatuh_tempo')->count();
        $totalNilaiSewa = AssetPemanfaatan::sum('besaran_sewa_tahunan');

        return [
            Stat::make('Total Aset Terdata', number_format($totalAset, 0, ',', '.'))
                ->description('Barang Milik Daerah Pemprov Jabar')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Aset Siap Dikerjasamakan', number_format($asetTersedia, 0, ',', '.'))
                ->description('Status Tersedia di Marketplace')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Booking Baru Masuk', number_format($bookingPending, 0, ',', '.'))
                ->description('Perlu di-follow-up WhatsApp')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color($bookingPending > 0 ? 'warning' : 'gray'),

            Stat::make('Perlu Perpanjangan Sewa', number_format($warningPerpanjangan, 0, ',', '.'))
                ->description('Jatuh tempo dalam ≤ 6 bulan (Warna Orange)')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($warningPerpanjangan > 0 ? 'danger' : 'success'),
        ];
    }
}
