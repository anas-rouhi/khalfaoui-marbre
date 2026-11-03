<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetAdminLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
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
            // Lien « Mot de passe oublié ? » sous le formulaire de connexion :
            // le lien de réinitialisation part par e-mail (MAIL_MAILER réel requis).
            ->passwordReset()
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
                // Or « pierre » des accents du tableau de bord.
                'gold' => Color::hex('#b98a3c'),
            ])
            // Libellés évalués à l'affichage (closures) : la configuration du
            // panneau est lue au démarrage, avant que SetAdminLocale n'ait
            // appliqué la langue choisie.
            ->navigationGroups([
                NavigationGroup::make(fn () => __('Catalogue')),
                NavigationGroup::make(fn () => __('Clients')),
                NavigationGroup::make(fn () => __('Réglages')),
            ])
            // Sélecteur FR / العربية : barre supérieure et page de connexion.
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.language-switcher'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.language-switcher', ['centered' => true]))
            ->renderHook(PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER, fn () => view('filament.language-switcher', ['centered' => true]))
            // Inter ne contient pas l'arabe : Cairo, déjà utilisée par le site.
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.arabic-font'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            // Tableau de bord : bannière, indicateurs, graphique et dernières
            // demandes (app/Filament/Widgets). La bannière remplace le widget
            // « Compte » de Filament ; la déconnexion reste dans le menu.
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ])
            // Persistant : réappliqué aux requêtes Livewire du panneau, qui
            // afficheraient sinon les tableaux et formulaires dans la langue
            // du site vitrine.
            ->middleware([SetAdminLocale::class], isPersistent: true)
            ->middleware([
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
