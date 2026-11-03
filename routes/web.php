<?php

use App\Http\Controllers\AdminLocaleController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// L'authentification passe exclusivement par le back-office Filament (/admin) :
// le site public n'expose aucune page de connexion ni d'inscription.

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/langue/{locale}', LocaleController::class)->name('locale.switch');

// Langue du back-office, distincte de celle du site (voir SetAdminLocale).
Route::get('/admin/locale/{locale}', AdminLocaleController::class)->name('admin.locale');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Généré plutôt que statique : la directive Sitemap exige une URL absolue,
// qui suit ainsi le domaine réel (démo, préproduction, production).
Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /devis',
        'Disallow: /langue/',
        '',
        'Sitemap: '.route('sitemap'),
    ];

    return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

Route::post('/devis', [DevisController::class, 'store'])
    ->middleware('throttle:devis')
    ->name('devis.store');

Route::post('/devis/pdf', [DevisController::class, 'pdf'])
    ->middleware('throttle:devis')
    ->name('devis.pdf');
