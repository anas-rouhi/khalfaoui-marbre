<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hébergement derrière un proxy (Cloudflare, load balancer, tunnel) :
        // le schéma HTTPS et l'IP du visiteur sont lus dans les en-têtes
        // X-Forwarded-*. Sans cela, le rate limiting verrait l'IP du proxy.
        $middleware->trustProxies(at: '*');

        // Global : couvre aussi le back-office Filament et le sitemap.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->web(append: [
            // Avant Inertia : les props partagées (traductions, direction)
            // doivent être calculées dans la langue déjà sélectionnée.
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
