<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Langue du back-office Filament, indépendante de celle du site vitrine.
 *
 * Le choix vit sous sa propre clé de session (`admin_locale`, contre
 * `locale` pour le site) : basculer l'un ne touche jamais l'autre. Le
 * middleware est déclaré « persistant » dans le panneau, il s'applique donc
 * aussi aux requêtes Livewire (filtres, actions, enregistrements), qui
 * passent sinon par le groupe `web` et prendraient la langue du site.
 *
 * L'arabe bascule l'interface en RTL : Filament lit la direction dans sa
 * propre traduction (`filament-panels::layout.direction`).
 */
class SetAdminLocale
{
    public const SESSION_KEY = 'admin_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null;

        if (! self::isSupported($locale)) {
            $locale = config('localization.default', 'fr');
        }

        App::setLocale($locale);
        // Dates relatives des tableaux (« il y a 2 heures » / « منذ ساعتين »).
        Carbon::setLocale($locale);

        return $next($request);
    }

    public static function isSupported(mixed $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, config('localization.supported'));
    }
}
