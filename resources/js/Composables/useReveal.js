import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Révèle les éléments portant l'attribut `data-reveal` lorsqu'ils entrent dans
 * le viewport, en leur ajoutant la classe `is-revealed`.
 *
 * Utiliser `data-reveal-delay="150"` pour décaler une apparition.
 *
 * La détection se fait par mesure directe plutôt que via IntersectionObserver :
 * un saut de défilement instantané (lien d'ancre, `scrollIntoView`, restauration
 * de position) peut ne pas déclencher l'observateur et laisserait alors du
 * contenu définitivement invisible.
 */
export function useReveal(rootRef, { visibleRatio = 0.12 } = {}) {
    let targets = [];
    let ticking = false;

    const reveal = (el) => {
        const delay = Number(el.dataset.revealDelay ?? 0);
        window.setTimeout(() => el.classList.add('is-revealed'), delay);
    };

    const check = () => {
        ticking = false;

        const viewportHeight = window.innerHeight;

        targets = targets.filter((el) => {
            const { top, height } = el.getBoundingClientRect();

            // Marge de déclenchement : l'élément doit avoir dépassé le bas de
            // l'écran d'une fraction de sa hauteur pour s'animer.
            const trigger = viewportHeight - Math.min(height * visibleRatio, 120);

            if (top < trigger && top + height > 0) {
                reveal(el);
                return false;
            }

            return true;
        });

        if (!targets.length) {
            stop();
        }
    };

    const onScroll = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(check);
    };

    const stop = () => {
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', onScroll);
    };

    onMounted(() => {
        const root = rootRef?.value ?? document;
        targets = Array.from(root.querySelectorAll('[data-reveal]'));

        if (!targets.length) {
            return;
        }

        // En mode « animations réduites », tout est affiché immédiatement.
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            targets.forEach((el) => el.classList.add('is-revealed'));
            targets = [];
            return;
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });

        check();
    });

    onBeforeUnmount(stop);
}
