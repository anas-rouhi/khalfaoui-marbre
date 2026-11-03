<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BeforeAfterSlider from '@/Components/Site/BeforeAfterSlider.vue';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps({
    projects: { type: Array, default: () => [] },
    /**
     * Types de projet administrés (Catalogue › Types de projet), dans l'ordre
     * du back-office et déjà traduits. Format : [{ key, label }]
     */
    projectTypes: { type: Array, default: () => [] },
});

const { t } = useI18n();

// ── Comparateur avant / après ──
const comparable = computed(() =>
    props.projects.filter((project) => project.beforeImage && project.image),
);

const comparedIndex = ref(0);

const compared = computed(() => comparable.value[comparedIndex.value] ?? null);

const items = computed(() => props.projects);

/**
 * Boutons de filtre : uniquement les types portés par au moins une
 * réalisation affichée (le serveur n'envoie que les réalisations publiées),
 * dans l'ordre du back-office. Un type absent de la liste — supprimé depuis,
 * ou données de démonstration — reste filtrable sous son libellé brut.
 *
 * `value: null` = aucun filtre. On filtre sur la clé du type, jamais sur le
 * texte affiché, qui change avec la langue.
 */
const categories = computed(() => {
    const used = new Set(items.value.map((item) => item.categoryKey).filter(Boolean));
    const known = new Set(props.projectTypes.map((type) => type.key));

    const orphans = [...used]
        .filter((key) => !known.has(key))
        .map((key) => ({
            key,
            label: items.value.find((item) => item.categoryKey === key)?.category ?? key,
        }));

    return [
        { value: null, label: t('projects.all') },
        ...[...props.projectTypes.filter((type) => used.has(type.key)), ...orphans].map((type) => ({
            value: type.key,
            label: type.label,
        })),
    ];
});

const activeCategory = ref(null);

// Évite l'image cassée tant que les photos ne sont pas déposées sur le serveur.
const brokenImages = ref(new Set());
const hasImage = (project) => Boolean(project.image) && !brokenImages.value.has(project.id);
const onImageError = (project) => {
    brokenImages.value = new Set(brokenImages.value).add(project.id);
};

// `null` = aucun filtre : la catégorie « Tout » change de libellé avec la
// langue, on ne peut donc pas s'y référer par son texte.
const filtered = computed(() =>
    activeCategory.value === null
        ? items.value
        : items.value.filter((item) => item.categoryKey === activeCategory.value),
);

/**
 * Rythme éditorial : la 1re et la 6e vignette occupent deux colonnes, ce qui
 * évite la monotonie d'une grille strictement régulière.
 */
const tileClass = (index) =>
    index % 5 === 0 ? 'lg:col-span-2 lg:row-span-2' : '';

// ── Visionneuse plein écran ──
const lightbox = ref(null);
const slideIndex = ref(0);

/**
 * Photo de couverture suivie de la galerie : la visionneuse parcourt
 * l'ensemble des vues du chantier.
 */
const slides = computed(() => {
    if (!lightbox.value) {
        return [];
    }

    return [
        { image: lightbox.value.image, caption: null },
        ...(lightbox.value.gallery ?? []),
    ].filter((slide) => Boolean(slide.image));
});

const currentSlide = computed(() => slides.value[slideIndex.value] ?? null);

const goTo = (index) => {
    const total = slides.value.length;
    if (total) {
        // Modulo positif : la navigation boucle dans les deux sens.
        slideIndex.value = ((index % total) + total) % total;
    }
};

const openLightbox = (project) => {
    lightbox.value = project;
    slideIndex.value = 0;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    lightbox.value = null;
    document.body.style.overflow = '';
};

const onKeydown = (event) => {
    if (!lightbox.value) {
        return;
    }

    if (event.key === 'Escape') {
        closeLightbox();
    } else if (event.key === 'ArrowRight') {
        goTo(slideIndex.value + 1);
    } else if (event.key === 'ArrowLeft') {
        goTo(slideIndex.value - 1);
    }
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <section id="realisations" class="relative bg-white py-24 sm:py-32 dark:bg-obsidian-900">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                <div class="max-w-2xl">
                    <p class="section-label">
                        <span class="section-rule" aria-hidden="true" />
                        {{ t('projects.label') }}
                    </p>
                    <h2 class="heading-display mt-6">{{ t('projects.title') }}</h2>
                    <p class="mt-5 text-base leading-relaxed text-stone-600 dark:text-obsidian-300">
                        {{ t('projects.lead') }}
                    </p>
                </div>

                <!-- Masquée tant qu'aucune réalisation ne porte de type :
                     un bouton « Tous » seul n'aurait aucun effet. -->
                <div v-if="categories.length > 1" class="flex flex-wrap gap-2.5">
                    <button
                        v-for="category in categories"
                        :key="category.value ?? 'all'"
                        type="button"
                        class="rounded-full border px-5 py-2.5 text-sm font-medium transition-all duration-300"
                        :class="
                            activeCategory === category.value
                                ? 'border-brand-500 bg-brand-600 text-white dark:border-brand-400 dark:bg-brand-600'
                                : 'chip'
                        "
                        :aria-pressed="activeCategory === category.value"
                        @click="activeCategory = category.value"
                    >
                        {{ category.label }}
                    </button>
                </div>
            </div>

            <!-- La révélation porte sur la grille entière : les vignettes sont
                 recréées à chaque changement de filtre et resteraient sinon
                 masquées, faute d'être observées. -->
            <div
                class="mt-14 grid auto-rows-[220px] grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
                data-reveal
            >
                <button
                    v-for="(project, index) in filtered"
                    :key="project.id"
                    type="button"
                    class="group relative overflow-hidden rounded-2xl border border-stone-200 bg-obsidian-800 text-start transition-all duration-500 hover:border-brand-500/50 hover:shadow-stone dark:border-white/10 dark:hover:border-champagne-300/40 dark:hover:shadow-luxe focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400"
                    :class="tileClass(index)"
                    @click="openLightbox(project)"
                >
                    <img
                        v-if="hasImage(project)"
                        :src="project.image"
                        :alt="project.title"
                        width="1600"
                        height="1200"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1100ms] ease-out group-hover:scale-110"
                        loading="lazy"
                        decoding="async"
                        @error="onImageError(project)"
                    />
                    <div
                        v-else
                        class="absolute inset-0 grid place-items-center bg-gradient-to-br from-obsidian-700 to-obsidian-900 text-xs uppercase tracking-[0.2em] text-obsidian-500"
                    >
                        {{ t('catalogue.photo_soon') }}
                    </div>

                    <!-- Les photos de chantier vont du très clair au très
                         sombre : le voile doit rester assez dense pour que la
                         légende tienne sur une façade en plein soleil. -->
                    <div class="absolute inset-0 bg-gradient-to-t from-obsidian-950 via-obsidian-950/75 via-45% to-obsidian-950/15 transition-opacity duration-500 group-hover:via-obsidian-950/85" aria-hidden="true" />

                    <div class="absolute inset-x-0 bottom-0 p-5">
                        <!-- Ombre portée : ce libellé fin passe sur des photos
                             très claires comme très sombres. -->
                        <div class="flex items-center gap-2 text-[0.65rem] uppercase tracking-[0.2em] text-brand-300 [text-shadow:0_1px_4px_rgba(0,0,0,0.95)]">
                            <span v-if="project.category">{{ project.category }}</span>
                            <span v-if="project.category && project.year" class="h-1 w-1 rounded-full bg-brand-500" aria-hidden="true" />
                            <span v-if="project.year">{{ project.year }}</span>
                        </div>

                        <h3 class="mt-2 font-display text-lg font-medium leading-snug text-white sm:text-xl">
                            {{ project.title }}
                        </h3>

                        <p v-if="project.location" class="mt-1.5 text-sm text-obsidian-300">
                            {{ project.location }}
                        </p>

                        <span class="mt-3 inline-flex translate-y-2 items-center gap-2 text-xs font-semibold text-white opacity-0 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100">
                            {{ t('projects.view') }}
                            <svg class="h-3.5 w-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </button>
            </div>

            <!-- ══ Comparateur avant / après ══ -->
            <div v-if="compared" class="mt-24" data-reveal>
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="section-label">
                            <span class="section-rule" aria-hidden="true" />
                            {{ t('compare.label') }}
                        </p>
                        <h3 class="heading-display mt-6 text-3xl sm:text-4xl lg:text-5xl">
                            {{ t('compare.title') }}
                        </h3>
                        <p class="mt-5 text-base leading-relaxed text-stone-600 dark:text-obsidian-300">
                            {{ t('compare.lead') }}
                        </p>
                    </div>

                    <!-- Sélecteur de chantier -->
                    <div v-if="comparable.length > 1" class="flex flex-wrap gap-2.5">
                        <button
                            v-for="(project, index) in comparable"
                            :key="project.id"
                            type="button"
                            class="rounded-full border px-5 py-2.5 text-sm font-medium transition-all duration-300"
                            :class="
                                index === comparedIndex
                                    ? 'border-brand-500 bg-brand-600 text-white dark:border-brand-400 dark:bg-brand-600'
                                    : 'chip'
                            "
                            :aria-pressed="index === comparedIndex"
                            @click="comparedIndex = index"
                        >
                            {{ project.location?.split(',')[0] ?? project.category }}
                        </button>
                    </div>
                </div>

                <div class="mt-10">
                    <BeforeAfterSlider
                        :key="compared.id"
                        :before="compared.beforeImage"
                        :after="compared.image"
                        :before-caption="compared.beforeCaption"
                        :after-caption="compared.title"
                    />

                    <p class="mt-5 text-sm text-stone-500 dark:text-obsidian-300">
                        <span class="text-stone-900 dark:text-white">{{ compared.title }}</span>
                        <span v-if="compared.location"> — {{ compared.location }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Visionneuse -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-200 ease-in"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="lightbox"
                    class="fixed inset-0 z-[60] grid place-items-center bg-stone-950/85 p-4 backdrop-blur-md sm:p-8 dark:bg-obsidian-950/95"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="lightbox.title"
                    @click.self="closeLightbox"
                >
                    <button
                        type="button"
                        class="absolute end-5 top-5 grid h-11 w-11 place-items-center rounded-full border border-white/20 bg-white/5 text-white transition hover:border-brand-400 hover:bg-white/10"
                        :aria-label="t('projects.close')"
                        @click="closeLightbox"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-luxe dark:border-white/10 dark:bg-obsidian-900">
                        <div v-if="currentSlide" class="relative">
                            <img
                                :src="currentSlide.image"
                                :alt="currentSlide.caption || lightbox.title"
                                width="1600"
                                height="1200"
                                decoding="async"
                                class="max-h-[62vh] w-full object-cover"
                            />

                            <!-- Légende de la vue courante -->
                            <p
                                v-if="currentSlide.caption"
                                class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-obsidian-950/95 to-transparent px-5 pb-4 pt-10 text-sm text-obsidian-200"
                            >
                                {{ currentSlide.caption }}
                            </p>

                            <!-- Navigation, uniquement s'il y a plusieurs vues -->
                            <template v-if="slides.length > 1">
                                <button
                                    type="button"
                                    class="absolute left-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-white/20 bg-obsidian-950/70 text-white transition hover:border-brand-400 hover:bg-obsidian-950"
                                    :aria-label="t('projects.prev_photo')"
                                    @click.stop="goTo(slideIndex - 1)"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-white/20 bg-obsidian-950/70 text-white transition hover:border-brand-400 hover:bg-obsidian-950"
                                    :aria-label="t('projects.next_photo')"
                                    @click.stop="goTo(slideIndex + 1)"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>

                                <div class="absolute end-4 top-4 flex items-center gap-1.5">
                                    <button
                                        v-for="(slide, index) in slides"
                                        :key="index"
                                        type="button"
                                        class="h-1.5 rounded-full transition-all duration-300"
                                        :class="index === slideIndex ? 'w-6 bg-brand-400' : 'w-1.5 bg-white/40 hover:bg-white/70'"
                                        :aria-label="t('projects.see_photo', { number: index + 1 })"
                                        :aria-current="index === slideIndex"
                                        @click.stop="goTo(index)"
                                    />
                                </div>
                            </template>
                        </div>

                        <div class="p-6 sm:p-8">
                            <div class="flex items-center gap-2 text-[0.65rem] uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">
                                <span v-if="lightbox.category">{{ lightbox.category }}</span>
                                <span v-if="lightbox.location">· {{ lightbox.location }}</span>
                                <span v-if="lightbox.year">· {{ lightbox.year }}</span>
                            </div>
                            <h3 class="mt-3 font-display text-2xl font-light text-stone-900 sm:text-3xl dark:text-white">{{ lightbox.title }}</h3>
                            <p v-if="lightbox.description" class="mt-4 leading-relaxed text-stone-600 dark:text-obsidian-300">
                                {{ lightbox.description }}
                            </p>
                            <a href="#devis" class="btn-primary mt-7" @click="closeLightbox">
                                {{ t('projects.similar') }}
                            </a>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>
