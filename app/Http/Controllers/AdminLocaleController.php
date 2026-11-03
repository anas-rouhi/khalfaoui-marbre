<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetAdminLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminLocaleController extends Controller
{
    /**
     * Mémorise la langue du back-office puis revient sur la page consultée.
     *
     * Accessible aussi depuis la page de connexion : le choix ne concerne que
     * la session du navigateur, et la valeur est vérifiée contre la liste des
     * langues prises en charge.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(SetAdminLocale::isSupported($locale), 404);

        $request->session()->put(SetAdminLocale::SESSION_KEY, $locale);

        // Retour à la page d'origine, à condition qu'elle soit dans le
        // back-office : un en-tête Referer forgé ne doit pas rediriger ailleurs.
        $previous = url()->previous();
        $admin = url('/admin');

        return redirect()->to(str_starts_with($previous, $admin) ? $previous : $admin);
    }
}
