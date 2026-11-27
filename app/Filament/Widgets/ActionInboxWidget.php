<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActionInboxWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Antrian Tindakan Prioritas UPTD';

    protected ?string $description = 'Klik kartu di bawah untuk langsung memproses pengajuan, berkas, atau permintaan kode bayar yang menunggu tindakan.';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $newBookingsCount = Booking::where('status', 'pending')->count();
        $pendingDocsCount = BookingDocument::where('status_verifikasi', 'pending')->count();
        $totalVerificationQueue = Booking::where('status', 'pending')
            ->orWhereHas('documents', fn ($q) => $q->where('status_verifikasi', 'pending'))
            ->count();

        $requestedCodesCount = BookingPayment::where('status_pembayaran', 'kode_diminta')->count();

        $pendingPaymentProofsCount = BookingPayment::where('status_pembayaran', 'pending_verifikasi')->count();
        $overdueTerminsCount = BookingPayment::where('status_pembayaran', '!=', 'lunas')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<', today())
            ->count();

        $beautyContestCount = Booking::where('status', 'beauty_contest')->count();
        $unscheduledBcCount = Booking::where('status', 'beauty_contest')
            ->whereNull('beauty_contest_scheduled_at')
            ->count();

        return [
            Stat::make('Verifikasi Pengajuan & Berkas', (string) $totalVerificationQueue)
                ->description(
                    $totalVerificationQueue > 0
                        ? "{$newBookingsCount} baru masuk • {$pendingDocsCount} berkas pending"
                        : 'Seluruh berkas pemohon terverifikasi'
                )
                ->descriptionIcon($totalVerificationQueue > 0 ? 'heroicon-m-inbox-arrow-down' : 'heroicon-m-check-circle')
                ->color($totalVerificationQueue > 0 ? 'warning' : 'success')
                ->url(BookingResource::getUrl('index', ['activeTab' => $newBookingsCount > 0 ? 'baru' : 'perlu_verifikasi'])),

            Stat::make('Permintaan Kode Bayar Termin', (string) $requestedCodesCount)
                ->description(
                    $requestedCodesCount > 0
                        ? 'Penyewa menunggu kode billing UPTD'
                        : 'Tidak ada permintaan kode baru'
                )
                ->descriptionIcon($requestedCodesCount > 0 ? 'heroicon-m-qr-code' : 'heroicon-m-check-circle')
                ->color($requestedCodesCount > 0 ? 'info' : 'gray')
                ->url(BookingResource::getUrl('index', ['activeTab' => 'minta_kode_bayar'])),

            Stat::make('Setoran & Jatuh Tempo Termin', (string) ($pendingPaymentProofsCount + $overdueTerminsCount))
                ->description(
                    $overdueTerminsCount > 0
                        ? "{$overdueTerminsCount} terlambat • {$pendingPaymentProofsCount} cek bukti setor"
                        : ($pendingPaymentProofsCount > 0 ? "{$pendingPaymentProofsCount} bukti setor menunggu pengesahan" : 'Tidak ada tunggakan termin')
                )
                ->descriptionIcon($overdueTerminsCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-banknotes')
                ->color($overdueTerminsCount > 0 ? 'danger' : ($pendingPaymentProofsCount > 0 ? 'warning' : 'gray'))
                ->url(BookingResource::getUrl('index', ['activeTab' => 'verifikasi_bayar'])),

            Stat::make('Seleksi Beauty Contest', (string) $beautyContestCount)
                ->description(
                    $beautyContestCount > 0
                        ? ($unscheduledBcCount > 0 ? "{$unscheduledBcCount} objek belum dijadwalkan" : 'Menunggu penetapan pemenang')
                        : 'Tidak ada seleksi aktif saat ini'
                )
                ->descriptionIcon('heroicon-m-trophy')
                ->color($beautyContestCount > 0 ? 'purple' : 'gray')
                ->url(BookingResource::getUrl('index', ['activeTab' => 'beauty_contest'])),
        ];
    }
}
