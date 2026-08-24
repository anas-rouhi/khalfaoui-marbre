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
            Actions\CreateAction::make()->label('Ajouter un tarif'),
        ];
    }

    public function getTitle(): string
    {
        return 'Tarifs de pose';
    }

    public function getSubheading(): ?string
    {
        return "Modifiez un tarif directement dans la colonne « Tarif » : l'estimateur du site l'applique immédiatement.";
    }
}
