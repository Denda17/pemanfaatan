<?php

namespace App\Filament\Resources\AssetPemanfaatanResource\RelationManagers;

use App\Models\AssetPemanfaatan;
use App\Models\AssetPemanfaatanPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Riwayat Termin Pembayaran & Setoran Kasda (STS / NTPN)';
    protected static ?string $modelLabel = 'Termin Setoran Kasda';

    public function form(Form $form): Form
    {
        $pemanfaatan = $this->getOwnerRecord();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Termin & Nominal Retribusi')
                    ->schema([
                        Forms\Components\TextInput::make('termin_ke')
                            ->label('Termin Ke-')
                            ->numeric()
                            ->default(fn () => ($pemanfaatan->payments()->count() + 1))
                            ->required(),

                        Forms\Components\TextInput::make('tahun_ke')
                            ->label('Tahun / Periode Kontrak')
                            ->placeholder('Contoh: Tahun Ke-1 (2024)')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('nominal_kewajiban')
                            ->label('Nominal Kewajiban Setor (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(fn () => $pemanfaatan->besaran_sewa_tahunan ?? 0)
                            ->required(),

                        Forms\Components\TextInput::make('nominal_disetor')
                            ->label('Nominal Riil yang Disetor (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(fn () => $pemanfaatan->besaran_sewa_tahunan ?? 0),

                        Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                            ->label('Batas Tanggal Jatuh Tempo')
                            ->default(now()->addMonth()),

                        Forms\Components\DatePicker::make('tanggal_bayar')
                            ->label('Tanggal Penyetoran ke Kasda')
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Legalitas STS & Bukti Setor Kasda')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_sts')
                            ->label('Nomor STS / NTPN / Referensi BJB')
                            ->placeholder('Contoh: STS-2024/BPKAD/045 atau NTPN: 92834719283')
                            ->maxLength(100)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status_pembayaran')
                            ->label('Status Setoran')
                            ->options([
                                'lunas' => 'Lunas (Disetor ke Rekening Kasda)',
                                'menunggu_pembayaran' => 'Menunggu Pembayaran / Belum Jatuh Tempo',
                                'jatuh_tempo' => 'Lewat Batas Jatuh Tempo (Menunggak)',
                                'batal' => 'Dibatalkan',
                            ])
                            ->default('lunas')
                            ->required(),

                        Forms\Components\Select::make('verified_by')
                            ->label('Diverifikasi oleh Petugas')
                            ->relationship('verifier', 'name')
                            ->default(fn () => Auth::id())
                            ->searchable()
                            ->preload(),

                        Forms\Components\FileUpload::make('bukti_bayar_path')
                            ->label('Upload Slip Setoran / Bukti Transfer Bank BJB')
                            ->disk('public')
                            ->directory('payments-sts')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(20480)
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Rekonsiliasi Kasda / Keterangan')
                            ->placeholder('Catatan perihal bukti setor, denda (jika ada), atau nomor rekening penerimaan BJB...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_sts')
            ->columns([
                Tables\Columns\TextColumn::make('termin_ke')
                    ->label('Termin')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => "Ke-{$state}")
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tahun_ke')
                    ->label('Periode')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('nominal_kewajiban')
                    ->label('Kewajiban')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nominal_disetor')
                    ->label('Disetor Kasda')
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_sts')
                    ->label('No. STS / NTPN')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Belum ada STS')
                    ->description(fn (AssetPemanfaatanPayment $record) => $record->tanggal_bayar ? 'Tgl Setor: ' . $record->tanggal_bayar->format('d/m/Y') : '-'),

                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lunas' => 'success',
                        'menunggu_pembayaran' => 'warning',
                        'jatuh_tempo' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lunas' => 'Lunas',
                        'menunggu_pembayaran' => 'Menunggu',
                        'jatuh_tempo' => 'Menunggak',
                        default => ucfirst($state),
                    }),

                Tables\Columns\IconColumn::make('bukti_bayar_path')
                    ->label('Bukti')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
            ])
            ->defaultSort('termin_ke', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Catat Setoran Baru')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat_bukti')
                    ->label('Bukti')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->iconButton()
                    ->tooltip('Buka Bukti Setoran')
                    ->url(fn (AssetPemanfaatanPayment $record) => $record->bukti_bayar_url, true)
                    ->visible(fn (AssetPemanfaatanPayment $record) => !empty($record->bukti_bayar_path)),

                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ]);
    }
}
