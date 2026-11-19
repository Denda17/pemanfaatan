<?php

namespace App\Filament\Resources\AssetResource\RelationManagers;

use App\Models\AssetUnit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Daftar Unit / Ruko Kompleks';

    protected static ?string $modelLabel = 'Unit Ruko';

    protected static ?string $pluralModelLabel = 'Unit Ruko';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Unit & Zona')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_unit')
                            ->label('Nomor Unit')
                            ->placeholder('Contoh: A11, B3, C14')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('blok')
                            ->label('Nama Blok / Koridor')
                            ->placeholder('Contoh: Blok A (Jl. ABC & Jl. Banceuy)')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Select::make('kategori_zona')
                            ->label('Kategori Zona Tarif')
                            ->options([
                                'hook_luar' => 'Hook Luar',
                                'super_premium' => 'Super Premium',
                                'premium' => 'Premium',
                                'hook_dalam' => 'Hook Dalam',
                                'basic' => 'Basic (Standar)',
                                'khusus' => 'Khusus',
                                'amat_khusus' => 'Amat Khusus',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $labels = [
                                    'hook_luar' => 'Hook Luar',
                                    'super_premium' => 'Super Premium',
                                    'premium' => 'Premium',
                                    'hook_dalam' => 'Hook Dalam',
                                    'basic' => 'Basic',
                                    'khusus' => 'Khusus',
                                    'amat_khusus' => 'Amat Khusus',
                                ];
                                if (isset($labels[$state])) {
                                    $set('kategori_label', $labels[$state]);
                                }
                            }),

                        Forms\Components\TextInput::make('kategori_label')
                            ->label('Label Kategori Ditampilkan')
                            ->placeholder('Contoh: Hook Luar')
                            ->required()
                            ->maxLength(100),
                    ])->columns(2),

                Forms\Components\Section::make('Tarif & Spesifikasi')
                    ->schema([
                        Forms\Components\TextInput::make('tarif_tahunan')
                            ->label('Tarif Sewa Tahunan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->helperText('Contoh: 90000000 untuk 90 Juta/thn'),

                        Forms\Components\Select::make('status_ketersediaan')
                            ->label('Status Ketersediaan')
                            ->options([
                                'tersedia' => 'Tersedia (Bisa Disewa)',
                                'proses_booking' => 'Sedang Proses Booking / Minat',
                                'tersewa' => 'Tersewa Aktif',
                                'pemeliharaan' => 'Dalam Perbaikan',
                            ])
                            ->default('tersedia')
                            ->required(),

                        Forms\Components\TextInput::make('luas_tanah')
                            ->label('Luas Tanah (m²)')
                            ->numeric()
                            ->suffix('m²'),

                        Forms\Components\TextInput::make('luas_bangunan')
                            ->label('Luas Bangunan (m²)')
                            ->numeric()
                            ->suffix('m²'),

                        Forms\Components\DateTimePicker::make('booking_deadline_at')
                            ->label('Batas Akhir Masa Booking Unit')
                            ->helperText('Otomatis menghitung mundur 5 hari kerja jika ada peminat pertama'),

                        Forms\Components\TextInput::make('svg_key')
                            ->label('ID Elemen SVG / Peta Tapak')
                            ->placeholder('Contoh: ruko-a11')
                            ->maxLength(50),
                    ])->columns(2),

                Forms\Components\Section::make('Foto Spesifik Unit Ini')
                    ->description('Foto ini akan langsung tampil di galeri utama ketika calon penyewa mengklik unit ruko ini di katalog/detail.')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_utama')
                            ->label('Foto Utama Unit (Tampak Muka Ruko)')
                            ->disk('public')
                            ->directory('assets/units')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('foto_galeri')
                            ->label('Foto Tambahan / Interior Unit (Hingga 5 Foto)')
                            ->disk('public')
                            ->directory('assets/units/gallery')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(5)
                            ->image()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Keterangan / Kondisi Unit')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_unit')
            ->defaultSort('nomor_unit')
            ->columns([
                Tables\Columns\ImageColumn::make('foto_utama')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn (AssetUnit $record) => $record->thumbnail_url),

                Tables\Columns\TextColumn::make('nomor_unit')
                    ->label('No. Unit')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('blok')
                    ->label('Blok / Koridor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori_label')
                    ->label('Zona')
                    ->badge()
                    ->color(fn (AssetUnit $record): string => match ($record->kategori_zona) {
                        'hook_luar' => 'danger',
                        'super_premium' => 'warning',
                        'premium' => 'purple',
                        'hook_dalam' => 'pink',
                        'basic' => 'info',
                        'khusus' => 'success',
                        'amat_khusus' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('tarif_tahunan')
                    ->label('Tarif / Thn')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('luas_bangunan')
                    ->label('Luas')
                    ->formatStateUsing(fn ($state, AssetUnit $record) => ($record->luas_tanah ? $record->luas_tanah.' / ' : '').($state ? $state.' m²' : '-')),

                Tables\Columns\TextColumn::make('status_ketersediaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'proses_booking' => 'warning',
                        'tersewa' => 'secondary',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tersedia' => 'Tersedia',
                        'proses_booking' => 'Booking',
                        'tersewa' => 'Tersewa',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_zona')
                    ->label('Zona Tarif')
                    ->options([
                        'hook_luar' => 'Hook Luar',
                        'super_premium' => 'Super Premium',
                        'premium' => 'Premium',
                        'hook_dalam' => 'Hook Dalam',
                        'basic' => 'Basic',
                        'khusus' => 'Khusus',
                        'amat_khusus' => 'Amat Khusus',
                    ]),

                Tables\Filters\SelectFilter::make('status_ketersediaan')
                    ->label('Status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'proses_booking' => 'Proses Booking',
                        'tersewa' => 'Tersewa',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
