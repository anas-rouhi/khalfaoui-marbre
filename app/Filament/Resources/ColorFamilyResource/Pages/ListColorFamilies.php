<?php

namespace App\Filament\Resources\ColorFamilyResource\Pages;

use App\Filament\Resources\ColorFamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListColorFamilies extends ListRecords
{
    protected static string $resource = ColorFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('Ajouter une teinte')),
        ];
    }

    public function getTitle(): string
    {
        return __('Teintes');
    }

    public function getSubheading(): ?string
    {
        return __('Boutons du filtre « Teinte » du catalogue. Seules les teintes portées par au moins une fiche apparaissent sur le site.');
    }
}
