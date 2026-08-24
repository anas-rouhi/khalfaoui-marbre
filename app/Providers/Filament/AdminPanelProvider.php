<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Page « Profil » dans le menu utilisateur : nom, e-mail et mot de
            // passe modifiables sans passer par la console.
            // `isSimple: false` la rend dans la coque du panneau — barre
            // latérale et en-tête de marque — plutôt qu'en page isolée.
            ->profile(isSimple: false)
            ->brandName(config('company.legal_name'))
            // Vue dédiée plutôt qu'une simple URL : elle pose la signature sur
            // un socle sombre, sans quoi le lettrage blanc disparaîtrait sur
            // le fond clair du panneau.
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('3.25rem')
            ->favicon(asset(ltrim(config('company.logo_mark'), '/')))
            ->colors([
                // Même vert émeraude que le site public.
                'primary' => Color::hex('#1e9d6b'),
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Blue,
            ])
            ->navigationGroups([
                NavigationGroup::make('Catalogue'),
                NavigationGroup::make('Clients'),
                NavigationGroup::make('Réglages'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
