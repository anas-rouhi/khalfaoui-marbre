<?php

namespace App\Filament\Resources\ProjectTypeResource\Pages;

use App\Filament\Resources\ProjectTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectType extends EditRecord
{
    protected static string $resource = ProjectTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('Supprimer'))
                ->hidden(fn () => $this->record->projects()->exists()),
        ];
    }

    public function getTitle(): string
    {
        return __('Type de projet — :name', ['name' => $this->record->name]);
    }
}
