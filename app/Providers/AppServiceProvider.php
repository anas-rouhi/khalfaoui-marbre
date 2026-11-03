<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Traductions du back-office : le texte français sert de clé
        // (`__('Nom commercial')`), lang/admin/ar.json donne l'arabe. Fichier
        // séparé de lang/ar.json, que le site envoie en entier au navigateur.
        $this->app['translator']->addJsonPath(lang_path('admin'));

        // En production, toutes les URL générées passent en HTTPS. Derrière un
        // proxy ou un tunnel, le schéma réel est déjà détecté grâce à
        // `trustProxies` (bootstrap/app.php).
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Formulaire de devis et PDF : chaque appel crée une fiche et le PDF
        // sollicite fortement le serveur. Un visiteur réel n'en a besoin que
        // de quelques-uns ; au-delà, c'est un robot.
        RateLimiter::for('devis', fn (Request $request) => [
            Limit::perMinute(5)->by('devis-minute:'.$request->ip()),
            Limit::perDay(30)->by('devis-day:'.$request->ip()),
        ]);
    }
}
