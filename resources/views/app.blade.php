@php
    $locale = app()->getLocale();
    $meta = config('localization.supported.'.$locale, config('localization.supported.fr'));
    $isRtl = ($meta['dir'] ?? 'ltr') === 'rtl';

    // Même format que le titre posé par Inertia (resources/js/app.js).
    $title = __('seo.title').' - '.config('app.name');
    $description = __('seo.description');
    $canonical = \App\Support\Seo::canonical();
    $shareImage = asset(\App\Support\Seo::SHARE_IMAGE);
    $ogLocales = ['fr' => 'fr_MA', 'ar' => 'ar_MA'];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $meta['dir'] ?? 'ltr' }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0a0b0d">

        {{-- Thème clair / sombre posé avant tout rendu, sans quoi la page
             s'afficherait un instant dans le mauvais mode. Choix mémorisé du
             visiteur d'abord, réglage système ensuite, sombre en dernier
             recours. La suite est gérée par resources/js/Composables/useTheme.js. --}}
        <script>
            (function () {
                var stored = null;
                try { stored = localStorage.getItem('km-theme'); } catch (e) {}
                var dark = stored === 'dark' || stored === 'light'
                    ? stored === 'dark'
                    : !window.matchMedia || !window.matchMedia('(prefers-color-scheme: light)').matches;
                var root = document.documentElement;
                root.classList.toggle('dark', dark);
                root.style.colorScheme = dark ? 'dark' : 'light';
                if (!dark) {
                    document.querySelector('meta[name="theme-color"]').setAttribute('content', '#fafaf9');
                }
            })();
        </script>

        <title inertia>{{ $title }}</title>

        <meta name="description" content="{{ $description }}">
        <meta name="author" content="KHALFAOUI MARBRE S.A.R.L">

        {{-- Une URL par langue : les moteurs indexent ainsi la version arabe. --}}
        <link rel="canonical" href="{{ $canonical }}">
        @foreach (\App\Support\Seo::alternates() as $hreflang => $href)
            <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
        @endforeach

        {{-- Aperçu lors d'un partage (WhatsApp, Facebook, LinkedIn, X). --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="KHALFAOUI MARBRE S.A.R.L">
        <meta property="og:locale" content="{{ $ogLocales[$locale] ?? 'fr_MA' }}">
        @foreach ($ogLocales as $code => $ogLocale)
            @if ($code !== $locale)
                <meta property="og:locale:alternate" content="{{ $ogLocale }}">
            @endif
        @endforeach
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $shareImage }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="{{ \App\Support\Seo::SHARE_IMAGE_WIDTH }}">
        <meta property="og:image:height" content="{{ \App\Support\Seo::SHARE_IMAGE_HEIGHT }}">
        <meta property="og:image:alt" content="{{ __('hero.image_alt') }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $shareImage }}">

        <script type="application/ld+json">{!! json_encode(\App\Support\Seo::localBusiness(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

        <link rel="icon" href="/favicon.ico" sizes="any">

        {{-- Image du hero : téléchargée en priorité, avant même le JavaScript
             qui l'affiche (plus grand élément visible, donc LCP). --}}
        <link rel="preload" as="image" type="image/webp" href="/images/hero/hero-cuisine-granit.webp" fetchpriority="high">

        {{-- Polices : Cairo couvre l'arabe, qu'Inter et Cormorant Garamond
             ne prennent pas en charge. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:300,400,500,600|inter:300,400,500,600,700{{ $isRtl ? '|cairo:300,400,600,700' : '' }}&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="bg-stone-50 antialiased dark:bg-obsidian-950 {{ $isRtl ? 'font-arabic' : 'font-sans' }}">
        @inertia
    </body>
</html>
