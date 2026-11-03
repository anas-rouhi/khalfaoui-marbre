<?php

namespace App\Filament\Resources\ProjectTypeResource\Pages;

use App\Filament\Resources\ProjectTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProjectType extends CreateRecord
{
    protected static string $resource = ProjectTypeResource::class;

    public function getTitle(): string
    {
        return __('Nouveau type de projet');
    }
}
