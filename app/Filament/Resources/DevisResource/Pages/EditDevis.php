<?php

namespace App\Filament\Resources\DevisResource\Pages;

use App\Filament\Resources\DevisResource;
use App\Models\Devis;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDevis extends EditRecord
{
    protected static string $resource = DevisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('whatsapp')
                ->label('Répondre sur WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(fn (Devis $record) => DevisResource::whatsappUrl($record), shouldOpenInNewTab: true),

            Actions\Action::make('appeler')
                ->label('Appeler')
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->url(fn (Devis $record) => 'tel:'.DevisResource::normalisePhone($record->phone)),

            Actions\DeleteAction::make()->label('Supprimer'),
        ];
    }

    public function getTitle(): string
    {
        return 'Demande de '.$this->record->client_name;
    }
}
