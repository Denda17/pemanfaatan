<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Manajemen BMD';
    protected static ?string $modelLabel = 'Aset BMD';
    protected static ?string $pluralModelLabel = 'Katalog Aset BMD';
    protected static ?int $navigationSort = 2;

    protected static array $regencies = [
        'Kota Bandung', 'Kota Bogor', 'Kota Bekasi', 'Kota Depok', 'Kota Cirebon', 
        'Kota Sukabumi', 'Kota Tasikmalaya', 'Kota Cimahi', 'Kota Banjar',
        'Kabupaten Bandung Barat', 'Kabupaten Bandung', 'Kabupaten Bekasi', 'Kabupaten Bogor', 
        'Kabupaten Ciamis', 'Kabupaten Cianjur', 'Kabupaten Cirebon', 'Kabupaten Garut', 
        'Kabupaten Indramayu', 'Kabupaten Karawang', 'Kabupaten Kuningan', 'Kabupaten Majalengka', 
        'Kabupaten Pangandaran', 'Kabupaten Purwakarta', 'Kabupaten Subang', 'Kabupaten Sukabumi', 
        'Kabupaten Sumedang', 'Kabupaten Tasikmalaya'
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Data Aset')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('1. Identitas Aset')
                            ->schema([
                                Forms\Components\TextInput::make('kode_aset')
                                    ->label('Kode Barang / Aset BMD')
                                    ->placeholder('Contoh: BMD-JBR-0001')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('nama_objek')
                                    ->label('Nama Objek Pemanfaatan')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('bentuk_objek')
                                    ->label('Bentuk Objek')
                                    ->options([
                                        'Tanah' => 'Tanah',
                                        'Tanah dan bangunan' => 'Tanah dan Bangunan',
                                        'Bangunan' => 'Bangunan',
                                        'Ruko' => 'Ruko / Ruang Usaha',
                                        'Gedung' => 'Gedung / Aula',
                                    ])
                                    ->default('Tanah')
                                    ->required(),

                                Forms\Components\Select::make('status_ketersediaan')
                                    ->label('Status Ketersediaan Marketplace')
                                    ->options([
                                        'tersedia' => 'Tersedia (Bisa Disewa / Booking)',
                                        'proses_booking' => 'Sedang Proses Booking / Verifikasi Berkas',
                                        'tersewa' => 'Tersewa (Kontrak Aktif)',
                                        'pemeliharaan' => 'Dalam Pemeliharaan / Belum Siap',
                                    ])
                                    ->default('tersedia')
                                    ->required(),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Tampilkan sebagai Aset Unggulan (Featured) di Beranda')
                                    ->default(false),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('2. Lokasi & Luas')
                            ->schema([
                                Forms\Components\Select::make('kabupaten_kota')
                                    ->label('Kabupaten / Kota')
                                    ->options(array_combine(static::$regencies, static::$regencies))
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Textarea::make('alamat')
                                    ->label('Alamat / Lokasi Lengkap')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('luas_tanah')
                                    ->label('Luas Tanah (m²)')
                                    ->numeric()
                                    ->suffix('m²'),

                                Forms\Components\TextInput::make('luas_bangunan')
                                    ->label('Luas Bangunan (m²)')
                                    ->numeric()
                                    ->suffix('m²'),

                                Forms\Components\TextInput::make('latitude')
                                    ->label('Garis Lintang (Latitude)')
                                    ->numeric()
                                    ->helperText('Contoh: -6.920944'),

                                Forms\Components\TextInput::make('longitude')
                                    ->label('Garis Bujur (Longitude)')
                                    ->numeric()
                                    ->helperText('Contoh: 107.609861'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('3. Spesifikasi & Tarif')
                            ->schema([
                                Forms\Components\TextInput::make('estimasi_tarif_tahunan')
                                    ->label('Estimasi Tarif Sewa Tahunan (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->helperText('Estimasi nilai wajar sewa sesuai formula Permendagri 7/2024'),

                                Forms\Components\TextInput::make('peruntukan')
                                    ->label('Rekomendasi Peruntukan')
                                    ->placeholder('Contoh: Komersial / Ruko / Kafe / Tower BTS / Lahan Parkir'),

                                Forms\Components\TagsInput::make('fasilitas')
                                    ->label('Fasilitas Pendukung')
                                    ->placeholder('Ketik fasilitas lalu tekan Enter')
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('deskripsi')
                                    ->label('Deskripsi Lengkap & Potensi Nilai Ekonomi')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('4. Foto & Galeri Thumbnail')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('Foto Thumbnail Utama (Tampil di Kartu Marketplace)')
                                    ->directory('assets-thumbnail')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('gallery')
                                    ->label('Galeri Foto Tambahan / Denah / Site Plan')
                                    ->directory('assets-gallery')
                                    ->multiple()
                                    ->image()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn (Asset $record) => $record->thumbnail_url),

                Tables\Columns\TextColumn::make('kode_aset')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_objek')
                    ->label('Nama Aset')
                    ->wrap()
                    ->limit(35)
                    ->tooltip(fn (Asset $record): ?string => $record->nama_objek)
                    ->searchable()
                    ->description(fn (Asset $record): string => ($record->bentuk_objek ? $record->bentuk_objek . ' • ' : '') . ($record->kabupaten_kota ?? '-')),

                Tables\Columns\TextColumn::make('status_ketersediaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'proses_booking' => 'warning',
                        'tersewa' => 'info',
                        'pemeliharaan' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tersedia' => 'Tersedia',
                        'proses_booking' => 'Proses Booking',
                        'tersewa' => 'Tersewa',
                        'pemeliharaan' => 'Pemeliharaan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('estimasi_tarif_tahunan')
                    ->label('Tarif / Thn')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bentuk_objek')
                    ->label('Bentuk')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('luas_tanah')
                    ->label('Luas Tnh')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') . ' m²' : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status_ketersediaan')
                    ->label('Status Ketersediaan')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'proses_booking' => 'Proses Booking',
                        'tersewa' => 'Tersewa',
                        'pemeliharaan' => 'Pemeliharaan',
                    ]),

                Tables\Filters\SelectFilter::make('kabupaten_kota')
                    ->label('Kabupaten / Kota')
                    ->options(array_combine(static::$regencies, static::$regencies)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit Aset')
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
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
