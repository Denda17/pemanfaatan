<?php

namespace App\Filament\Resources\BookingRequirementResource\Pages;

use App\Filament\Resources\BookingRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookingRequirement extends EditRecord
{
    protected static string $resource = BookingRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
