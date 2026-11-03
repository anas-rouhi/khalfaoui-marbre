{{--
    Sélecteur de langue du back-office : barre supérieure et page de connexion.
    Les boutons Filament suivent le thème (clair / sombre) du panneau.
--}}
@php
    $languages = [
        'fr' => ['flag' => '🇫🇷', 'label' => 'FR'],
        'ar' => ['flag' => '🇲🇦', 'label' => 'العربية'],
    ];
    $current = app()->getLocale();
@endphp

<div
    @class(['flex items-center gap-x-1', 'justify-center' => $centered ?? false])
    role="group"
    aria-label="{{ __('Langue de l\'interface') }}"
>
    @foreach ($languages as $code => $language)
        <x-filament::button
            tag="a"
            :href="route('admin.locale', $code)"
            size="xs"
            :color="$current === $code ? 'primary' : 'gray'"
            :outlined="$current !== $code"
            :aria-current="$current === $code ? 'true' : null"
            :lang="$code"
        >
            <span aria-hidden="true">{{ $language['flag'] }}</span>
            {{ $language['label'] }}
        </x-filament::button>
    @endforeach
</div>
