<?php

/*
|--------------------------------------------------------------------------
| Langues du site public
|--------------------------------------------------------------------------
|
| `dir` pilote l'attribut dir="" du document, `font` la famille de caractères
| appliquée au corps de page (l'arabe n'est pas couvert par Inter ni par
| Cormorant Garamond).
|
| La langue choisie est mémorisée en session. Chaque langue dispose aussi
| d'une URL propre (`/?lang=ar`), déclarée aux moteurs via hreflang.
|
*/

return [
    'default' => 'fr',

    'supported' => [
        'fr' => [
            'label' => 'Français',
            'short' => 'FR',
            'native' => 'Français',
            'dir' => 'ltr',
            'font' => 'sans',
        ],
        'ar' => [
            'label' => 'Arabe',
            'short' => 'AR',
            'native' => 'العربية',
            'dir' => 'rtl',
            'font' => 'arabic',
        ],
    ],
];
