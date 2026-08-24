<?php

namespace App\Filament\Resources\InstallationRateResource\Pages;

use App\Filament\Resources\InstallationRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInstallationRate extends CreateRecord
{
    protected static string $resource = InstallationRateResource::class;

    public function getTitle(): string
    {
        return 'Nouveau tarif de pose';
    }
}
