<script setup>
import { ref } from 'vue';
import { useCompany } from '@/Composables/useCompany';
import { useI18n } from '@/Composables/useI18n';

defineProps({
    /** Remplacez par votre plus belle photo de chantier (1920×1280 min). */
    image: { type: String, default: '/images/hero/hero-cuisine-granit.jpg' },
});

const { company, whatsappLink } = useCompany();
const { t } = useI18n();

// Tant que la photo n'est pas déposée, les dégradés seuls assurent un fond
// sombre présentable plutôt qu'une image cassée.
const imageFailed = ref(false);

const stats = [
    { value: '15+', key: 'hero.stat_years' },
    { value: '500+', key: 'hero.stat_sites' },
    { value: '40+', key: 'hero.stat_varieties' },
];
</script>

<template>
    <section id="accueil" class="relative min-h-[100svh] overflow-hidden bg-obsidian-950">
        <!-- Couche image : légèrement surdimensionnée pour que le panoramique
             lent ne laisse jamais apparaître de bord. -->
        <div class="absolute inset-0 bg-gradient-to-br from-obsidian-800 via-obsidian-950 to-black">
            <img
                v-show="!imageFailed"
                :src="image"
                :alt="t('hero.image_alt')"
                class="h-full w-full origin-center object-cover object-center animate-slow-pan"
                fetchpriority="high"
                decoding="async"
                @error="imageFailed = true"
            />
        </div>

        <!-- Superpositions : dégradé vertical pour ancrer le texte, voile
             latéral pour le contraste, et teinte verte discrète de la marque. -->
        <!-- Voiles dosés pour garder le texte lisible à gauche sans effacer la
             photo : le côté droit reste largement dégagé sur la matière. -->
        <div class="absolute inset-0 bg-gradient-to-t from-obsidian-950 via-obsidian-950/55 to-obsidian-950/20" aria-hidden="true" />
        <div class="absolute inset-0 bg-gradient-to-r from-obsidian-950/95 via-obsidian-950/45 to-transparent" aria-hidden="true" />
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_20%_60%,rgba(30,157,107,0.16),transparent_58%)]" aria-hidden="true" />
        <div class="pointer-events-none absolute inset-0 shadow-[inset_0_0_140px_40px_rgba(0,0,0,0.55)]" aria-hidden="true" />

        <div class="relative mx-auto flex min-h-[100svh] max-w-7xl flex-col justify-center px-6 pb-24 pt-40 sm:pt-44">
            <div class="max-w-3xl">
                <p class="section-label animate-fade-up delay-100ms">
                    <span class="h-px w-10 bg-brand-500" aria-hidden="true" />
                    {{ company.legal_name }}
                </p>

                <h1 class="mt-7 animate-fade-up font-display text-5xl font-light leading-[1.05] tracking-tight text-white delay-200ms sm:text-6xl lg:text-7xl">
                    {{ t('hero.title_1') }}
                    <span class="block bg-gradient-to-r from-brand-300 via-brand-400 to-brand-600 bg-clip-text text-transparent">
                        {{ t('hero.title_2') }}
                    </span>
                </h1>

                <p class="mt-8 max-w-xl animate-fade-up text-base leading-relaxed text-obsidian-200 delay-300ms sm:text-lg">
                    {{ t('hero.lead') }}
                </p>

                <div class="mt-11 flex animate-fade-up flex-col gap-4 delay-400ms sm:flex-row sm:items-center">
                    <a href="#devis" class="btn-primary group px-9 py-4 text-base">
                        {{ t('hero.cta_quote') }}
                        <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>

                    <a href="#catalogue" class="btn-ghost px-9 py-4 text-base">
                        {{ t('hero.cta_collection') }}
                    </a>
                </div>

                <!-- Chiffres clés -->
                <dl class="mt-16 grid max-w-lg animate-fade-up grid-cols-3 gap-6 border-t border-white/10 pt-8 delay-500ms">
                    <div v-for="stat in stats" :key="stat.label">
                        <dt class="sr-only">{{ t(stat.key) }}</dt>
                        <dd>
                            <span class="block font-display text-3xl font-light text-brand-400 sm:text-4xl"><bdi dir="ltr">{{ stat.value }}</bdi></span>
                            <span class="mt-1.5 block text-[0.7rem] uppercase tracking-[0.18em] text-obsidian-400">{{ t(stat.key) }}</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Repère de défilement -->
        <a
            href="#catalogue"
            class="absolute bottom-8 left-1/2 hidden -translate-x-1/2 animate-fade-in flex-col items-center gap-2 text-obsidian-400 transition hover:text-brand-400 delay-700ms md:flex"
            :aria-label="t('hero.scroll_aria')"
        >
            <span class="text-[0.65rem] uppercase tracking-[0.35em]">{{ t('hero.scroll') }}</span>
            <svg class="h-5 w-5 animate-scroll-hint" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M12 5v14M6 13l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>

        <!-- Raccourci WhatsApp visible dès le premier écran sur mobile -->
        <a
            :href="whatsappLink(t('whatsapp.quote'))"
            target="_blank"
            rel="noopener"
            class="absolute bottom-6 end-6 grid h-14 w-14 place-items-center rounded-full bg-brand-500 text-white shadow-brand-glow transition hover:scale-105 hover:bg-brand-400 sm:hidden"
            :aria-label="t('hero.whatsapp_aria')"
        >
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.33 4.97L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91a9.85 9.85 0 0 0-9.91-9.93Zm5.43 12.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.3-.02-.46.13-.6.14-.14.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35Z" />
            </svg>
        </a>
    </section>
</template>
