import { readonly, ref } from 'vue';

/**
 * Mode clair / mode sombre du site public.
 *
 * Le thème initial est appliqué avant le premier rendu par le script en
 * tête de app.blade.php (sans quoi la page clignoterait) : on se contente
 * ici de le relire sur <html>, puis de le faire évoluer.
 *
 * L'état est partagé au niveau du module : tous les boutons de bascule
 * (navbar, menu mobile) restent synchronisés.
 */

/** Même clé que le script anti-flash de app.blade.php. */
export const THEME_STORAGE_KEY = 'km-theme';

/** Couleur de la barre d'adresse mobile, assortie au fond de chaque mode. */
const THEME_COLORS = { dark: '#0a0b0d', light: '#fafaf9' };

const hasDocument = typeof document !== 'undefined';

const isDark = ref(hasDocument ? document.documentElement.classList.contains('dark') : true);

// Le stockage peut être indisponible (navigation privée, cookies bloqués) :
// le thème fonctionne alors, simplement sans être mémorisé.
const readStoredTheme = () => {
    try {
        return window.localStorage.getItem(THEME_STORAGE_KEY);
    } catch {
        return null;
    }
};

const storeTheme = (theme) => {
    try {
        window.localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch {
        // Choix conservé pour la visite en cours seulement.
    }
};

const apply = (dark) => {
    isDark.value = dark;

    const root = document.documentElement;
    root.classList.toggle('dark', dark);
    root.style.colorScheme = dark ? 'dark' : 'light';

    document.querySelector('meta[name="theme-color"]')?.setAttribute(
        'content',
        dark ? THEME_COLORS.dark : THEME_COLORS.light,
    );
};

/**
 * Fondu des couleurs pendant la bascule uniquement : une transition posée
 * en permanence sur tous les éléments ralentirait chaque survol du site.
 */
let transitionTimer = null;
const withTransition = (callback) => {
    const root = document.documentElement;

    root.classList.add('theme-transition');
    callback();

    clearTimeout(transitionTimer);
    transitionTimer = setTimeout(() => root.classList.remove('theme-transition'), 400);
};

// Tant que le visiteur n'a rien choisi, le site suit le réglage système,
// y compris s'il change en cours de visite (bascule automatique au coucher
// du soleil, par exemple). Le drapeau couvre le cas où le choix n'a pas pu
// être enregistré.
let chosenThisVisit = false;
if (hasDocument && typeof window.matchMedia === 'function') {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', (event) => {
        if (!chosenThisVisit && !readStoredTheme()) {
            withTransition(() => apply(event.matches));
        }
    });
}

export function useTheme() {
    const setTheme = (theme) => {
        chosenThisVisit = true;
        storeTheme(theme);
        withTransition(() => apply(theme === 'dark'));
    };

    const toggleTheme = () => setTheme(isDark.value ? 'light' : 'dark');

    return { isDark: readonly(isDark), setTheme, toggleTheme };
}
