{{--
    Police du back-office en arabe : Inter (police par défaut de Filament) ne
    contient pas les glyphes arabes. Cairo est celle du site vitrine.
    Posé en fin de <head>, ce style remplace la variable définie par Filament.
--}}
@if (app()->getLocale() === 'ar')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        :root { --font-family: 'Cairo', 'Inter', sans-serif; }
    </style>
@endif
