<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Mémorise la langue choisie puis revient sur la page consultée.
     *
     * Les URL sont identiques dans les deux langues : la préférence vit en
     * session, ce qui évite de dupliquer l'arborescence du site.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('localization.supported')), 404);

        $request->session()->put('locale', $locale);

        return back();
    }
}
