<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function beforeSave(): void
    {
        /** @var Booking $record */
        $record = $this->getRecord();
        $newStatus = $this->data['status'] ?? null;

        if ($newStatus === 'contract_signed' && $record->status !== 'contract_signed' && ! BookingResource::canSignContract($record)) {
            Notification::make()
                ->title('Dokumen PKS Belum Terverifikasi')
                ->body('Status "PKS Ditandatangani & Sewa Aktif" hanya dapat dipilih apabila dokumen Perjanjian Sewa (PKS) telah diunggah dan berstatus Terverifikasi.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        /** @var Booking $record */
        $record = $this->getRecord();
        $record->asset?->syncBookingStatus();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('hubungi_wa')
                ->label('Hubungi WA')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->url(fn (Booking $record): string => $record->whatsapp_url, true)
                ->action(function (Booking $record): void {
                    if ($record->status === 'pending') {
                        $record->update([
                            'status' => 'contacted',
                            'tanggal_dihubungi' => now(),
                            'petugas_uptd_id' => Auth::id(),
                        ]);
                        $this->refreshFormData(['status', 'tanggal_dihubungi', 'petugas_uptd_id']);
                    }
                }),

            Actions\Action::make('jadwalkan_bc')
                ->label('Jadwalkan Seleksi')
                ->icon('heroicon-o-calendar-days')
                ->color('purple')
                ->visible(fn (Booking $record): bool => $record->status === 'beauty_contest')
                ->form([
                    Forms\Components\DateTimePicker::make('beauty_contest_scheduled_at')
                        ->label('Waktu Pelaksanaan Beauty Contest')
                        ->required()
                        ->default(fn (Booking $record) => $record->beauty_contest_scheduled_at ?? now()->addWeekdays(2)->setTime(9, 30)),
                    Forms\Components\TextInput::make('beauty_contest_location')
                        ->label('Tempat / Tautan Virtual Meet')
                        ->required()
                        ->default(fn (Booking $record) => $record->beauty_contest_location ?? 'Ruang Rapat Kantor UPTD Pengamanan & Pemanfaatan Aset, Jl. Ciliwung No. 11 Bandung'),
                    Forms\Components\Textarea::make('beauty_contest_notes')
                        ->label('Petunjuk Materi Paparan Bagi Peminat')
                        ->default(fn (Booking $record) => $record->beauty_contest_notes ?? 'Peserta menyiapkan materi paparan rencana usaha, kesiapan modal, dan estimasi dampak PAD (durasi 30 menit).')
                        ->rows(3),
                ])
                ->action(function (Booking $record, array $data): void {
                    $count = $record->scheduleBeautyContest(
                        Carbon::parse($data['beauty_contest_scheduled_at']),
                        $data['beauty_contest_location'],
                        $data['beauty_contest_notes']
                    );
                    $this->refreshFormData(['beauty_contest_scheduled_at', 'beauty_contest_location', 'beauty_contest_notes']);

                    Notification::make()
                        ->title('Jadwal Beauty Contest Ditetapkan')
                        ->body("Jadwal disinkronkan ke {$count} pemohon yang bersaing pada objek ini.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('tetapkan_pemenang_bc')
                ->label('Tetapkan Pemenang')
                ->icon('heroicon-o-trophy')
                ->color('warning')
                ->visible(fn (Booking $record): bool => $record->status === 'beauty_contest')
                ->requiresConfirmation()
                ->modalHeading('Tetapkan Pemenang Seleksi Beauty Contest')
                ->form([
                    Forms\Components\FileUpload::make('beauty_contest_berita_acara_path')
                        ->label('Unggah Berita Acara Seleksi (.pdf)')
                        ->disk('local')
                        ->directory('booking-documents')
                        ->acceptedFileTypes(['application/pdf']),
                    Forms\Components\Textarea::make('catatan_admin')
                        ->label('Catatan Penetapan Hasil Evaluasi UPTD')
                        ->rows(2),
                ])
                ->action(function (Booking $record, array $data): void {
                    $record->awardBeautyContestWinner(
                        $data['beauty_contest_berita_acara_path'] ?? null,
                        $data['catatan_admin'] ?? null
                    );
                    $this->refreshFormData(['status', 'is_beauty_contest_winner', 'catatan_admin', 'beauty_contest_berita_acara_path']);

                    Notification::make()
                        ->title('Pemenang Beauty Contest Ditetapkan')
                        ->body("{$record->nama_pemohon} ditetapkan sebagai pemenang sewa objek ini.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('lihat_portal_pemohon')
                ->label('Pratinjau Portal Pemohon')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Booking $record): string => route('booking.status', $record->kode_booking), true),

            Actions\ActionGroup::make([
                Actions\DeleteAction::make()
                    ->label('Hapus Pengajuan Permanen'),
            ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->tooltip('Tindakan Lainnya'),
        ];
    }
}
