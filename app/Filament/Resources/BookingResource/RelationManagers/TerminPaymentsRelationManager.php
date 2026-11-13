<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use App\Models\BookingPayment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TerminPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Riwayat Setoran Termin Kasda (Skema Bertahap)';
    protected static ?string $modelLabel = 'Setoran Termin Kasda';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->skema_pembayaran === 'bertahap';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('termin_ke')
                    ->label('Termin Ke-')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('tahun_ke')
                    ->label('Tahun / Periode Kontrak')
                    ->placeholder('Contoh: Tahun ke-2')
                    ->maxLength(50),

                Forms\Components\TextInput::make('nominal_disetor')
                    ->label('Nominal Setoran Riil (Rp)')
                    ->numeric()
                    ->prefix('Rp'),

                Forms\Components\TextInput::make('nomor_sts')
                    ->label('Nomor STS / NTPD / No. Transaksi BJB')
                    ->maxLength(100),

                Forms\Components\DatePicker::make('tanggal_bayar')
                    ->label('Tanggal Penyetoran ke Kasda'),

                Forms\Components\Select::make('status_pembayaran')
                    ->label('Status Verifikasi Setoran')
                    ->options([
                        'lunas' => 'Lunas (Disetor ke Rekening Kasda)',
                        'pending_verifikasi' => 'Menunggu Verifikasi Kasda',
                        'ditolak' => 'Perlu Perbaikan / Ditolak',
                        'menunggu_pembayaran' => 'Belum Dibayar',
                    ])
                    ->required()
                    ->default('pending_verifikasi'),

                Forms\Components\FileUpload::make('file_path')
                    ->label('File Bukti Setor Bank BJB')
                    ->disk('public')
                    ->directory('booking-termin-payments')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(20480)
                    ->openable()
                    ->downloadable()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan Hasil Rekonsiliasi Kasda')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Select::make('verified_by')
                    ->label('Diverifikasi oleh Petugas')
                    ->relationship('verifier', 'name', fn ($query) => $query->where('role', 'admin'))
                    ->default(fn () => Auth::id())
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('termin_ke')
                    ->label('Termin')
                    ->formatStateUsing(fn ($state) => 'Termin ke-' . $state)
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tahun_ke')
                    ->label('Periode Tahun')
                    ->badge()
                    ->color('info')
                    ->default('-'),

                Tables\Columns\TextColumn::make('nominal_disetor')
                    ->label('Nominal Disetor')
                    ->money('IDR', locale: 'id')
                    ->placeholder('Rp 0')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_sts')
                    ->label('Nomor STS / NTPD')
                    ->placeholder('-')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('tanggal_bayar')
                    ->label('Tanggal Setor')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lunas' => 'success',
                        'pending_verifikasi' => 'warning',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lunas' => 'Lunas Kasda',
                        'pending_verifikasi' => 'Menunggu Verifikasi',
                        'ditolak' => 'Ditolak / Revisi',
                        default => 'Belum Bayar',
                    }),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verifikator')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat_slip')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (BookingPayment $record) => $record->bukti_url, true)
                    ->visible(fn (BookingPayment $record) => !empty($record->file_path)),

                Tables\Actions\Action::make('verifikasi_lunas')
                    ->label('Sahkan Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengesahan Setoran Kasda')
                    ->modalDescription('Pastikan dana setoran sewa ini telah benar-benar masuk dan terkonfirmasi pada mutasi Rekening Kas Umum Daerah (RKUD) Bank BJB.')
                    ->visible(fn (BookingPayment $record) => $record->status_pembayaran !== 'lunas' && !empty($record->file_path))
                    ->action(function (BookingPayment $record) {
                        $record->update([
                            'status_pembayaran' => 'lunas',
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Setoran Kasda Disahkan')
                            ->body("Termin ke-{$record->termin_ke} berhasil disahkan lunas.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Catat Setoran Termin'),
            ]);
    }
}
