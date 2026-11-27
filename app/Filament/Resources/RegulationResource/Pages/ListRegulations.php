<?php

namespace App\Filament\Resources\RegulationResource\Pages;

use App\Filament\Resources\RegulationResource;
use App\Models\Regulation;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRegulations extends ListRecords
{
    protected static string $resource = RegulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Regulasi / SOP')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getTabs(): array
    {
        $bmdCount = Regulation::where('kategori_regulasi', Regulation::KATEGORI_PENGELOLAAN_BMD)->count();
        $kdkmpCount = Regulation::where('kategori_regulasi', Regulation::KATEGORI_KDKMP)->count();
        $sopCount = Regulation::where('kategori_regulasi', Regulation::KATEGORI_SOP)->count();

        return [
            'semua' => Tab::make('Semua')
                ->badge(Regulation::count()),

            'pengelolaan_bmd' => Tab::make('Pengelolaan BMD')
                ->icon('heroicon-m-scale')
                ->badge($bmdCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori_regulasi', Regulation::KATEGORI_PENGELOLAAN_BMD)),

            'kdkmp' => Tab::make('Dasar Hukum KDKMP')
                ->icon('heroicon-m-building-library')
                ->badge($kdkmpCount)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori_regulasi', Regulation::KATEGORI_KDKMP)),

            'sop' => Tab::make('SOP & Juknis')
                ->icon('heroicon-m-clipboard-document-check')
                ->badge($sopCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori_regulasi', Regulation::KATEGORI_SOP)),
        ];
    }
}
