<?php

namespace App\Filament\Resources\RegulationResource\Pages;

use App\Filament\Resources\RegulationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegulation extends EditRecord
{
    protected static string $resource = RegulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Buka PDF')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn ($record) => $record->url_preview)
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
