<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use App\Models\BookingPayment;
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

                Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                    ->label('Tanggal Jatuh Tempo (Sesuai PKS)'),

                Forms\Components\TextInput::make('nominal_kewajiban')
                    ->label('Nominal Tagihan / Kewajiban (Rp)')
                    ->numeric()
                    ->prefix('Rp'),

                Forms\Components\TextInput::make('kode_pembayaran')
                    ->label('Kode Pembayaran / Billing Kasda')
                    ->maxLength(100),

                Forms\Components\FileUpload::make('kode_bayar_file_path')
                    ->label('Berkas Kode Bayar / Surat Tagihan (PDF/Gambar)')
                    ->disk('local')
                    ->directory('booking-termin-billing')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable()
                    ->columnSpanFull(),

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
                        'menunggu_pembayaran' => 'Belum Dibayar',
                        'kode_diminta' => 'Diminta Kode Bayar oleh Penyewa',
                        'kode_terbit' => 'Kode Bayar Terbit (Siap Dibayar)',
                        'pending_verifikasi' => 'Menunggu Verifikasi Kasda',
                        'lunas' => 'Lunas (Disetor ke Rekening Kasda)',
                        'ditolak' => 'Perlu Perbaikan / Ditolak',
                    ])
                    ->required()
                    ->default('menunggu_pembayaran'),

                Forms\Components\FileUpload::make('file_path')
                    ->label('File Bukti Setor Bank BJB')
                    ->disk('local')
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
                    ->formatStateUsing(fn ($state) => 'Termin ke-'.$state)
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

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(fn (BookingPayment $record): string => match ($record->due_urgency) {
                        'overdue' => 'danger',
                        'warning' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->placeholder('Belum Diatur'),

                Tables\Columns\TextColumn::make('kode_pembayaran')
                    ->label('Kode Bayar')
                    ->placeholder('-')
                    ->searchable()
                    ->copyable(),

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
                        'pending_verifikasi', 'kode_diminta' => 'warning',
                        'kode_terbit' => 'info',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lunas' => 'Lunas Kasda',
                        'pending_verifikasi' => 'Menunggu Verifikasi',
                        'kode_diminta' => 'Minta Kode Bayar',
                        'kode_terbit' => 'Kode Bayar Terbit',
                        'ditolak' => 'Ditolak / Revisi',
                        default => 'Belum Bayar',
                    }),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verifikator')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('terbitkan_kode_bayar')
                    ->label(fn (BookingPayment $record): string => $record->hasIssuedPaymentCode() ? 'Perbarui Kode Bayar' : 'Upload Kode Bayar')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->visible(fn (BookingPayment $record): bool => $record->termin_ke >= 2 && $record->status_pembayaran !== 'lunas')
                    ->form(fn (BookingPayment $record): array => [
                        Forms\Components\TextInput::make('kode_pembayaran')
                            ->label('Kode Pembayaran / Kode Billing')
                            ->default($record->kode_pembayaran)
                            ->placeholder('Contoh: 8801234567890')
                            ->maxLength(100),

                        Forms\Components\FileUpload::make('kode_bayar_file_path')
                            ->label('Upload Berkas Kode Bayar / Surat Tagihan (Opsional)')
                            ->disk('local')
                            ->directory('booking-termin-billing')
                            ->default($record->kode_bayar_file_path)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240)
                            ->openable()
                            ->downloadable(),

                        Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo (Sesuai PKS)')
                            ->default($record->tanggal_jatuh_tempo),

                        Forms\Components\TextInput::make('nominal_kewajiban')
                            ->label('Nominal Tagihan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default($record->nominal_kewajiban),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan untuk Penyewa')
                            ->default($record->catatan)
                            ->rows(2),
                    ])
                    ->action(function (BookingPayment $record, array $data): void {
                        if (empty($data['kode_pembayaran']) && empty($data['kode_bayar_file_path'])) {
                            Notification::make()
                                ->title('Kode Bayar Belum Diisi')
                                ->body('Harap isi teks Kode Pembayaran atau unggah file kode bayar.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'kode_pembayaran' => $data['kode_pembayaran'] ?? $record->kode_pembayaran,
                            'kode_bayar_file_path' => $data['kode_bayar_file_path'] ?? $record->kode_bayar_file_path,
                            'kode_bayar_file_name' => ! empty($data['kode_bayar_file_path']) ? basename($data['kode_bayar_file_path']) : $record->kode_bayar_file_name,
                            'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'] ?? $record->tanggal_jatuh_tempo,
                            'nominal_kewajiban' => $data['nominal_kewajiban'] ?? $record->nominal_kewajiban,
                            'catatan' => $data['catatan'] ?? $record->catatan,
                            'status_pembayaran' => 'kode_terbit',
                            'kode_diterbitkan_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Kode Pembayaran Diterbitkan')
                            ->body("Kode pembayaran Termin ke-{$record->termin_ke} telah tersedia untuk penyewa.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('lihat_slip')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (BookingPayment $record) => $record->bukti_url, true)
                    ->visible(fn (BookingPayment $record) => ! empty($record->file_path)),

                Tables\Actions\Action::make('verifikasi_lunas')
                    ->label('Sahkan Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengesahan Setoran Kasda')
                    ->modalDescription('Pastikan dana setoran sewa ini telah benar-benar masuk dan terkonfirmasi pada mutasi Rekening Kas Umum Daerah (RKUD) Bank BJB.')
                    ->visible(fn (BookingPayment $record) => $record->status_pembayaran !== 'lunas' && ! empty($record->file_path))
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
