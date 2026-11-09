<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestBookingsWidget extends BaseWidget
{
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Permohonan Booking Aset Terbaru (Perlu Tindak Lanjut Admin)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()->latest('created_at')->limit(6)
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode_booking')
                    ->label('Kode')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('asset.nama_objek')
                    ->label('Objek Aset')
                    ->wrap()
                    ->limit(35),

                Tables\Columns\TextColumn::make('nama_pemohon')
                    ->label('Pemohon')
                    ->weight('medium')
                    ->description(fn (Booking $record) => ($record->nama_instansi ? $record->nama_instansi . ' • ' : '') . $record->nomor_whatsapp),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'contacted' => 'info',
                        'document_review' => 'primary',
                        'field_survey' => 'indigo',
                        'approved' => 'success',
                        'contract_signed' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'contacted' => 'Dihubungi',
                        'document_review' => 'Verif Berkas',
                        'field_survey' => 'Survei',
                        'approved' => 'Disetujui',
                        'contract_signed' => 'PKS Terbit',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('documents_count')
                    ->counts('documents')
                    ->label('Berkas')
                    ->suffix(' Dok')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('chat_wa')
                    ->label('WA')
                    ->tooltip('Hubungi via WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->iconButton()
                    ->url(fn (Booking $record): string => $record->whatsapp_url, true)
                    ->action(function (Booking $record) {
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
                    ->label('Input Berkas')
                    ->tooltip('Detail & Input Berkas')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->iconButton()
                    ->url(fn (Booking $record): string => route('filament.admin.resources.bookings.edit', ['record' => $record])),
            ]);
    }
}
