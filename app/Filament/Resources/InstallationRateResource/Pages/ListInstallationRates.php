<?php

namespace App\Filament\Resources\InstallationRateResource\Pages;

use App\Filament\Resources\InstallationRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstallationRates extends ListRecords
{
    protected static string $resource = InstallationRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('Ajouter un usage')),
        ];
    }

    public function getTitle(): string
    {
        return __('Usages & tarifs de pose');
    }

    public function getSubheading(): ?string
    {
        return __("Chaque usage sert de case à cocher sur les fiches, de filtre dans le catalogue et de choix dans l'estimateur. Modifiez un tarif directement dans la colonne « Tarif » : il s'applique immédiatement.");
    }
}
