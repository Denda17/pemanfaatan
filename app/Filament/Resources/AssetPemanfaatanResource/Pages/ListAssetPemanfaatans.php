<?php

namespace App\Filament\Resources\AssetPemanfaatanResource\Pages;

use App\Filament\Resources\AssetPemanfaatanResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListAssetPemanfaatans extends ListRecords
{
    protected static string $resource = AssetPemanfaatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pindaiJatuhTempo')
                ->label('Pindai Jatuh Tempo (H-6 Bulan)')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    Artisan::call('bmd:check-expiring-leases');
                    Notification::make()
                        ->title('Pemindaian Jatuh Tempo Berhasil')
                        ->body('Status masa sewa seluruh kontrak BMD telah diperbarui sesuai ketentuan Permendagri No. 19/2016.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
