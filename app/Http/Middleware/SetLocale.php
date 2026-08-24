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

        $locale = $request->session()->get('locale');

        if (is_string($locale) && array_key_exists($locale, config('localization.supported'))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
