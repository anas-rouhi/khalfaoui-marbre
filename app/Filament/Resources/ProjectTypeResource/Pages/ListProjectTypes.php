<?php

namespace App\Filament\Resources\ProjectTypeResource\Pages;

use App\Filament\Resources\ProjectTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectTypes extends ListRecords
{
    protected static string $resource = ProjectTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('Ajouter un type')),
        ];
    }

    public function getTitle(): string
    {
        return __('Types de projet');
    }

    public function getSubheading(): ?string
    {
        return __('Boutons de filtre de la section Réalisations. Seuls les types portés par au moins une réalisation publiée apparaissent sur le site.');
    }
}
