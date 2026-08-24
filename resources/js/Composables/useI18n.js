import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Traduction côté navigateur.
 *
 * Le dictionnaire complet est partagé par HandleInertiaRequests : `t()` ne
 * fait qu'une lecture en mémoire, sans appel réseau.
 */
export function useI18n() {
    const page = usePage();

    const i18n = computed(() => page.props.i18n ?? {});
    const locale = computed(() => i18n.value.locale ?? 'fr');
    const direction = computed(() => i18n.value.direction ?? 'ltr');
    const isRtl = computed(() => direction.value === 'rtl');
    const available = computed(() => i18n.value.available ?? []);

    /**
     * @param {string} key clé du dictionnaire, ex. « nav.home »
     * @param {Object} replacements valeurs à injecter, ex. { number: 3 }
     */
    const t = (key, replacements = {}) => {
        let message = i18n.value.messages?.[key];

        // Une clé absente s'affiche telle quelle : le manque saute aux yeux
        // en relecture plutôt que de laisser un blanc silencieux.
        if (typeof message !== 'string') {
            return key;
        }

        for (const [name, value] of Object.entries(replacements)) {
            message = message.replaceAll(`:${name}`, String(value));
        }

        return message;
    };

    /**
     * Bascule de langue.
     *
     * Navigation complète, et non visite Inertia : l'attribut `dir`, la classe
     * de police du `body` et la feuille de style arabe sont posés par le
     * gabarit Blade, que seule une vraie recharge régénère.
     */
    const switchLocale = (code) => {
        if (code === locale.value) {
            return;
        }

        window.location.assign(`/langue/${code}`);
    };

    return { t, locale, direction, isRtl, available, switchLocale };
}
