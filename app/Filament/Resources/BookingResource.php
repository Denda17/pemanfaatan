<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Pelayanan Pemanfaatan';
    protected static ?string $modelLabel = 'Booking Aset';
    protected static ?string $pluralModelLabel = 'Pengajuan Booking Aset';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan & Peminat')
                    ->schema([
                        Forms\Components\TextInput::make('kode_booking')
                            ->label('Kode Booking')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('asset_id')
                            ->label('Objek Aset BMD')
                            ->relationship('asset', 'nama_objek')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('tipe_pemohon')
                            ->label('Kategori Pemohon')
                            ->options([
                                'perorangan' => 'Perorangan / Pribadi',
                                'badan_usaha' => 'Badan Usaha / Badan Hukum',
                            ])
                            ->default('perorangan')
                            ->required(),

                        Forms\Components\TextInput::make('nama_pemohon')
                            ->label('Nama Lengkap Pemohon / Penanggung Jawab')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nomor_whatsapp')
                            ->label('Nomor WhatsApp Aktif')
                            ->required()
                            ->tel()
                            ->helperText('Nomor WhatsApp akan digunakan petugas UPTD untuk follow-up.'),

                        Forms\Components\TextInput::make('nik_atau_nib')
                            ->label('NIK / NIB Pemohon')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nama_instansi')
                            ->label('Nama Badan Usaha / Koperasi / Perorangan')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('durasi_sewa_tahun')
                            ->label('Rencana Durasi Sewa (Tahun)')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10)
                            ->suffix('Tahun')
                            ->live(),

                        Forms\Components\Select::make('skema_pembayaran')
                            ->label('Skema Pembayaran')
                            ->options([
                                'sekaligus' => 'Dibayar Full Sekaligus di Awal',
                                'bertahap' => 'Pembayaran Bertahap (Termin Tahunan)',
                            ])
                            ->default('sekaligus')
                            ->required()
                            ->live()
                            ->helperText(fn ($get) => $get('skema_pembayaran') === 'bertahap' 
                                ? 'Skema Bertahap: Termin 1 wajib lunas sebelum penandatanganan PKS. Jadwal termin tahun ke-2 dst. wajib dicantumkan dalam klausul naskah PKS.' 
                                : 'Skema Sekaligus: Pelunasan 100% penuh di awal sebelum penandatanganan naskah PKS.'),

                        Forms\Components\FileUpload::make('proposal_bisnis_path')
                            ->label('Dokumen Proposal Bisnis / Feasibility Study')
                            ->directory('booking-documents')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->openable()
                            ->downloadable()
                            ->helperText('Wajib dilampirkan oleh pemohon jika durasi sewa lebih dari 5 tahun.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('rencana_peruntukan')
                            ->label('Rencana Penggunaan / Peruntukan Usaha')
                            ->placeholder('Jelaskan jenis usaha, konsep bisnis, atau fasilitas yang akan dibangun...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Tindak Lanjut & Verifikasi UPTD Pengamanan & Pemanfaatan Aset')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Tahapan Proses')
                            ->options([
                                'pending' => 'Menunggu Tindak Lanjut (Baru Masuk)',
                                'contacted' => 'Sudah Dihubungi via WhatsApp',
                                'document_review' => 'Pengumpulan & Verifikasi Berkas',
                                'field_survey' => 'Peninjauan / Survey Lapangan',
                                'approved' => 'Disetujui Pengelola Barang',
                                'contract_signed' => 'PKS Ditandatangani & Sewa Aktif',
                                'rejected' => 'Dibatalkan / Tidak Memenuhi Syarat',
                            ])
                            ->required()
                            ->live()
                            ->default('pending'),

                        Forms\Components\Select::make('petugas_uptd_id')
                            ->label('Petugas Verifikator UPTD')
                            ->relationship('petugasUptd', 'name', fn ($query) => $query->where('role', 'admin'))
                            ->searchable()
                            ->preload()
                            ->default(fn () => Auth::id()),

                        Forms\Components\DateTimePicker::make('tanggal_booking')
                            ->label('Waktu Booking Masuk')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('tanggal_dihubungi')
                            ->label('Waktu Dihubungi Petugas')
                            ->nullable(),

                        Forms\Components\Textarea::make('catatan_admin')
                            ->label('Catatan Hasil Koordinasi / Survey Lapangan')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan (Jika Ditolak / Batal)')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'rejected')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_booking')
                    ->label('Kode Booking')
                    ->weight('bold')
                    ->copyable()
                    ->description(fn (Booking $record): string => $record->created_at ? $record->created_at->format('d/m/Y H:i') : '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('asset.nama_objek')
                    ->label('Objek Aset')
                    ->wrap()
                    ->limit(35)
                    ->tooltip(fn (Booking $record): ?string => $record->asset?->nama_objek)
                    ->description(fn (Booking $record): string => $record->asset?->kabupaten_kota ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_pemohon')
                    ->label('Pemohon')
                    ->weight('medium')
                    ->wrap()
                    ->description(fn (Booking $record): string => ($record->nama_instansi && $record->nama_instansi !== 'Perorangan' ? $record->nama_instansi . ' • ' : '') . $record->nomor_whatsapp)
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipe_pemohon')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'badan_usaha' => 'info',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'badan_usaha' => 'Badan Usaha',
                        default => 'Perorangan',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'contacted' => 'info',
                        'document_review' => 'primary',
                        'field_survey' => 'indigo',
                        'approved' => 'success',
                        'contract_signed' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'contacted' => 'Dihubungi',
                        'document_review' => 'Verif Berkas',
                        'field_survey' => 'Survei',
                        'approved' => 'Disetujui',
                        'contract_signed' => 'PKS Terbit',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('documents_count')
                    ->counts('documents')
                    ->label('Berkas')
                    ->badge()
                    ->suffix(' Dok')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('tanggal_booking')
                    ->label('Tgl Booking')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nomor_whatsapp')
                    ->label('No. WhatsApp')
                    ->copyable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('durasi_sewa_tahun')
                    ->label('Durasi Sewa')
                    ->formatStateUsing(fn ($state) => $state . ' Tahun')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('skema_pembayaran')
                    ->label('Skema Bayar')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sekaligus' => 'success',
                        'bertahap' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'sekaligus' => 'Full Sekaligus',
                        'bertahap' => 'Bertahap / Termin',
                        default => ucfirst($state ?? '-'),
                    })
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_pemohon')
                    ->label('Kategori Pemohon')
                    ->options([
                        'perorangan' => 'Perorangan / Pribadi',
                        'badan_usaha' => 'Badan Usaha / Badan Hukum',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending (Menunggu Tindak Lanjut)',
                        'contacted' => 'Sudah Dihubungi WA',
                        'document_review' => 'Verifikasi Berkas',
                        'field_survey' => 'Survey Lapangan',
                        'approved' => 'Disetujui',
                        'contract_signed' => 'PKS Terbit',
                        'rejected' => 'Dibatalkan / Batal',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('hubungi_wa')
                    ->label('WA')
                    ->tooltip('Hubungi via WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->iconButton()
                    ->url(fn (Booking $record): string => $record->whatsapp_url, true)
                    ->action(function (Booking $record) {
                        if ($record->status === 'pending') {
                            $record->update([
                                'status' => 'contacted',
                                'tanggal_dihubungi' => now(),
                                'petugas_uptd_id' => Auth::id(),
                            ]);
                            Notification::make()
                                ->title('Status Diperbarui')
                                ->body("Status booking {$record->kode_booking} beralih ke 'Sudah Dihubungi via WhatsApp'.")
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('hubungi_wa_menu')
                        ->label('Chat WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->url(fn (Booking $record): string => $record->whatsapp_url, true)
                        ->action(function (Booking $record) {
                            if ($record->status === 'pending') {
                                $record->update([
                                    'status' => 'contacted',
                                    'tanggal_dihubungi' => now(),
                                    'petugas_uptd_id' => Auth::id(),
                                ]);
                                Notification::make()
                                    ->title('Status Diperbarui')
                                    ->body("Status booking {$record->kode_booking} beralih ke 'Sudah Dihubungi via WhatsApp'.")
                                    ->success()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('update_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Pilih Status Baru')
                                ->options([
                                    'contacted' => 'Sudah Dihubungi via WhatsApp',
                                    'document_review' => 'Pengumpulan & Verifikasi Berkas',
                                    'field_survey' => 'Peninjauan / Survey Lapangan',
                                    'approved' => 'Disetujui Pengelola Barang',
                                    'contract_signed' => 'PKS Ditandatangani & Sewa Aktif',
                                    'rejected' => 'Dibatalkan / Tidak Memenuhi Syarat',
                                ])
                                ->required(),

                            Forms\Components\Textarea::make('catatan_admin')
                                ->label('Catatan Petugas UPTD')
                                ->rows(2),
                        ])
                        ->action(function (Booking $record, array $data) {
                            $record->update([
                                'status' => $data['status'],
                                'catatan_admin' => $data['catatan_admin'] ? ($record->catatan_admin . "\n" . now()->format('d/m/Y H:i') . ': ' . $data['catatan_admin']) : $record->catatan_admin,
                                'petugas_uptd_id' => Auth::id(),
                            ]);

                            // Jika disetujui / PKS terbit, ubah status aset ke 'tersewa'
                            if ($data['status'] === 'contract_signed') {
                                $record->asset?->update(['status_ketersediaan' => 'tersewa']);
                            } elseif ($data['status'] === 'rejected') {
                                $record->asset?->update(['status_ketersediaan' => 'tersedia']);
                            } else {
                                $record->asset?->update(['status_ketersediaan' => 'proses_booking']);
                            }

                            Notification::make()
                                ->title('Status Berhasil Diperbarui')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\EditAction::make()
                        ->label('Detail & Input Berkas')
                        ->icon('heroicon-o-document-text'),
                ])
                ->tooltip('Pilihan Tindakan')
                ->icon('heroicon-m-ellipsis-vertical'),
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
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\TerminPaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
