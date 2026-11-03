<?php

namespace App\Support;

/**
 * Métadonnées de référencement partagées par le gabarit racine et le sitemap.
 */
class Seo
{
    /** Visuel de partage (WhatsApp, Facebook) : JPEG pour la compatibilité, < 300 Ko. */
    public const SHARE_IMAGE = 'images/hero/hero-cuisine-granit.jpg';

    public const SHARE_IMAGE_WIDTH = 1920;

    public const SHARE_IMAGE_HEIGHT = 1280;

    /**
     * URL de la page d'accueil dans chaque langue (hreflang).
     *
     * @return array<string, string>
     */
    public static function alternates(): array
    {
        // « https://site/ » plutôt que « https://site » : la variante arabe
        // devient « https://site/?lang=ar ».
        $home = rtrim(route('home'), '/').'/';
        $default = config('localization.default', 'fr');

        $urls = [];

        foreach (array_keys(config('localization.supported')) as $locale) {
            $urls[$locale] = $locale === $default ? $home : $home.'?lang='.$locale;
        }

        $urls['x-default'] = $home;

        return $urls;
    }

    public static function canonical(): string
    {
        $alternates = static::alternates();

        return $alternates[app()->getLocale()] ?? $alternates['x-default'];
    }

    /**
     * Fiche entreprise au format Schema.org (JSON-LD), lue par Google pour
     * le panneau d'informations et la recherche locale.
     *
     * @return array<string, mixed>
     */
    public static function localBusiness(): array
    {
        $company = config('company');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'HomeGoodsStore',
            '@id' => static::alternates()['x-default'].'#entreprise',
            'name' => $company['name'],
            'legalName' => $company['legal_name'],
            'description' => __('seo.description'),
            'url' => static::alternates()['x-default'],
            'logo' => asset(ltrim($company['logo'], '/')),
            'image' => asset(self::SHARE_IMAGE),
            'telephone' => $company['phone_e164'],
            'email' => $company['email'],
            'priceRange' => '$$',
            'currenciesAccepted' => 'MAD',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Route 1033 Lahraouiyine',
                'addressLocality' => 'Casablanca',
                'addressRegion' => 'Casablanca-Settat',
                'addressCountry' => 'MA',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $company['geo']['lat'],
                'longitude' => $company['geo']['lng'],
            ],
            'areaServed' => ['@type' => 'City', 'name' => 'Casablanca'],
            'openingHoursSpecification' => collect($company['opening_hours'] ?? [])
                ->map(fn (array $slot) => [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $slot['days'],
                    'opens' => $slot['opens'],
                    'closes' => $slot['closes'],
                ])
                ->all(),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $company['phone_e164'],
                'contactType' => 'customer service',
                'availableLanguage' => ['French', 'Arabic'],
            ],
        ];
    }
}
