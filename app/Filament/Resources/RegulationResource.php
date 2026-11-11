<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegulationResource\Pages;
use App\Models\Regulation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RegulationResource extends Resource
{
    protected static ?string $model = Regulation::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Regulasi & Legal';
    protected static ?string $modelLabel = 'Dasar Hukum / Regulasi';
    protected static ?string $pluralModelLabel = 'Dasar Hukum & SOP';
    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identitas Dokumen Hukum & SOP')
                            ->description('Informasi legalitas, nomor surat keputusan, jenis hierarki, dan penerbit.')
                            ->schema([
                                Forms\Components\TextInput::make('nomor')
                                    ->label('Nomor Peraturan / SK / SOP')
                                    ->placeholder('Contoh: Nomor 3 Tahun 2026 atau 01/SOP/BPKAD/2026')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('tahun')
                                    ->label('Tahun Terbit')
                                    ->numeric()
                                    ->default(date('Y'))
                                    ->required(),

                                Forms\Components\TextInput::make('judul')
                                    ->label('Judul Lengkap Dokumen')
                                    ->placeholder('Contoh: Standar Operasional Prosedur Pengajuan Sewa BMD')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, ?string $operation) {
                                        if ($operation === 'create' && filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug URL (Unik)')
                                    ->helperText('Digunakan pada link download & preview di portal publik.')
                                    ->required()
                                    ->unique(Regulation::class, 'slug', ignoreRecord: true)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('jenis')
                                    ->label('Jenis Dokumen')
                                    ->options([
                                        'Peraturan Daerah (Perda)' => 'Peraturan Daerah (Perda)',
                                        'Peraturan Gubernur (Pergub)' => 'Peraturan Gubernur (Pergub)',
                                        'Peraturan Menteri Dalam Negeri' => 'Peraturan Menteri Dalam Negeri (Permendagri)',
                                        'Peraturan Pemerintah (PP)' => 'Peraturan Pemerintah (PP)',
                                        'Standar Operasional Prosedur (SOP)' => 'Standar Operasional Prosedur (SOP)',
                                        'Keputusan Gubernur (Kepgub)' => 'Keputusan Gubernur (Kepgub)',
                                        'Surat Edaran (SE)' => 'Surat Edaran (SE)',
                                        'Petunjuk Teknis (Juknis)' => 'Petunjuk Teknis (Juknis)',
                                        'Lainnya' => 'Lainnya',
                                    ])
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('tingkat')
                                    ->label('Kategori Hierarki / Wilayah')
                                    ->options([
                                        'daerah' => 'Pemerintah Daerah Prov. Jabar (Perda / Pergub / Kepgub)',
                                        'kemendagri' => 'Kementerian Dalam Negeri (Permendagri)',
                                        'pusat' => 'Pemerintah Pusat (PP / UU / Kemenkeu)',
                                        'sop' => 'SOP & Petunjuk Teknis UPTD / Dinas',
                                        'lainnya' => 'Lainnya / Regulasi Terkait',
                                    ])
                                    ->required()
                                    ->default('daerah'),

                                Forms\Components\TextInput::make('penerbit')
                                    ->label('Instansi / Pejabat Penerbit')
                                    ->placeholder('Contoh: Pemerintah Daerah Provinsi Jawa Barat, BPKAD Jabar, Kemendagri')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Ringkasan & Poin Pokok Bagi Mitra')
                            ->description('Ringkasan ketentuan yang memudahkan peminat sewa memahami aturan main.')
                            ->schema([
                                Forms\Components\Textarea::make('deskripsi')
                                    ->label('Deskripsi / Penjelasan Singkat')
                                    ->placeholder('Ringkasan substansi regulasi atau SOP...')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\TagsInput::make('poin_kunci')
                                    ->label('Poin-Poin Pokok untuk Calon Mitra / Penyewa')
                                    ->placeholder('Ketik poin lalu tekan Enter (misal: "Batas permohonan H-6 bulan sebelum masa sewa habis")')
                                    ->helperText('Poin-poin ini akan muncul sebagai daftar checklist pada kartu regulasi di web publik.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Berkas Dokumen PDF')
                            ->description('Unggah dokumen PDF resmi yang dapat diunduh atau dipratinjau mitra.')
                            ->schema([
                                Forms\Components\FileUpload::make('file_path')
                                    ->label('Berkas PDF Resmi')
                                    ->disk('public')
                                    ->directory('regulasi-docs')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(51200) // 50 MB
                                    ->openable()
                                    ->downloadable()
                                    ->required(fn (?Regulation $record) => $record === null)
                                    ->helperText('Format harus PDF (maksimal 50 MB).'),

                                Forms\Components\TextInput::make('download_name')
                                    ->label('Nama Berkas Unduhan Resmi')
                                    ->placeholder('Contoh: Perda-Jabar-No-3-2026.pdf')
                                    ->helperText('Nama berkas bersih saat diunduh oleh mitra.')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Section::make('Pengaturan Tampilan & Publikasi')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Publikasikan di Portal')
                                    ->helperText('Aktifkan agar dapat dilihat dan diunduh oleh peminat sewa di website.')
                                    ->default(true),

                                Forms\Components\TextInput::make('urutan')
                                    ->label('Nomor Urutan Tampilan')
                                    ->numeric()
                                    ->default(1)
                                    ->helperText('Urutan terkecil tampil paling depan.'),

                                Forms\Components\Select::make('badge_color')
                                    ->label('Warna Aksen Label')
                                    ->options([
                                        'emerald' => 'Emerald (Hijau Daerah)',
                                        'teal' => 'Teal (Toska)',
                                        'blue' => 'Blue (Biru)',
                                        'indigo' => 'Indigo',
                                        'purple' => 'Purple (Ungu Pusat)',
                                        'amber' => 'Amber (Kuning SOP)',
                                        'slate' => 'Slate (Abu Netral)',
                                    ])
                                    ->default('emerald'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->label('No')
                    ->sortable()
                    ->width('60px'),

                Tables\Columns\TextColumn::make('nomor')
                    ->label('Nomor & Judul Regulasi')
                    ->searchable(['nomor', 'judul', 'penerbit', 'deskripsi'])
                    ->description(fn (Regulation $record): string => Str::limit($record->judul, 70))
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (Regulation $record): string => match ($record->tingkat) {
                        'daerah' => 'success',
                        'kemendagri' => 'info',
                        'pusat' => 'primary',
                        'sop' => 'warning',
                        default => 'gray',
                    })
                    ->description(fn (Regulation $record): string => match ($record->tingkat) {
                        'daerah' => 'Prov. Jabar',
                        'kemendagri' => 'Kemendagri',
                        'pusat' => 'Pemerintah Pusat',
                        'sop' => 'SOP & Juknis UPTD',
                        default => 'Lainnya',
                    }),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->state(fn (Regulation $record): string => $record->formatted_size)
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Tayang Publik')
                    ->alignCenter(),
            ])
            ->defaultSort('urutan', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('tingkat')
                    ->label('Hierarki Tingkat')
                    ->options([
                        'daerah' => 'Prov. Jabar',
                        'kemendagri' => 'Kemendagri',
                        'pusat' => 'Pemerintah Pusat',
                        'sop' => 'SOP & Juknis',
                        'lainnya' => 'Lainnya',
                    ]),

                Tables\Filters\SelectFilter::make('jenis')
                    ->label('Jenis Regulasi')
                    ->options([
                        'Peraturan Daerah (Perda)' => 'Perda',
                        'Peraturan Gubernur (Pergub)' => 'Pergub',
                        'Peraturan Menteri Dalam Negeri' => 'Permendagri',
                        'Peraturan Pemerintah (PP)' => 'Peraturan Pemerintah',
                        'Standar Operasional Prosedur (SOP)' => 'SOP',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Publikasi')
                    ->placeholder('Semua Dokumen')
                    ->trueLabel('Hanya Tayang')
                    ->falseLabel('Draft / Nonaktif'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Regulation $record) => $record->url_preview)
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegulations::route('/'),
            'create' => Pages\CreateRegulation::route('/create'),
            'edit' => Pages\EditRegulation::route('/{record}/edit'),
        ];
    }
}
