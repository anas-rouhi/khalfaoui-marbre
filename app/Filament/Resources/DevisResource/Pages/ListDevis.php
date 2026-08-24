<?php

namespace App\Filament\Resources\DevisResource\Pages;

use App\Filament\Resources\DevisResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListDevis extends ListRecords
{
    protected static string $resource = DevisResource::class;

    /**
     * Pas de bouton « Créer » : les demandes arrivent du formulaire public.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Demandes de devis';
    }

    /**
     * Onglets de suivi, avec le compteur de chaque étape.
     */
    public function getTabs(): array
    {
        $counts = DevisResource::getModel()::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $tabs = [
            'toutes' => Tab::make('Toutes')->badge($counts->sum()),
        ];

        foreach (DevisResource::STATUSES as $status => $label) {
            $tabs[$status] = Tab::make($label)
                ->badge($counts[$status] ?? 0)
                ->badgeColor(DevisResource::statusColor($status))
                ->modifyQueryUsing(fn ($query) => $query->where('status', $status));
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'toutes';
    }
}
