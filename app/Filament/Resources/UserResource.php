<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Manajemen Pengguna';
    protected static ?int $navigationSort = 5;

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
                        Forms\Components\Section::make('Informasi Akun & Akses')
                            ->description('Kredensial login dan hak akses pengguna di sistem.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap (PIC)')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(User::class, 'email', ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('password')
                                    ->label('Kata Sandi')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : 'Minimal 8 karakter.')
                                    ->maxLength(255),

                                Forms\Components\Select::make('role')
                                    ->label('Peran / Hak Akses (Role)')
                                    ->options([
                                        'peminat' => 'Peminat Sewa / Calon Mitra (Portal Publik)',
                                        'admin' => 'Administrator UPTD (Akses Backoffice Panel)',
                                    ])
                                    ->default('peminat')
                                    ->required(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Status Akun Aktif')
                                    ->helperText('Jika dinonaktifkan, pengguna tidak dapat masuk ke sistem.')
                                    ->default(true),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Identitas Pemohon & Legalitas')
                            ->description('Data identitas resmi pemohon sewa atau instansi yang diajukan.')
                            ->schema([
                                Forms\Components\Radio::make('tipe_pemohon')
                                    ->label('Kategori Pemohon')
                                    ->options([
                                        'perorangan' => 'Perorangan / Pribadi',
                                        'badan_usaha' => 'Badan Usaha / Badan Hukum (PT, CV, Koperasi, Yayasan)',
                                    ])
                                    ->default('perorangan')
                                    ->inline()
                                    ->live()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('nik')
                                    ->label('Nomor Induk Kependudukan (NIK)')
                                    ->placeholder('16 digit NIK sesuai KTP')
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Nomor WhatsApp Aktif')
                                    ->placeholder('Contoh: 081234567890')
                                    ->tel()
                                    ->required()
                                    ->maxLength(25),

                                Forms\Components\TextInput::make('instansi')
                                    ->label('Nama Perusahaan / Instansi')
                                    ->placeholder('Contoh: PT Kreasi Jabar Sejahtera')
                                    ->visible(fn (Forms\Get $get) => $get('tipe_pemohon') === 'badan_usaha')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('nib')
                                    ->label('Nomor Induk Berusaha (NIB)')
                                    ->placeholder('13 digit NIB')
                                    ->visible(fn (Forms\Get $get) => $get('tipe_pemohon') === 'badan_usaha')
                                    ->maxLength(30),

                                Forms\Components\TextInput::make('npwp')
                                    ->label('NPWP (Pribadi / Perusahaan)')
                                    ->placeholder('Nomor Pokok Wajib Pajak')
                                    ->maxLength(30),

                                Forms\Components\Textarea::make('alamat')
                                    ->label('Alamat Lengkap Domisili / Kantor')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record): string => $record->email),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'primary',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin UPTD',
                        default => 'Peminat Sewa',
                    }),

                Tables\Columns\TextColumn::make('tipe_pemohon')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'badan_usaha' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'badan_usaha' => 'Badan Usaha',
                        default => 'Perorangan',
                    })
                    ->description(fn (User $record): ?string => $record->instansi),

                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Total Pengajuan')
                    ->counts('bookings')
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Akun Aktif')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin UPTD',
                        'peminat' => 'Peminat Sewa',
                    ]),

                Tables\Filters\SelectFilter::make('tipe_pemohon')
                    ->label('Kategori Pemohon')
                    ->options([
                        'perorangan' => 'Perorangan',
                        'badan_usaha' => 'Badan Usaha',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Akun')
                    ->placeholder('Semua Akun')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif / Suspend'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
