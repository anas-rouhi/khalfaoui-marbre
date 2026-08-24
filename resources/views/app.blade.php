@php
    $locale = app()->getLocale();
    $meta = config('localization.supported.'.$locale, config('localization.supported.fr'));
    $isRtl = ($meta['dir'] ?? 'ltr') === 'rtl';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $meta['dir'] ?? 'ltr' }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0a0b0d">

        <title inertia>{{ config('app.name', 'KHALFAOUI MARBRE') }}</title>

        <meta name="description" content="{{ $isRtl
            ? 'خلفاوي رخام ش.م.م — بيع وتركيب الرخام والغرانيت بالدار البيضاء. أسطح مطابخ، أرضيات، أدراج، حمامات وواجهات. الطريق 1033 الحراويين.'
            : 'KHALFAOUI MARBRE S.A.R.L — Vente et pose de marbre et granit à Casablanca. Plans de travail, sols, escaliers, salles de bain et façades. Route 1033 Lahraouiyine.' }}">
        <meta name="author" content="KHALFAOUI MARBRE S.A.R.L">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="KHALFAOUI MARBRE S.A.R.L">
        <meta property="og:locale" content="{{ $isRtl ? 'ar_MA' : 'fr_MA' }}">

        <link rel="icon" href="/favicon.ico" sizes="any">

        {{-- Polices : Cairo couvre l'arabe, qu'Inter et Cormorant Garamond
             ne prennent pas en charge. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:300,400,500,600|inter:300,400,500,600,700{{ $isRtl ? '|cairo:300,400,600,700' : '' }}&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="bg-obsidian-950 antialiased {{ $isRtl ? 'font-arabic' : 'font-sans' }}">
        @inertia
    </body>
</html>
