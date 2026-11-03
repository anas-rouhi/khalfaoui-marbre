<?php

namespace App\Filament\Resources\ColorFamilyResource\Pages;

use App\Filament\Resources\ColorFamilyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateColorFamily extends CreateRecord
{
    protected static string $resource = ColorFamilyResource::class;

    public function getTitle(): string
    {
        return __('Nouvelle teinte');
    }
}
