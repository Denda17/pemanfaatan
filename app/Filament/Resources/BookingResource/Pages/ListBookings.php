<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Input Pengajuan Manual')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        $baruCount = Booking::whereIn('status', ['pending', 'contacted'])->count();
        $verifikasiCount = Booking::where('status', 'document_review')
            ->orWhereHas('documents', fn (Builder $q) => $q->where('status_verifikasi', 'pending'))
            ->count();
        $bcCount = Booking::where('status', 'beauty_contest')->count();
        $mintaKodeCount = Booking::whereHas('payments', fn (Builder $q) => $q->where('status_pembayaran', 'kode_diminta'))->count();
        $verifBayarCount = Booking::whereHas('payments', function (Builder $q): void {
            $q->where('status_pembayaran', 'pending_verifikasi')
                ->orWhere(function (Builder $sub): void {
                    $sub->where('status_pembayaran', '!=', 'lunas')
                        ->whereNotNull('tanggal_jatuh_tempo')
                        ->whereDate('tanggal_jatuh_tempo', '<', today());
                });
        })->count();

        return [
            'semua' => Tab::make('Semua'),

            'baru' => Tab::make('Baru Masuk')
                ->icon('heroicon-m-inbox-arrow-down')
                ->badge($baruCount > 0 ? $baruCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['pending', 'contacted'])),

            'perlu_verifikasi' => Tab::make('Verifikasi Berkas')
                ->icon('heroicon-m-clipboard-document-check')
                ->badge($verifikasiCount > 0 ? $verifikasiCount : null)
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $q): void {
                    $q->where('status', 'document_review')
                        ->orWhereHas('documents', fn (Builder $dq) => $dq->where('status_verifikasi', 'pending'));
                })),

            'beauty_contest' => Tab::make('Beauty Contest')
                ->icon('heroicon-m-trophy')
                ->badge($bcCount > 0 ? $bcCount : null)
                ->badgeColor('purple')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'beauty_contest')),

            'minta_kode_bayar' => Tab::make('Minta Kode Bayar')
                ->icon('heroicon-m-qr-code')
                ->badge($mintaKodeCount > 0 ? $mintaKodeCount : null)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('payments', fn (Builder $q) => $q->where('status_pembayaran', 'kode_diminta'))),

            'verifikasi_bayar' => Tab::make('Setoran & Jatuh Tempo')
                ->icon('heroicon-m-banknotes')
                ->badge($verifBayarCount > 0 ? $verifBayarCount : null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('payments', function (Builder $q): void {
                    $q->where('status_pembayaran', 'pending_verifikasi')
                        ->orWhere(function (Builder $sub): void {
                            $sub->where('status_pembayaran', '!=', 'lunas')
                                ->whereNotNull('tanggal_jatuh_tempo')
                                ->whereDate('tanggal_jatuh_tempo', '<', today());
                        });
                })),

            'aktif' => Tab::make('Disetujui / PKS Aktif')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['approved', 'contract_signed'])),

            'arsip' => Tab::make('Ditolak / Gugur')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['rejected', 'expired', 'cancelled'])),
        ];
    }
}
