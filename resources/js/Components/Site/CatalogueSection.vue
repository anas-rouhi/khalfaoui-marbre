<script setup>
import { computed, ref } from 'vue';
import { useCompany } from '@/Composables/useCompany';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps({
    /** Liste déjà résolue par la page (base de données ou données de démo). */
    products: { type: Array, default: () => [] },
    /**
     * Teintes administrées (Réglages › Teintes), dans l'ordre du back-office.
     * Format : [{ key, label, swatch }]
     */
    colorFamilies: { type: Array, default: () => [] },
    /**
     * Usages administrés (Réglages › Usages & tarifs).
     * Format : [{ key, label }]
     */
    applications: { type: Array, default: () => [] },
    /**
     * Familles de matière administrées (Catalogue › Familles de matière).
     * Format : [{ key, label }]
     */
    categories: { type: Array, default: () => [] },
});

const emit = defineEmits(['request-quote']);

const { whatsappLink } = useCompany();
const { t } = useI18n();

const items = computed(() => props.products);

/** Pastille par défaut d'une teinte inconnue de la liste administrée. */
const DEFAULT_SWATCH = '#8b8e94';

/** Traduction du dictionnaire si elle existe, la clé brute sinon. */
const translateOr = (key, fallback) => {
    const translated = t(key);

    return translated === key ? fallback : translated;
};

/**
 * Boutons d'un filtre : uniquement les valeurs portées par au moins une
 * fiche affichée, dans l'ordre du back-office. Une valeur présente sur une
 * fiche mais absente de la liste (supprimée depuis, ou données de démo)
 * reste filtrable, avec un libellé de repli.
 */
const buildOptions = (definitions, usedKeys, fallback) => {
    const known = new Set(definitions.map((definition) => definition.key));

    return [
        ...definitions.filter((definition) => usedKeys.has(definition.key)),
        ...[...usedKeys].filter((key) => !known.has(key)).map(fallback),
    ];
};

const applicationOptions = computed(() =>
    buildOptions(
        props.applications,
        new Set(items.value.flatMap((item) => item.applications ?? [])),
        (key) => ({ key, label: translateOr(`application.${key}`, key) }),
    ),
);

const colorOptions = computed(() =>
    buildOptions(
        props.colorFamilies,
        new Set(items.value.map((item) => item.colorFamily).filter(Boolean)),
        (key) => ({ key, label: translateOr(`colour.${key}`, key), swatch: DEFAULT_SWATCH }),
    ),
);

const familyOptions = computed(() =>
    buildOptions(
        props.categories,
        new Set(items.value.map((item) => item.categoryKey).filter(Boolean)),
        (key) => ({ key, label: translateOr(`family.${key}`, key) }),
    ),
);

const applicationFilters = computed(() => [
    { key: 'all', label: t('catalogue.all') },
    ...applicationOptions.value,
]);

const colorFilters = computed(() => [
    { key: 'all', label: t('catalogue.colours_all'), swatch: 'linear-gradient(135deg,#f5f5f5,#141519)' },
    ...colorOptions.value,
]);

const familyFilters = computed(() => [
    { key: 'all', label: t('catalogue.families_all') },
    ...familyOptions.value,
]);

const activeApplication = ref('all');
const activeColor = ref('all');
const activeFamily = ref('all');

// Une photo encore absente du serveur ne doit pas afficher d'image cassée :
// on bascule sur le visuel de remplacement.
const brokenImages = ref(new Set());
const hasImage = (product) => Boolean(product.image) && !brokenImages.value.has(product.id);
const onImageError = (product) => {
    brokenImages.value = new Set(brokenImages.value).add(product.id);
};

const filtered = computed(() =>
    items.value.filter((item) => {
        const matchesApplication =
            activeApplication.value === 'all' ||
            (item.applications ?? []).includes(activeApplication.value);

        const matchesColor = activeColor.value === 'all' || item.colorFamily === activeColor.value;

        const matchesFamily = activeFamily.value === 'all' || item.categoryKey === activeFamily.value;

        return matchesApplication && matchesColor && matchesFamily;
    }),
);

const resetFilters = () => {
    activeApplication.value = 'all';
    activeColor.value = 'all';
    activeFamily.value = 'all';
};

const applicationLabel = (key) =>
    applicationOptions.value.find((option) => option.key === key)?.label ?? key;

// Demande de disponibilité pré-remplie. Les fiches de démonstration n'ont
// pas de référence : le message s'en passe.
const productWhatsappLink = (product) =>
    whatsappLink(
        product.reference
            ? t('whatsapp.product', { name: product.name, reference: product.reference })
            : t('whatsapp.product_no_ref', { name: product.name }),
    );

// Même format dans les deux langues : chiffres occidentaux et espace comme
// séparateur de milliers. Le point serait lu comme une virgule décimale.
const formatPrice = (value) =>
    value == null ? null : new Intl.NumberFormat('fr-FR').format(value);
</script>

<template>
    <section id="catalogue" class="relative overflow-hidden bg-stone-50 py-24 sm:py-32 dark:bg-obsidian-950">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-600/30 to-transparent dark:via-champagne-300/40" aria-hidden="true" />

        <div class="mx-auto max-w-7xl px-6">
            <!-- En-tête de section -->
            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                <div class="max-w-2xl">
                    <p class="section-label">
                        <span class="section-rule" aria-hidden="true" />
                        {{ t('catalogue.label') }}
                    </p>
                    <h2 class="heading-display mt-6">{{ t('catalogue.title') }}</h2>
                    <p class="mt-5 text-base leading-relaxed text-stone-600 dark:text-obsidian-300">
                        {{ t('catalogue.lead') }}
                    </p>
                </div>

                <p class="shrink-0 text-sm text-stone-500 dark:text-obsidian-300">
                    <span class="font-display text-4xl font-light text-stone-900 dark:text-white">{{ filtered.length }}</span>
                    <span class="ms-2">{{ filtered.length > 1 ? t('catalogue.count_many') : t('catalogue.count_one') }}</span>
                </p>
            </div>

            <!-- Filtres -->
            <div class="mt-14 space-y-6" data-reveal data-reveal-delay="100">
                <div v-if="applicationOptions.length" class="flex flex-wrap items-center gap-2.5">
                    <span class="me-2 hidden text-xs uppercase tracking-[0.2em] text-stone-500 sm:inline dark:text-obsidian-300">{{ t('catalogue.filter_usage') }}</span>
                    <button
                        v-for="app in applicationFilters"
                        :key="app.key"
                        type="button"
                        class="rounded-full border px-5 py-2.5 text-sm font-medium transition-all duration-300"
                        :class="
                            activeApplication === app.key
                                ? 'border-brand-500 bg-brand-600 text-white shadow-brand-glow dark:border-brand-400 dark:bg-brand-600'
                                : 'chip'
                        "
                        :aria-pressed="activeApplication === app.key"
                        @click="activeApplication = app.key"
                    >
                        {{ app.label }}
                    </button>
                </div>

                <div v-if="colorOptions.length" class="flex flex-wrap items-center gap-2.5">
                    <span class="me-2 hidden text-xs uppercase tracking-[0.2em] text-stone-500 sm:inline dark:text-obsidian-300">{{ t('catalogue.filter_colour') }}</span>
                    <button
                        v-for="color in colorFilters"
                        :key="color.key"
                        type="button"
                        class="group flex items-center gap-2.5 rounded-full border py-2 ps-2 pe-5 text-sm font-medium transition-all duration-300"
                        :class="
                            activeColor === color.key
                                ? 'border-brand-500 bg-brand-50 text-stone-900 dark:border-brand-400 dark:bg-white/10 dark:text-white'
                                : 'chip'
                        "
                        :aria-pressed="activeColor === color.key"
                        @click="activeColor = color.key"
                    >
                        <span
                            class="h-5 w-5 rounded-full ring-1 ring-inset ring-stone-900/15 dark:ring-white/25"
                            :style="{ background: color.swatch }"
                            aria-hidden="true"
                        />
                        {{ color.label }}
                    </button>
                </div>

                <div v-if="familyOptions.length" class="flex flex-wrap items-center gap-2.5">
                    <span class="me-2 hidden text-xs uppercase tracking-[0.2em] text-stone-500 sm:inline dark:text-obsidian-300">{{ t('catalogue.filter_family') }}</span>
                    <button
                        v-for="family in familyFilters"
                        :key="family.key"
                        type="button"
                        class="rounded-full border px-5 py-2.5 text-sm font-medium transition-all duration-300"
                        :class="
                            activeFamily === family.key
                                ? 'border-brand-500 bg-brand-500/10 text-brand-800 dark:border-brand-400 dark:bg-brand-500/15 dark:text-brand-300'
                                : 'chip'
                        "
                        :aria-pressed="activeFamily === family.key"
                        @click="activeFamily = family.key"
                    >
                        {{ family.label }}
                    </button>
                </div>
            </div>

            <!-- Grille produits -->
            <TransitionGroup
                tag="div"
                class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                enter-active-class="transition duration-500 ease-out"
                enter-from-class="translate-y-6 opacity-0"
                leave-active-class="absolute transition duration-200 ease-in"
                leave-to-class="scale-95 opacity-0"
                move-class="transition duration-500"
            >
                <article
                    v-for="product in filtered"
                    :key="product.id"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm shadow-stone-900/5 transition-all duration-500 hover:-translate-y-1.5 hover:border-brand-500/40 hover:shadow-stone dark:border-white/10 dark:bg-obsidian-900/60 dark:shadow-none dark:hover:border-champagne-300/40 dark:hover:shadow-luxe"
                >
                    <div class="relative aspect-[4/3] overflow-hidden bg-stone-200 dark:bg-obsidian-800">
                        <img
                            v-if="hasImage(product)"
                            :src="product.image"
                            :alt="`${product.name} — ${product.color}`"
                            width="1200"
                            height="900"
                            class="h-full w-full object-cover transition-transform duration-[900ms] ease-out group-hover:scale-110"
                            loading="lazy"
                            decoding="async"
                            @error="onImageError(product)"
                        />
                        <div
                            v-else
                            class="grid h-full w-full place-items-center bg-gradient-to-br from-stone-200 to-stone-300 text-xs uppercase tracking-[0.2em] text-stone-500 dark:from-obsidian-700 dark:to-obsidian-900 dark:text-obsidian-300"
                        >
                            {{ t('catalogue.photo_soon') }}
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-stone-900/25 via-transparent to-transparent opacity-80 transition-opacity dark:from-obsidian-950 dark:via-obsidian-950/10 duration-500 group-hover:opacity-95" aria-hidden="true" />

                        <span
                            v-if="product.finish"
                            class="absolute start-4 top-4 rounded-full border border-white/60 bg-white/85 px-3 py-1 text-[0.65rem] uppercase tracking-[0.16em] text-stone-800 backdrop-blur dark:border-white/20 dark:bg-obsidian-950/70 dark:text-obsidian-200"
                        >
                            {{ product.finish }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="font-display text-xl font-medium text-stone-900 dark:text-white">{{ product.name }}</h3>
                        <p class="mt-1 text-sm text-brand-700 dark:text-brand-400">{{ product.color }}</p>

                        <p v-if="product.description" class="mt-3 line-clamp-2 text-sm leading-relaxed text-stone-600 dark:text-obsidian-300">
                            {{ product.description }}
                        </p>

                        <ul class="mt-4 flex flex-wrap gap-1.5">
                            <li
                                v-for="app in product.applications ?? []"
                                :key="app"
                                class="rounded-md bg-stone-100 px-2.5 py-1 text-[0.68rem] tracking-wide text-stone-600 dark:bg-white/5 dark:text-obsidian-300"
                            >
                                {{ applicationLabel(app) }}
                            </li>
                        </ul>

                        <div class="mt-5 flex items-end justify-between gap-3 border-t border-stone-100 pt-4 dark:border-white/5">
                            <div>
                                <p v-if="product.pricePerM2" class="font-display text-lg text-stone-900 dark:text-white">
                                    {{ formatPrice(product.pricePerM2) }}
                                    <span class="text-xs font-sans text-stone-500 dark:text-obsidian-300">{{ t('catalogue.per_m2') }}</span>
                                </p>
                                <p v-else class="text-sm text-stone-500 dark:text-obsidian-300">{{ t('catalogue.price_on_request') }}</p>
                                <p v-if="product.origin" class="mt-0.5 text-[0.68rem] uppercase tracking-[0.14em] text-stone-500 dark:text-obsidian-300">
                                    {{ t('catalogue.origin') }} {{ product.origin }}
                                </p>
                                <!-- Badge de référence (KM-xxx), à citer au téléphone. -->
                                <p
                                    v-if="product.reference"
                                    class="mt-2 inline-flex items-center gap-1 rounded-md border border-stone-200 bg-stone-50 px-2 py-0.5 text-[0.66rem] tracking-[0.12em] text-stone-600 dark:border-champagne-300/25 dark:bg-champagne-300/5 dark:text-champagne-200"
                                >
                                    {{ t('catalogue.reference') }} <span dir="ltr" class="font-semibold">{{ product.reference }}</span>
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <a
                                    :href="productWhatsappLink(product)"
                                    target="_blank"
                                    rel="noopener"
                                    class="grid h-9 w-9 place-items-center rounded-full bg-brand-600 text-white transition hover:bg-brand-700 hover:shadow-brand-glow dark:bg-brand-600 dark:hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400"
                                    :aria-label="`${t('catalogue.whatsapp')} — ${product.name}`"
                                    :title="t('catalogue.whatsapp')"
                                >
                                    <svg class="h-[1.1rem] w-[1.1rem]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.33 4.97L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91a9.85 9.85 0 0 0-9.91-9.93Zm5.43 12.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.3-.02-.46.13-.6.14-.14.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35Z" />
                                    </svg>
                                </a>

                                <button
                                    type="button"
                                    class="rounded-full border border-brand-600/40 px-4 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-600 hover:text-white dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-600"
                                    @click="emit('request-quote', product)"
                                >
                                    {{ t('catalogue.quote') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </TransitionGroup>

            <!-- État vide -->
            <div v-if="!filtered.length" class="mt-16 rounded-2xl border border-dashed border-stone-300 py-20 text-center dark:border-white/15">
                <p class="text-stone-600 dark:text-obsidian-300">{{ t('catalogue.empty_title') }}</p>
                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <button type="button" class="btn-ghost" @click="resetFilters">{{ t('catalogue.empty_reset') }}</button>
                    <a
                        :href="whatsappLink(t('whatsapp.advice'))"
                        target="_blank"
                        rel="noopener"
                        class="btn-primary"
                    >
                        {{ t('catalogue.empty_advice') }}
                    </a>
                </div>
            </div>

            <p class="mt-14 text-center text-sm text-stone-500 dark:text-obsidian-300" data-reveal>
                {{ t('catalogue.not_found') }}
                <a
                    :href="whatsappLink(t('whatsapp.not_found'))"
                    target="_blank"
                    rel="noopener"
                    class="text-brand-700 underline-offset-4 transition hover:underline dark:text-brand-400"
                >
                    {{ t('catalogue.not_found_link') }}
                </a>.
            </p>
        </div>
    </section>
</template>
