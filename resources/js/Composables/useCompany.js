import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Coordonnées réelles de l'entreprise, partagées depuis config/company.php.
 * Les valeurs de repli permettent d'afficher les composants isolément
 * (Storybook, tests) sans props Inertia.
 */
const FALLBACK = {
    name: 'KHALFAOUI MARBRE',
    legal_name: 'KHALFAOUI MARBRE S.A.R.L',
    tagline: "Travaux de bâtiment tous corps d'état · Vente de toutes sortes de marbre et granit",
    logo: '/images/brand/khalfaoui-marbre-logo.svg',
    logo_mark: '/images/brand/khalfaoui-marbre-mark.svg',
    address: 'Route 1033 Lahraouiyine, Casablanca',
    address_short: 'Route 1033 Lahraouiyine — Casablanca',
    phone_display: '+212 617427729',
    phone_e164: '+212617427729',
    whatsapp: '212617427729',
    email: 'KHALFAOUI-MARBRE@hotmail.com',
    hours: [
        { days: 'Lundi — Vendredi', time: '08:00 — 19:00' },
        { days: 'Samedi', time: '08:00 — 17:00' },
        { days: 'Dimanche', time: 'Sur rendez-vous' },
    ],
    geo: { lat: 33.5395, lng: -7.51 },
};

export function useCompany() {
    const page = usePage();

    const company = computed(() => ({ ...FALLBACK, ...(page.props.company ?? {}) }));

    const telLink = computed(() => `tel:${company.value.phone_e164}`);
    const mailLink = computed(() => `mailto:${company.value.email}`);

    /** Construit un lien wa.me avec un message pré-rempli. */
    const whatsappLink = (message = '') =>
        `https://wa.me/${company.value.whatsapp}${message ? `?text=${encodeURIComponent(message)}` : ''}`;

    const mapsQuery = computed(() =>
        encodeURIComponent(`${company.value.legal_name}, ${company.value.address}`),
    );

    const mapsEmbedUrl = computed(() => `https://www.google.com/maps?q=${mapsQuery.value}&hl=fr&z=15&output=embed`);

    const mapsDirectionsUrl = computed(
        () => `https://www.google.com/maps/dir/?api=1&destination=${mapsQuery.value}`,
    );

    return { company, telLink, mailLink, whatsappLink, mapsEmbedUrl, mapsDirectionsUrl };
}
