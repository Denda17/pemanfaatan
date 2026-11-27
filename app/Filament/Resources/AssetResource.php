<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $recordTitleAttribute = 'nama_objek';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Manajemen BMD';

    protected static ?string $modelLabel = 'Aset BMD';

    protected static ?string $pluralModelLabel = 'Katalog Aset BMD';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_aset', 'nama_objek', 'alamat', 'kabupaten_kota'];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Asset $record */
        return [
            'Kode' => $record->kode_aset ?? '-',
            'Lokasi' => $record->kabupaten_kota ?? '-',
            'Status' => ucfirst(str_replace('_', ' ', $record->status_ketersediaan ?? '-')),
        ];
    }

    protected static array $regencies = [
        'Kota Bandung', 'Kota Bogor', 'Kota Bekasi', 'Kota Depok', 'Kota Cirebon',
        'Kota Sukabumi', 'Kota Tasikmalaya', 'Kota Cimahi', 'Kota Banjar',
        'Kabupaten Bandung Barat', 'Kabupaten Bandung', 'Kabupaten Bekasi', 'Kabupaten Bogor',
        'Kabupaten Ciamis', 'Kabupaten Cianjur', 'Kabupaten Cirebon', 'Kabupaten Garut',
        'Kabupaten Indramayu', 'Kabupaten Karawang', 'Kabupaten Kuningan', 'Kabupaten Majalengka',
        'Kabupaten Pangandaran', 'Kabupaten Purwakarta', 'Kabupaten Subang', 'Kabupaten Sukabumi',
        'Kabupaten Sumedang', 'Kabupaten Tasikmalaya',
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
                                    ->live()
                                    ->required(),

                                Forms\Components\DateTimePicker::make('booking_opened_at')
                                    ->label('Waktu Pembukaan Minat Pertama')
                                    ->visible(fn (Forms\Get $get) => $get('status_ketersediaan') === 'proses_booking'),

                                Forms\Components\DateTimePicker::make('booking_deadline_at')
                                    ->label('Batas Akhir Penutupan Minat (5 Hari Kerja)')
                                    ->helperText('Setelah tanggal/jam ini, tombol dan form booking publik otomatis ditutup.')
                                    ->visible(fn (Forms\Get $get) => $get('status_ketersediaan') === 'proses_booking'),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Tampilkan sebagai Aset Unggulan (Featured) di Beranda')
                                    ->default(false),

                                Forms\Components\Select::make('tipe_aset')
                                    ->label('Tipe / Struktur Aset')
                                    ->options([
                                        'tunggal' => 'Aset Tunggal (Satu Objek Utuh)',
                                        'multi_unit' => 'Aset Kompleks / Multi-Unit (Memiliki Ruko / Kios / Unit Mandiri)',
                                    ])
                                    ->default('tunggal')
                                    ->live()
                                    ->required()
                                    ->helperText('Pilih Multi-Unit jika aset ini memiliki banyak unit/ruko (seperti Komplek Banceuy Permai)'),

                                Forms\Components\Select::make('site_plan_type')
                                    ->label('Tipe Denah / Site Plan Interaktif')
                                    ->options([
                                        'none' => 'Tanpa Denah Khusus',
                                        'interactive_cluster' => 'Denah Cluster / Kompleks Interaktif',
                                    ])
                                    ->default('none')
                                    ->visible(fn (Forms\Get $get) => $get('tipe_aset') === 'multi_unit'),
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

                        Forms\Components\Tabs\Tab::make('4. Foto, Galeri, Video & Site Plan')
                            ->schema([
                                Forms\Components\FileUpload::make('foto_utama')
                                    ->label('Foto Utama Objek (Cover Marketplace)')
                                    ->disk('public')
                                    ->directory('assets/thumbnails')
                                    ->image()
                                    ->imageEditor()
                                    ->helperText('Foto resolusi tinggi tampak depan objek aset BMD')
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('foto_galeri')
                                    ->label('Galeri Multi-Foto Lapangan (Hingga 10 Foto)')
                                    ->disk('public')
                                    ->directory('assets/gallery')
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(10)
                                    ->image()
                                    ->helperText('Unggah hingga 10 foto kondisi fisik lapangan dari berbagai sudut (tampak depan, akses, batas persil, interior, lingkungan)')
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('video_path')
                                    ->label('Upload Video Peninjauan / Drone Lapangan (MP4 / WebM)')
                                    ->disk('public')
                                    ->directory('assets/videos')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                                    ->maxSize(102400)
                                    ->helperText('Unggah video peninjauan lapangan resmi atau video udara drone (maksimal 100MB)')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('video_url')
                                    ->label('Atau Tautan Video YouTube / Drone Walkthrough')
                                    ->placeholder('Contoh: https://www.youtube.com/watch?v=... atau https://youtu.be/...')
                                    ->url()
                                    ->helperText('Jika video berdurasi panjang dan diunggah di YouTube / Cloud, masukkan URL-nya di sini')
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('site_plan_url')
                                    ->label('Denah Tapak / Peta Site Plan Bidang Tanah')
                                    ->disk('public')
                                    ->directory('assets/site-plans')
                                    ->image()
                                    ->imageEditor()
                                    ->helperText('Gambar peta ukur BPN, denah tapak, atau site plan arsitektur bidang tanah')
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
                Tables\Columns\ImageColumn::make('foto_utama')
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
                    ->limit(42)
                    ->tooltip(fn (Asset $record): ?string => $record->nama_objek)
                    ->searchable()
                    ->description(function (Asset $record): string {
                        $tipe = $record->isMultiUnit() ? 'Multi-Unit' : ($record->bentuk_objek ?: 'Tunggal');

                        return "{$tipe} • ".($record->kabupaten_kota ?? '-');
                    }),

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

                Tables\Columns\TextColumn::make('tipe_aset')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'multi_unit' ? 'purple' : 'gray')
                    ->formatStateUsing(fn (string $state, Asset $record): string => $state === 'multi_unit' ? 'Multi-Unit ('.$record->units()->count().' Unit)' : 'Tunggal')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('booking_deadline_at')
                    ->label('Batas Minat (5 Hari Kerja)')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->description(fn (Asset $record): ?string => $record->status_ketersediaan === 'proses_booking' ? ($record->isBookingOpen() ? 'Buka (sisa '.$record->remaining_booking_time.')' : 'Sudah Ditutup') : null)
                    ->toggleable(),

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
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.').' m²' : '-')
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
            RelationManagers\UnitsRelationManager::class,
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
