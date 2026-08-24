<?php

/*
|--------------------------------------------------------------------------
| Informations de l'entreprise
|--------------------------------------------------------------------------
|
| Source unique de vérité pour les coordonnées réelles de KHALFAOUI MARBRE.
| Partagé avec le front-end via HandleInertiaRequests::share().
|
*/

return [
    'name' => 'KHALFAOUI MARBRE',
    'legal_name' => 'KHALFAOUI MARBRE S.A.R.L',
    'tagline' => 'Travaux de bâtiment tous corps d\'état · Vente de toutes sortes de marbre et granit',

    /*
    | Identité visuelle
    |
    | Source unique pour le site public ET le back-office Filament : remplacer
    | un fichier ci-dessous suffit à mettre le logo à jour partout.
    |
    | - « logo » : signature complète (emblème + KHALFAOUI MARBRE S.A.R.L),
    |   utilisée dans l'en-tête, le pied de page et la page de connexion admin.
    | - « logo_mark » : emblème seul, pour les espaces étroits (mobile, favicon).
    |
    | Les deux fichiers sont dessinés en clair : ils se posent sur un fond
    | sombre. Sur un fond clair, les composants les placent sur un socle sombre.
    */
    'logo' => '/images/brand/khalfaoui-marbre-logo.svg',
    'logo_mark' => '/images/brand/khalfaoui-marbre-mark.svg',

    'address' => 'Route 1033 Lahraouiyine, Casablanca',
    'address_short' => 'Route 1033 Lahraouiyine — Casablanca',

    // Format lisible et format E.164 pour les liens tel:/wa.me
    // 'phone_display' => '+212 661-219409',
    'phone_display' => '+212 617427729',
    // 'phone_e164' => '+212661219409',
    'phone_e164' => '+212617427729',
    // 'whatsapp' => '212661219409',
    'whatsapp' => '212617427729',

    'email' => 'KHALFAOUI-MARBRE@hotmail.com',

    // `key` / `value_key` renvoient au dictionnaire de traduction ; `days` et
    // `time` restent le texte affiché à défaut de traduction.
    'hours' => [
        ['key' => 'hours.weekdays', 'days' => 'Lundi — Vendredi', 'time' => '08:00 — 19:00'],
        ['key' => 'hours.saturday', 'days' => 'Samedi', 'time' => '08:00 — 17:00'],
        ['key' => 'hours.sunday', 'days' => 'Dimanche', 'value_key' => 'hours.sunday_value', 'time' => 'Sur rendez-vous'],
    ],

    // Coordonnées du dépôt, utilisées pour l'intégration Google Maps.
    'geo' => [
        'lat' => 33.5395,
        'lng' => -7.5100,
    ],
];
