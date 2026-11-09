<?php

namespace App\Filament\Resources\AssetPemanfaatanResource\Pages;

use App\Filament\Resources\AssetPemanfaatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetPemanfaatan extends EditRecord
{
    protected static string $resource = AssetPemanfaatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
