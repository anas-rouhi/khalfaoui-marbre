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
    | - « logo_on_light » : même signature, lettrage foncé, pour le mode clair
    |   du site public.
    |
    | « logo » et « logo_mark » sont dessinés en clair : ils se posent sur un
    | fond sombre. L'emblème garde son disque sombre, lisible partout.
    */
    'logo' => '/images/brand/khalfaoui-marbre-logo.svg',
    'logo_on_light' => '/images/brand/khalfaoui-marbre-logo-on-light.svg',
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

    // Destinataire des alertes « nouvelle demande de devis ». Par défaut
    // l'adresse publique ; surchargeable sans toucher au code.
    'notification_email' => env('DEVIS_NOTIFICATION_EMAIL', 'KHALFAOUI-MARBRE@hotmail.com'),

    // `key` / `value_key` renvoient au dictionnaire de traduction ; `days` et
    // `time` restent le texte affiché à défaut de traduction.
    'hours' => [
        ['key' => 'hours.weekdays', 'days' => 'Lundi — Vendredi', 'time' => '08:00 — 19:00'],
        ['key' => 'hours.saturday', 'days' => 'Samedi', 'time' => '08:00 — 17:00'],
        ['key' => 'hours.sunday', 'days' => 'Dimanche', 'value_key' => 'hours.sunday_value', 'time' => 'Sur rendez-vous'],
    ],

    // Mêmes horaires au format Schema.org (données structurées Google).
    // Le dimanche, ouvert sur rendez-vous seulement, n'y figure pas.
    'opening_hours' => [
        ['days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '08:00', 'closes' => '19:00'],
        ['days' => ['Saturday'], 'opens' => '08:00', 'closes' => '17:00'],
    ],

    // Coordonnées du dépôt, utilisées pour l'intégration Google Maps.
    'geo' => [
        'lat' => 33.5395,
        'lng' => -7.5100,
    ],
];
