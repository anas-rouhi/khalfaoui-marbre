<?php

namespace App\Filament\Resources\ColorFamilyResource\Pages;

use App\Filament\Resources\ColorFamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditColorFamily extends EditRecord
{
    protected static string $resource = ColorFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('Supprimer'))
                ->hidden(fn () => $this->record->products()->exists()),
        ];
    }

    public function getTitle(): string
    {
        return __('Teinte — :name', ['name' => $this->record->name]);
    }
}
