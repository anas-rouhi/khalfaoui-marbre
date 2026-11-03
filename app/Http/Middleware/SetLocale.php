<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Applique la langue mémorisée en session.
     *
     * La valeur est systématiquement vérifiée contre la liste des langues
     * prises en charge : une session forgée ne peut pas imposer un locale
     * arbitraire au traducteur.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Le back-office reste en français : ses libellés métier y sont écrits
        // en dur, une bascule ne donnerait qu'une interface à moitié traduite.
        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        // `?lang=ar` donne à chaque langue une URL propre, indispensable pour
        // que les moteurs de recherche (qui n'ont pas de session) indexent la
        // version arabe. Le choix est mémorisé pour la suite de la visite.
        $requested = $request->query('lang');

        if ($this->isSupported($requested)) {
            $request->session()->put('locale', $requested);
        }

        $locale = $request->session()->get('locale');

        if ($this->isSupported($locale)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function isSupported(mixed $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, config('localization.supported'));
    }
}
