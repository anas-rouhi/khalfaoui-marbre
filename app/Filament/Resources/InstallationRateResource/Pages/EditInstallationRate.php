<?php

namespace App\Filament\Resources\InstallationRateResource\Pages;

use App\Filament\Resources\InstallationRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstallationRate extends EditRecord
{
    protected static string $resource = InstallationRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('Supprimer'))
                ->hidden(fn () => InstallationRateResource::productCount($this->record) > 0),
        ];
    }

    public function getTitle(): string
    {
        return __('Usage — :name', ['name' => $this->record->label]);
    }
}
