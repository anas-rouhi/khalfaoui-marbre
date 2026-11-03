<?php

namespace App\Filament\Resources\DevisResource\Pages;

use App\Filament\Resources\DevisResource;
use App\Models\Devis;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDevis extends EditRecord
{
    protected static string $resource = DevisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('whatsapp')
                ->label(__('Répondre sur WhatsApp'))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(fn (Devis $record) => DevisResource::whatsappUrl($record), shouldOpenInNewTab: true),

            Actions\Action::make('appeler')
                ->label(__('Appeler'))
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->url(fn (Devis $record) => 'tel:'.DevisResource::normalisePhone($record->phone)),

            Actions\DeleteAction::make()->label(__('Supprimer')),
        ];
    }

    /**
     * Seul le statut est modifiable ici, et il n'est pas assignable en masse
     * (voir Devis::$fillable) : on l'applique explicitement.
     *
     * @param  Devis  $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (isset($data['status'])) {
            $record->markAs($data['status']);
        }

        return $record;
    }

    public function getTitle(): string
    {
        return __('Demande de :name', ['name' => $this->record->client_name]);
    }
}
