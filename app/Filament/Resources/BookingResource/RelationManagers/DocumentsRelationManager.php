<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use App\Models\BookingRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Kelengkapan Dokumen Administrasi & Sewa';

    protected static ?string $modelLabel = 'Dokumen Persyaratan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('booking_requirement_id')
                    ->label('Jenis Dokumen / Persyaratan')
                    ->options(function () {
                        $adminReqs = BookingRequirement::where('kategori', 'administrasi')
                            ->where('diunggah_oleh', 'admin')
                            ->orderBy('urutan')
                            ->pluck('nama_persyaratan', 'id')
                            ->toArray();

                        $mitraReqs = BookingRequirement::where('kode', 'REQ_BUKTI_BAYAR')
                            ->pluck('nama_persyaratan', 'id')
                            ->toArray();

                        $pemohonReqs = BookingRequirement::where('kategori', 'pemohon')
                            ->orderBy('urutan')
                            ->pluck('nama_persyaratan', 'id')
                            ->toArray();

                        return [
                            '1. Berkas Administrasi & Penetapan Sewa (Diinput Admin UPTD)' => $adminReqs,
                            '2. Khusus Diinput oleh Mitra Penyewa' => $mitraReqs,
                            '3. Berkas Pengajuan Pemohon' => $pemohonReqs,
                        ];
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->helperText(function (Forms\Get $get) {
                        $reqId = $get('booking_requirement_id');
                        if (! $reqId) {
                            return 'Pilih jenis berkas kelengkapan yang akan diinput.';
                        }

                        $req = BookingRequirement::find($reqId);
                        if (! $req) {
                            return null;
                        }

                        if ($req->kode === 'REQ_BUKTI_BAYAR') {
                            return 'Perhatian: Berkas Bukti Bayar secara SOP diunggah langsung oleh Mitra Penyewa melalui portal status permohonan. Admin dapat menginput jika mitra menyerahkan bukti setor fisik/offline.';
                        }

                        if ($req->diunggah_oleh === 'admin') {
                            return 'Berkas resmi ini diterbitkan dan diinput oleh Tim UPTD BPKAD Jawa Barat.';
                        }

                        return 'Berkas pendukung kelengkapan permohonan dari mitra pemohon.';
                    })
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('nomor_dokumen')
                    ->label('Nomor Dokumen / Surat')
                    ->placeholder('Contoh: 015/PKS/BPKAD/2026, 032/Kepgub/2026, atau No. STS Kasda')
                    ->maxLength(255),

                Forms\Components\DatePicker::make('tanggal_dokumen')
                    ->label('Tanggal Dokumen')
                    ->default(now()),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload File Dokumen (PDF / Scan)')
                    ->disk('local')
                    ->directory('booking-documents')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(25600)
                    ->openable()
                    ->downloadable()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status_verifikasi')
                    ->label('Status Hasil Verifikasi')
                    ->options([
                        'verified' => 'Valid & Terverifikasi',
                        'pending' => 'Sedang Ditelaah',
                        'revision' => 'Perlu Perbaikan / Kurang',
                        'rejected' => 'Tidak Memenuhi Syarat (TMS)',
                    ])
                    ->default('verified')
                    ->required(),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan Verifikator UPTD')
                    ->placeholder('Catatan kelengkapan berkas fisik / hasil negosiasi / paraf pejabat...')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_dokumen')
            ->columns([
                Tables\Columns\TextColumn::make('requirement.nama_persyaratan')
                    ->label('Jenis Dokumen')
                    ->weight('bold')
                    ->wrap()
                    ->description(function ($record) {
                        if (! $record->requirement) {
                            return null;
                        }

                        if ($record->requirement->kode === 'REQ_BUKTI_BAYAR') {
                            return 'Khusus Diinput oleh Mitra Penyewa';
                        }

                        if ($record->requirement->diunggah_oleh === 'admin') {
                            return 'Diterbitkan & Diinput Admin UPTD';
                        }

                        return 'Dokumen Pengajuan Pemohon';
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('nomor_dokumen')
                    ->label('No. Dokumen')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_dokumen')
                    ->label('Tgl Dokumen')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'revision' => 'danger',
                        'rejected' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'verified' => 'Terverifikasi',
                        'pending' => 'Menunggu',
                        'revision' => 'Revisi',
                        'rejected' => 'TMS',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('Berkas')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat / Unduh' : '-')
                    ->url(fn ($record) => $record->file_path ? route('booking.documents.view', $record) : null, true)
                    ->color('primary')
                    ->icon('heroicon-o-document-arrow-down'),

                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Diinput Oleh')
                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record->booking?->nama_pemohon.' (Mitra)'))
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_verifikasi')
                    ->options([
                        'verified' => 'Terverifikasi',
                        'pending' => 'Menunggu',
                        'revision' => 'Revisi',
                        'rejected' => 'TMS',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Input Berkas Dokumen')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['admin_id'] = Auth::id();

                        return $data;
                    }),
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

    public function getTabs(): array
    {
        return [
            'administrasi' => Tab::make('Berkas Administrasi & Sewa (7 Berkas)')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('requirement', fn ($q) => $q->where('kategori', 'administrasi'))),
            'pemohon' => Tab::make('Berkas Pengajuan Pemohon')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('requirement', fn ($q) => $q->where('kategori', 'pemohon'))),
            'all' => Tab::make('Semua Dokumen'),
        ];
    }
}
