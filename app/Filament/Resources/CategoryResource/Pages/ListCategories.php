<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('Ajouter une famille')),
        ];
    }

    public function getTitle(): string
    {
        return __('Familles de matière');
    }

    public function getSubheading(): ?string
    {
        return __('Boutons du filtre « Famille » du catalogue. Seules les familles rattachées à au moins une fiche apparaissent sur le site.');
    }
}
