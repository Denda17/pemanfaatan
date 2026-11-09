<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetPemanfaatanResource\Pages;
use App\Models\AssetPemanfaatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssetPemanfaatanResource extends Resource
{
    protected static ?string $model = AssetPemanfaatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Manajemen BMD';
    protected static ?string $modelLabel = 'Rekap Sewa / PKS';
    protected static ?string $pluralModelLabel = 'Data Pemanfaatan & Sewa BMD';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $warningCount = static::getModel()::where('status_perpanjangan', 'peringatan_jatuh_tempo')->count();
        return $warningCount > 0 ? (string) $warningCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Objek & Mitra Sewa')
                    ->schema([
                        Forms\Components\Select::make('asset_id')
                            ->label('Objek Aset BMD')
                            ->relationship('asset', 'nama_objek')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('jenis_pemanfaatan')
                            ->label('Bentuk Pemanfaatan')
                            ->options([
                                'Sewa' => 'Sewa',
                                'KSP' => 'Kerjasama Pemanfaatan (KSP)',
                                'BGS' => 'Bangun Guna Serah (BGS)',
                                'BSG' => 'Bangun Serah Guna (BSG)',
                                'Pinjam Pakai' => 'Pinjam Pakai',
                            ])
                            ->default('Sewa')
                            ->required(),

                        Forms\Components\TextInput::make('nama_mitra')
                            ->label('Nama Mitra / Penyewa')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('jenis_mitra')
                            ->label('Kategori Mitra')
                            ->options([
                                'Perusahaan (PT)' => 'Perusahaan (PT)',
                                'Perusahaan (CV)' => 'Perusahaan (CV)',
                                'Koperasi' => 'Koperasi',
                                'Yayasan' => 'Yayasan',
                                'Perorangan' => 'Perorangan',
                                'Swasta / Mitra' => 'Swasta / Lainnya',
                            ])
                            ->default('Perorangan'),

                        Forms\Components\TextInput::make('kontak_mitra')
                            ->label('Nomor WhatsApp / Kontak Mitra')
                            ->tel()
                            ->placeholder('Contoh: 081234567890')
                            ->helperText('Digunakan untuk pengiriman notifikasi peringatan dini jatuh tempo sewa BMD.'),
                    ])->columns(2),

                Forms\Components\Section::make('Nilai & Masa Berlaku Sewa')
                    ->schema([
                        Forms\Components\TextInput::make('besaran_sewa_tahunan')
                            ->label('Besaran Sewa per Tahun (Rp)')
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('jangka_waktu_tahun')
                            ->label('Jangka Waktu (Tahun)')
                            ->numeric()
                            ->suffix('Tahun'),

                        Forms\Components\TextInput::make('total_sewa')
                            ->label('Total Nilai Sewa Kontrak (Rp)')
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\Select::make('mekanisme_pembayaran')
                            ->label('Mekanisme Pembayaran')
                            ->options([
                                'Sekaligus' => 'Sekaligus di Awal',
                                'Bertahap' => 'Bertahap per Tahun',
                            ])
                            ->default('Sekaligus'),

                        Forms\Components\DatePicker::make('tanggal_awal')
                            ->label('Tanggal Mulai Sewa'),

                        Forms\Components\DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Berakhir Sewa'),

                        Forms\Components\Select::make('status_perpanjangan')
                            ->label('Status / Early Warning Perpanjangan')
                            ->options([
                                'aktif' => 'Masa Sewa Masih Aktif (Aman)',
                                'peringatan_jatuh_tempo' => 'Mendekati Jatuh Tempo (Perlu Follow-up Perpanjangan)',
                                'selesai' => 'Masa Sewa Berakhir',
                                'diperpanjang' => 'Sudah Diperpanjang',
                            ])
                            ->default('aktif')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Dasar Hukum & Dokumen Legalitas')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_persetujuan')
                            ->label('Nomor Surat Persetujuan Sekda / BPKAD'),

                        Forms\Components\TextInput::make('nomor_kepgub')
                            ->label('Nomor Keputusan Gubernur (Kepgub)'),

                        Forms\Components\TextInput::make('nomor_perjanjian')
                            ->label('Nomor Perjanjian Kerjasama (PKS)'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.nama_objek')
                    ->label('Objek Aset')
                    ->wrap()
                    ->limit(35)
                    ->tooltip(fn (AssetPemanfaatan $record) => $record->asset?->nama_objek)
                    ->description(fn (AssetPemanfaatan $record) => $record->asset?->kabupaten_kota ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_mitra')
                    ->label('Mitra Penyewa')
                    ->wrap()
                    ->limit(30)
                    ->description(fn (AssetPemanfaatan $record) => $record->jenis_mitra ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('besaran_sewa_tahunan')
                    ->label('Sewa / Thn')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_awal')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tanggal_akhir')
                    ->label('Tgl Berakhir')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_perpanjangan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'peringatan_jatuh_tempo' => 'warning',
                        'selesai' => 'danger',
                        'diperpanjang' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktif' => 'Aktif',
                        'peringatan_jatuh_tempo' => 'Jatuh Tempo',
                        'selesai' => 'Berakhir',
                        'diperpanjang' => 'Diperpanjang',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('nomor_perjanjian')
                    ->label('No. PKS')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('kontak_mitra')
                    ->label('Kontak Mitra')
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_akhir', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status_perpanjangan')
                    ->label('Peringatan Perpanjangan')
                    ->options([
                        'peringatan_jatuh_tempo' => 'Perlu Follow-up Perpanjangan (Warna Orange)',
                        'aktif' => 'Masa Sewa Masih Aktif',
                        'selesai' => 'Masa Sewa Telah Berakhir',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('kirimPeringatanWa')
                    ->label('Peringatan WA')
                    ->tooltip('Kirim pesan peringatan jatuh tempo Permendagri 19/2016 via WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->iconButton()
                    ->url(fn (AssetPemanfaatan $record) => $record->whatsapp_reminder_url)
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->tooltip('Edit Data Sewa')
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetPemanfaatans::route('/'),
            'create' => Pages\CreateAssetPemanfaatan::route('/create'),
            'edit' => Pages\EditAssetPemanfaatan::route('/{record}/edit'),
        ];
    }
}
