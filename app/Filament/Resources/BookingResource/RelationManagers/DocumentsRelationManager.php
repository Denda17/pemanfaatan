<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use App\Models\BookingRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Berkas Persyaratan Sewa (Diinput Admin UPTD)';
    protected static ?string $modelLabel = 'Berkas Persyaratan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('booking_requirement_id')
                    ->label('Jenis Persyaratan (Permendagri 7/2024)')
                    ->options(BookingRequirement::orderBy('urutan')->pluck('nama_persyaratan', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('nomor_dokumen')
                    ->label('Nomor Dokumen / Surat')
                    ->placeholder('Contoh: 015/PKS/BPKAD/2026 atau NIB: 012345678')
                    ->maxLength(255),

                Forms\Components\DatePicker::make('tanggal_dokumen')
                    ->label('Tanggal Dokumen')
                    ->default(now()),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload File Dokumen (PDF / Scan)')
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
                    ->placeholder('Catatan kelengkapan berkas fisik / hasil survey / paraf pejabat...')
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
                    ->label('Jenis Persyaratan')
                    ->weight('bold')
                    ->wrap()
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
                    ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, true)
                    ->color('primary')
                    ->icon('heroicon-o-document-arrow-down'),

                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Diinput Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Input Berkas Persyaratan')
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
}
