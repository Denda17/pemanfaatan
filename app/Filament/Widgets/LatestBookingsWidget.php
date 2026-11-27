<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestBookingsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Daftar Pengajuan Prioritas (Siap Ditindaklanjuti)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['asset'])
                    ->withCount([
                        'documents',
                        'documents as pending_documents_count' => fn (Builder $q) => $q->where('status_verifikasi', 'pending'),
                        'payments as requested_codes_count' => fn (Builder $q) => $q->where('status_pembayaran', 'kode_diminta'),
                        'payments as pending_payments_count' => fn (Builder $q) => $q->where('status_pembayaran', 'pending_verifikasi'),
                    ])
                    ->where(function (Builder $query): void {
                        $query->whereNotIn('status', ['rejected', 'expired', 'cancelled'])
                            ->orWhereHas('payments', fn (Builder $q) => $q->whereIn('status_pembayaran', ['kode_diminta', 'pending_verifikasi']));
                    })
                    ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'beauty_contest' THEN 2 WHEN status = 'document_review' THEN 3 ELSE 4 END")
                    ->latest('updated_at')
                    ->limit(5)
            )
            ->headerActions([
                Tables\Actions\Action::make('lihat_semua')
                    ->label('Lihat Semua Pengajuan')
                    ->icon('heroicon-m-arrow-right')
                    ->color('gray')
                    ->size('xs')
                    ->url(BookingResource::getUrl('index')),
            ])
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('kode_booking')
                    ->label('Kode Booking')
                    ->weight('bold')
                    ->copyable()
                    ->description(fn (Booking $record): string => $record->created_at?->diffForHumans() ?? '-'),

                Tables\Columns\TextColumn::make('asset.nama_objek')
                    ->label('Objek Aset')
                    ->wrap()
                    ->limit(38)
                    ->description(fn (Booking $record): string => $record->asset?->kabupaten_kota ?? '-'),

                Tables\Columns\TextColumn::make('nama_pemohon')
                    ->label('Pemohon')
                    ->weight('medium')
                    ->description(fn (Booking $record): string => ($record->nama_instansi && $record->nama_instansi !== 'Perorangan' ? $record->nama_instansi.' • ' : '').$record->nomor_whatsapp),

                Tables\Columns\TextColumn::make('status')
                    ->label('Tahap')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'contacted' => 'info',
                        'document_review' => 'primary',
                        'beauty_contest' => 'purple',
                        'field_survey' => 'indigo',
                        'approved', 'contract_signed' => 'success',
                        'rejected', 'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Baru Masuk',
                        'contacted' => 'Dihubungi',
                        'document_review' => 'Verif Berkas',
                        'beauty_contest' => 'Beauty Contest',
                        'field_survey' => 'Survei',
                        'approved' => 'Disetujui',
                        'contract_signed' => 'PKS Aktif',
                        'rejected' => 'Ditolak',
                        'expired' => 'Gugur',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('next_action')
                    ->label('Tindakan Admin')
                    ->state(function (Booking $record): string {
                        if (($record->requested_codes_count ?? 0) > 0) {
                            return 'Upload Kode Bayar';
                        }
                        if (($record->pending_payments_count ?? 0) > 0) {
                            return 'Sahkan Bukti Setor';
                        }
                        if ($record->status === 'pending') {
                            return 'Follow-up WA';
                        }
                        if ($record->status === 'beauty_contest') {
                            return $record->isBeautyContestScheduled() ? 'Tetapkan Pemenang' : 'Jadwalkan Seleksi';
                        }
                        if (($record->pending_documents_count ?? 0) > 0) {
                            return "Verifikasi {$record->pending_documents_count} Berkas";
                        }

                        return 'Pantau Progres';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'Upload Kode') => 'info',
                        str_starts_with($state, 'Sahkan') || str_starts_with($state, 'Follow-up') || str_starts_with($state, 'Verifikasi') => 'warning',
                        str_contains($state, 'Seleksi') || str_contains($state, 'Pemenang') => 'purple',
                        default => 'gray',
                    }),
            ])
            ->recordUrl(fn (Booking $record): string => route('filament.admin.resources.bookings.edit', ['record' => $record]))
            ->actions([
                Tables\Actions\Action::make('chat_wa')
                    ->label('WA')
                    ->tooltip('Hubungi via WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->iconButton()
                    ->url(fn (Booking $record): string => $record->whatsapp_url, true)
                    ->action(function (Booking $record): void {
                        if ($record->status === 'pending') {
                            $record->update([
                                'status' => 'contacted',
                                'tanggal_dihubungi' => now(),
                                'petugas_uptd_id' => Auth::id(),
                            ]);
                            Notification::make()
                                ->title('Status Diperbarui')
                                ->body("Status booking {$record->kode_booking} beralih ke 'Sudah Dihubungi'.")
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('detail')
                    ->label('Proses')
                    ->icon('heroicon-m-arrow-right')
                    ->color('primary')
                    ->button()
                    ->size('xs')
                    ->url(fn (Booking $record): string => route('filament.admin.resources.bookings.edit', ['record' => $record])),
            ]);
    }
}
