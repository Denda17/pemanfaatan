<?php

namespace App\Filament\Resources\BookingRequirementResource\Pages;

use App\Filament\Resources\BookingRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookingRequirements extends ListRecords
{
    protected static string $resource = BookingRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
