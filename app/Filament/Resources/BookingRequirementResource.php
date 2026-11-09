<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingRequirementResource\Pages;
use App\Models\BookingRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingRequirementResource extends Resource
{
    protected static ?string $model = BookingRequirement::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $modelLabel = 'Checklist Persyaratan';
    protected static ?string $pluralModelLabel = 'Master Persyaratan Sewa';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode')
                    ->label('Kode Unik')
                    ->required()
                    ->maxLength(50),

                Forms\Components\TextInput::make('nama_persyaratan')
                    ->label('Nama Persyaratan Dokumen')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi / Dasar Ketentuan')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_required')
                    ->label('Wajib Dilengkapi')
                    ->default(true),

                Forms\Components\TextInput::make('urutan')
                    ->label('Nomor Urut')
                    ->numeric()
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->label('No')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_persyaratan')
                    ->label('Nama Dokumen Persyaratan')
                    ->wrap()
                    ->searchable()
                    ->description(fn (BookingRequirement $record) => $record->kode),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean(),

                Tables\Columns\TextColumn::make('documents_count')
                    ->counts('documents')
                    ->label('Berkas Terkait')
                    ->badge(),
            ])
            ->defaultSort('urutan', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBookingRequirements::route('/'),
            'create' => Pages\CreateBookingRequirement::route('/create'),
            'edit' => Pages\EditBookingRequirement::route('/{record}/edit'),
        ];
    }
}
