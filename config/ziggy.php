<?php

/*
|--------------------------------------------------------------------------
| Routes exposées au JavaScript (directive @routes et resources/js/ziggy.js)
|--------------------------------------------------------------------------
|
| Liste blanche : seules les routes utilisées par le site public sont
| publiées dans le code source de la page. Celles du back-office Filament
| n'ont pas à y figurer.
|
*/

return [
    'only' => ['home', 'locale.switch', 'devis.*'],
];
