<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProjectResource;
use Filament\Widgets\Widget;

/**
 * Bannière d'accueil du tableau de bord : salutation et raccourcis vers les
 * trois gestes du quotidien (nouvelle référence, nouveau chantier, site).
 */
class WelcomeBanner extends Widget
{
    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    /** Aucune requête : affichée d'emblée, sans squelette de chargement. */
    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected static string $view = 'filament.widgets.welcome-banner';

    protected function getViewData(): array
    {
        return [
            'userName' => auth()->user()?->name,
            'today' => now()->translatedFormat('l j F Y'),
            'actions' => [
                [
                    'label' => __('Ajouter un marbre'),
                    'url' => ProductResource::getUrl('create'),
                    'icon' => 'heroicon-m-plus',
                    'style' => 'primary',
                ],
                [
                    'label' => __('Ajouter une réalisation'),
                    'url' => ProjectResource::getUrl('create'),
                    'icon' => 'heroicon-m-camera',
                    'style' => 'outline',
                ],
                [
                    'label' => __('Voir le site vitrine'),
                    'url' => url('/'),
                    'icon' => 'heroicon-m-arrow-top-right-on-square',
                    'style' => 'ghost',
                    'external' => true,
                ],
            ],
        ];
    }
}
