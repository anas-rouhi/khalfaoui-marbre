<script setup>
import { computed, ref } from 'vue';
import { useCompany } from '@/Composables/useCompany';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps({
    /** Liste déjà résolue par la page (base de données ou données de démo). */
    products: { type: Array, default: () => [] },
});

const emit = defineEmits(['request-quote']);

const { whatsappLink } = useCompany();
const { t } = useI18n();

const items = computed(() => props.products);

const APPLICATIONS = [
    { key: 'all', label: 'catalogue.all' },
    { key: 'sol', label: 'application.sol' },
    { key: 'cuisine', label: 'application.cuisine_short' },
    { key: 'salle-de-bain', label: 'application.salle-de-bain' },
    { key: 'facade', label: 'application.facade' },
    { key: 'escalier', label: 'application.escalier' },
];

const COLORS = [
    { key: 'all', label: 'catalogue.colours_all', swatch: 'linear-gradient(135deg,#f5f5f5,#141519)' },
    { key: 'noir', label: 'colour.noir', swatch: '#141519' },
    { key: 'blanc', label: 'colour.blanc', swatch: '#eceae5' },
    { key: 'gris', label: 'colour.gris', swatch: '#8b8e94' },
    { key: 'beige', label: 'colour.beige', swatch: '#cbb094' },
    { key: 'brun', label: 'colour.brun', swatch: '#6b4a33' },
    { key: 'vert', label: 'colour.vert', swatch: '#1e9d6b' },
];

const activeApplication = ref('all');
const activeColor = ref('all');

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

        return matchesApplication && matchesColor;
    }),
);

const resetFilters = () => {
    activeApplication.value = 'all';
    activeColor.value = 'all';
};

const applicationLabel = (key) => {
    const found = APPLICATIONS.find((a) => a.key === key);

    return found ? t(found.label) : key;
};

// Même format dans les deux langues : chiffres occidentaux et espace comme
// séparateur de milliers. Le point serait lu comme une virgule décimale.
const formatPrice = (value) =>
    value == null ? null : new Intl.NumberFormat('fr-FR').format(value);
</script>

<template>
    <section id="catalogue" class="relative overflow-hidden bg-obsidian-950 py-24 sm:py-32">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-500/40 to-transparent" aria-hidden="true" />

        <div class="mx-auto max-w-7xl px-6">
            <!-- En-tête de section -->
            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                <div class="max-w-2xl">
                    <p class="section-label">
                        <span class="h-px w-10 bg-brand-500" aria-hidden="true" />
                        {{ t('catalogue.label') }}
                    </p>
                    <h2 class="heading-display mt-6">{{ t('catalogue.title') }}</h2>
                    <p class="mt-5 text-base leading-relaxed text-obsidian-300">
                        {{ t('catalogue.lead') }}
                    </p>
                </div>

                <p class="shrink-0 text-sm text-obsidian-400">
                    <span class="font-display text-4xl font-light text-white">{{ filtered.length }}</span>
                    <span class="ms-2">{{ filtered.length > 1 ? t('catalogue.count_many') : t('catalogue.count_one') }}</span>
                </p>
            </div>

            <!-- Filtres -->
            <div class="mt-14 space-y-6" data-reveal data-reveal-delay="100">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="me-2 hidden text-xs uppercase tracking-[0.2em] text-obsidian-500 sm:inline">{{ t('catalogue.filter_usage') }}</span>
                    <button
                        v-for="app in APPLICATIONS"
                        :key="app.key"
                        type="button"
                        class="rounded-full border px-5 py-2.5 text-sm font-medium transition-all duration-300"
                        :class="
                            activeApplication === app.key
                                ? 'border-brand-400 bg-brand-500 text-white shadow-brand-glow'
                                : 'border-white/10 bg-white/5 text-obsidian-300 hover:border-white/25 hover:text-white'
                        "
                        :aria-pressed="activeApplication === app.key"
                        @click="activeApplication = app.key"
                    >
                        {{ t(app.label) }}
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="me-2 hidden text-xs uppercase tracking-[0.2em] text-obsidian-500 sm:inline">{{ t('catalogue.filter_colour') }}</span>
                    <button
                        v-for="color in COLORS"
                        :key="color.key"
                        type="button"
                        class="group flex items-center gap-2.5 rounded-full border py-2 ps-2 pe-5 text-sm font-medium transition-all duration-300"
                        :class="
                            activeColor === color.key
                                ? 'border-brand-400 bg-white/10 text-white'
                                : 'border-white/10 bg-white/5 text-obsidian-300 hover:border-white/25 hover:text-white'
                        "
                        :aria-pressed="activeColor === color.key"
                        @click="activeColor = color.key"
                    >
                        <span
                            class="h-5 w-5 rounded-full ring-1 ring-inset ring-white/25"
                            :style="{ background: color.swatch }"
                            aria-hidden="true"
                        />
                        {{ t(color.label) }}
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
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-white/10 bg-obsidian-900/60 transition-all duration-500 hover:-translate-y-1.5 hover:border-brand-500/50 hover:shadow-luxe"
                >
                    <div class="relative aspect-[4/3] overflow-hidden bg-obsidian-800">
                        <img
                            v-if="hasImage(product)"
                            :src="product.image"
                            :alt="`${product.name} — ${product.color}`"
                            class="h-full w-full object-cover transition-transform duration-[900ms] ease-out group-hover:scale-110"
                            loading="lazy"
                            decoding="async"
                            @error="onImageError(product)"
                        />
                        <div
                            v-else
                            class="grid h-full w-full place-items-center bg-gradient-to-br from-obsidian-700 to-obsidian-900 text-xs uppercase tracking-[0.2em] text-obsidian-500"
                        >
                            {{ t('catalogue.photo_soon') }}
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-obsidian-950 via-obsidian-950/10 to-transparent opacity-80 transition-opacity duration-500 group-hover:opacity-95" aria-hidden="true" />

                        <span
                            v-if="product.finish"
                            class="absolute start-4 top-4 rounded-full border border-white/20 bg-obsidian-950/70 px-3 py-1 text-[0.65rem] uppercase tracking-[0.16em] text-obsidian-200 backdrop-blur"
                        >
                            {{ product.finish }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="font-display text-xl font-medium text-white">{{ product.name }}</h3>
                        <p class="mt-1 text-sm text-brand-400">{{ product.color }}</p>

                        <p v-if="product.description" class="mt-3 line-clamp-2 text-sm leading-relaxed text-obsidian-400">
                            {{ product.description }}
                        </p>

                        <ul class="mt-4 flex flex-wrap gap-1.5">
                            <li
                                v-for="app in product.applications ?? []"
                                :key="app"
                                class="rounded-md bg-white/5 px-2.5 py-1 text-[0.68rem] tracking-wide text-obsidian-300"
                            >
                                {{ applicationLabel(app) }}
                            </li>
                        </ul>

                        <div class="mt-5 flex items-end justify-between gap-3 border-t border-white/5 pt-4">
                            <div>
                                <p v-if="product.pricePerM2" class="font-display text-lg text-white">
                                    {{ formatPrice(product.pricePerM2) }}
                                    <span class="text-xs font-sans text-obsidian-400">{{ t('catalogue.per_m2') }}</span>
                                </p>
                                <p v-else class="text-sm text-obsidian-400">{{ t('catalogue.price_on_request') }}</p>
                                <p v-if="product.origin" class="mt-0.5 text-[0.68rem] uppercase tracking-[0.14em] text-obsidian-500">
                                    {{ t('catalogue.origin') }} {{ product.origin }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="shrink-0 rounded-full border border-brand-500/40 px-4 py-2 text-xs font-semibold text-brand-300 transition hover:bg-brand-500 hover:text-white"
                                @click="emit('request-quote', product)"
                            >
                                {{ t('catalogue.quote') }}
                            </button>
                        </div>
                    </div>
                </article>
            </TransitionGroup>

            <!-- État vide -->
            <div v-if="!filtered.length" class="mt-16 rounded-2xl border border-dashed border-white/15 py-20 text-center">
                <p class="text-obsidian-300">{{ t('catalogue.empty_title') }}</p>
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

            <p class="mt-14 text-center text-sm text-obsidian-400" data-reveal>
                {{ t('catalogue.not_found') }}
                <a
                    :href="whatsappLink(t('whatsapp.not_found'))"
                    target="_blank"
                    rel="noopener"
                    class="text-brand-400 underline-offset-4 transition hover:underline"
                >
                    {{ t('catalogue.not_found_link') }}
                </a>.
            </p>
        </div>
    </section>
</template>
