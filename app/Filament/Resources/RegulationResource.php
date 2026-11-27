<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegulationResource\Pages;
use App\Models\Regulation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegulationResource extends Resource
{
    protected static ?string $model = Regulation::class;

    protected static ?string $recordTitleAttribute = 'judul';

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Regulasi & Legal';

    protected static ?string $modelLabel = 'Dasar Hukum / Regulasi';

    protected static ?string $pluralModelLabel = 'Dasar Hukum & SOP';

    protected static ?int $navigationSort = 4;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nomor', 'judul', 'penerbit'];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Regulation $record */
        return [
            'Nomor' => $record->nomor,
            'Kategori' => $record->kategori_label,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identitas Dokumen Hukum & SOP')
                            ->description('Pilih kategori utama regulasi terlebih dahulu, lalu lengkapi nomor, judul, dan hierarki penerbit.')
                            ->schema([
                                Forms\Components\ToggleButtons::make('kategori_regulasi')
                                    ->label('Kategori Utama Regulasi')
                                    ->options(Regulation::kategoriOptions())
                                    ->icons([
                                        Regulation::KATEGORI_PENGELOLAAN_BMD => 'heroicon-m-scale',
                                        Regulation::KATEGORI_KDKMP => 'heroicon-m-building-library',
                                        Regulation::KATEGORI_SOP => 'heroicon-m-clipboard-document-check',
                                    ])
                                    ->colors([
                                        Regulation::KATEGORI_PENGELOLAAN_BMD => 'success',
                                        Regulation::KATEGORI_KDKMP => 'info',
                                        Regulation::KATEGORI_SOP => 'warning',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->default(Regulation::KATEGORI_PENGELOLAAN_BMD)
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Forms\Set $set): void {
                                        if ($state === Regulation::KATEGORI_SOP) {
                                            $set('jenis', 'Standar Operasional Prosedur (SOP)');
                                            $set('tingkat', 'sop');
                                            $set('badge_color', 'amber');
                                        } elseif ($state === Regulation::KATEGORI_KDKMP) {
                                            $set('badge_color', 'blue');
                                        } elseif ($state === Regulation::KATEGORI_PENGELOLAAN_BMD) {
                                            $set('badge_color', 'emerald');
                                        }
                                    })
                                    ->columnSpanFull(),

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
                                        'Undang-Undang / Inpres' => 'Undang-Undang / Instruksi Presiden (Inpres)',
                                        'Peraturan Pemerintah (PP)' => 'Peraturan Pemerintah (PP)',
                                        'Peraturan Menteri Dalam Negeri' => 'Peraturan Menteri Dalam Negeri (Permendagri)',
                                        'Peraturan Menteri Koperasi / Terkait' => 'Peraturan Menteri Koperasi / Kementerian Terkait',
                                        'Peraturan Daerah (Perda)' => 'Peraturan Daerah (Perda)',
                                        'Peraturan Gubernur (Pergub)' => 'Peraturan Gubernur (Pergub)',
                                        'Keputusan Gubernur (Kepgub)' => 'Keputusan Gubernur (Kepgub)',
                                        'Surat Edaran (SE)' => 'Surat Edaran (SE)',
                                        'Standar Operasional Prosedur (SOP)' => 'Standar Operasional Prosedur (SOP)',
                                        'Petunjuk Teknis (Juknis)' => 'Petunjuk Teknis (Juknis)',
                                        'Lainnya' => 'Lainnya',
                                    ])
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('tingkat')
                                    ->label('Tingkat / Hierarki Penerbit')
                                    ->options([
                                        'daerah' => 'Pemerintah Daerah Prov. Jabar (Perda / Pergub / Kepgub)',
                                        'kemendagri' => 'Kementerian / Lembaga Pusat',
                                        'pusat' => 'Pemerintah Pusat (UU / PP / Inpres)',
                                        'sop' => 'Internal UPTD / BPKAD (SOP & Juknis)',
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
                                    ->placeholder('Ketik poin lalu tekan Enter')
                                    ->helperText('Poin-poin ini muncul sebagai daftar checklist pada kartu di web publik.')
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
                                    ->maxSize(51200)
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
                                    ->helperText('Aktifkan agar dapat dilihat dan diunduh di halaman publik.')
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
                                        'blue' => 'Blue (Biru KDKMP)',
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
                    ->width('50px'),

                Tables\Columns\TextColumn::make('nomor')
                    ->label('Nomor & Judul Dokumen')
                    ->searchable(['nomor', 'judul', 'penerbit', 'deskripsi'])
                    ->description(fn (Regulation $record): string => Str::limit($record->judul, 70))
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kategori_regulasi')
                    ->label('Kategori Utama')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Regulation::KATEGORI_KDKMP => 'info',
                        Regulation::KATEGORI_SOP => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        Regulation::KATEGORI_KDKMP => 'Dasar Hukum KDKMP',
                        Regulation::KATEGORI_SOP => 'SOP & Juknis',
                        default => 'Pengelolaan BMD',
                    })
                    ->description(fn (Regulation $record): string => $record->jenis),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->description(fn (Regulation $record): string => $record->formatted_size)
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Tayang')
                    ->alignCenter(),
            ])
            ->defaultSort('urutan', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_regulasi')
                    ->label('Kategori Utama')
                    ->options(Regulation::kategoriOptions()),

                Tables\Filters\SelectFilter::make('tingkat')
                    ->label('Hierarki Tingkat')
                    ->options([
                        'daerah' => 'Prov. Jabar',
                        'kemendagri' => 'Kementerian',
                        'pusat' => 'Pemerintah Pusat',
                        'sop' => 'SOP & Juknis',
                        'lainnya' => 'Lainnya',
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
                    ->tooltip('Pratinjau PDF')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->iconButton()
                    ->url(fn (Regulation $record) => $record->url_preview)
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
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
