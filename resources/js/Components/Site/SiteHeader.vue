<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BrandLogo from '@/Components/Site/BrandLogo.vue';
import LanguageSwitcher from '@/Components/Site/LanguageSwitcher.vue';
import { useCompany } from '@/Composables/useCompany';
import { useI18n } from '@/Composables/useI18n';

const { company, telLink, mailLink, whatsappLink, mapsDirectionsUrl } = useCompany();
const { t } = useI18n();

const navLinks = [
    { key: 'nav.home', href: '#accueil' },
    { key: 'nav.catalogue', href: '#catalogue' },
    { key: 'nav.projects', href: '#realisations' },
    { key: 'nav.quote', href: '#devis' },
    { key: 'nav.contact', href: '#contact' },
];

const scrolled = ref(false);
const mobileOpen = ref(false);
const activeSection = ref('accueil');

const onScroll = () => {
    scrolled.value = window.scrollY > 24;

    // Détermine la section courante pour souligner le lien correspondant.
    const offset = window.scrollY + window.innerHeight * 0.35;
    for (const link of navLinks) {
        const section = document.querySelector(link.href);
        if (section && section.offsetTop <= offset) {
            activeSection.value = link.href.slice(1);
        }
    }
};

const closeMenu = () => (mobileOpen.value = false);

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

const whatsappHref = computed(() => whatsappLink(t('whatsapp.general')));
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50">
        <!-- Barre de contact : masquée sur mobile où l'espace est précieux,
             les mêmes coordonnées restent accessibles dans le menu. -->
        <div
            class="hidden border-b border-white/5 bg-obsidian-950/90 backdrop-blur-md transition-all duration-500 lg:block"
            :class="scrolled ? 'max-h-0 overflow-hidden opacity-0' : 'max-h-16 opacity-100'"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-2.5 text-xs text-obsidian-300">
                <div class="flex items-center gap-7">
                    <a
                        :href="mapsDirectionsUrl"
                        target="_blank"
                        rel="noopener"
                        class="flex items-center gap-2 transition hover:text-brand-400"
                    >
                        <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        {{ company.address }}
                    </a>

                    <a :href="telLink" class="flex items-center gap-2 transition hover:text-brand-400">
                        <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <bdi dir="ltr">{{ company.phone_display }}</bdi>
                    </a>

                    <a :href="mailLink" class="flex items-center gap-2 transition hover:text-brand-400">
                        <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="m2 7 10 6 10-6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ company.email }}
                    </a>
                </div>

                <p class="tracking-[0.28em] text-obsidian-400">{{ t('nav.tagline') }}</p>
            </div>
        </div>

        <!-- Navigation principale -->
        <nav
            class="transition-all duration-500"
            :class="
                scrolled || mobileOpen
                    ? 'border-b border-white/10 bg-obsidian-950/85 shadow-luxe backdrop-blur-xl'
                    : 'border-b border-transparent bg-transparent'
            "
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-4">
                <a href="#accueil" :aria-label="t('nav.home_aria')" @click="closeMenu">
                    <BrandLogo :size="scrolled ? 'sm' : 'md'" />
                </a>

                <ul class="hidden items-center gap-9 lg:flex">
                    <li v-for="link in navLinks" :key="link.href">
                        <a
                            :href="link.href"
                            class="group relative py-2 text-sm font-medium tracking-wide transition-colors duration-300"
                            :class="
                                activeSection === link.href.slice(1)
                                    ? 'text-white'
                                    : 'text-obsidian-300 hover:text-white'
                            "
                        >
                            {{ t(link.key) }}
                            <span
                                class="absolute -bottom-0.5 start-0 h-px bg-brand-400 transition-all duration-300"
                                :class="activeSection === link.href.slice(1) ? 'w-full' : 'w-0 group-hover:w-full'"
                            />
                        </a>
                    </li>
                </ul>

                <div class="flex items-center gap-3">
                    <LanguageSwitcher class="hidden sm:inline-flex" />

                    <a
                        :href="whatsappHref"
                        target="_blank"
                        rel="noopener"
                        class="group hidden items-center gap-2.5 rounded-full bg-brand-500 py-2.5 ps-4 pe-5 text-sm font-semibold text-white shadow-brand-glow transition-all duration-300 hover:bg-brand-400 hover:shadow-lg sm:inline-flex"
                    >
                        <svg class="h-[18px] w-[18px] transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.3-.02-.46.13-.6.14-.14.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35Z" />
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.33 4.97L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.13h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.36c0-4.54 3.7-8.24 8.24-8.24a8.19 8.19 0 0 1 5.82 2.42 8.18 8.18 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23Z" />
                        </svg>
                        <span class="hidden xl:inline"><bdi dir="ltr">{{ company.phone_display }}</bdi></span>
                        <span class="xl:hidden">{{ t('nav.whatsapp') }}</span>
                    </a>

                    <button
                        type="button"
                        class="grid h-11 w-11 place-items-center rounded-xl border border-white/15 bg-white/5 text-white backdrop-blur transition hover:border-brand-400/60 lg:hidden"
                        :aria-expanded="mobileOpen"
                        aria-controls="menu-mobile"
                        :aria-label="mobileOpen ? t('nav.close_menu') : t('nav.open_menu')"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <svg v-if="!mobileOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                        </svg>
                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu mobile -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="-translate-y-3 opacity-0"
                leave-active-class="transition duration-200 ease-in"
                leave-to-class="-translate-y-3 opacity-0"
            >
                <div v-show="mobileOpen" id="menu-mobile" class="border-t border-white/10 bg-obsidian-950/95 backdrop-blur-xl lg:hidden">
                    <ul class="space-y-1 px-6 py-4">
                        <li v-for="link in navLinks" :key="link.href">
                            <a
                                :href="link.href"
                                class="block rounded-xl px-4 py-3 text-sm font-medium text-obsidian-200 transition hover:bg-white/5 hover:text-white"
                                @click="closeMenu"
                            >
                                {{ t(link.key) }}
                            </a>
                        </li>
                    </ul>

                    <div class="space-y-3 border-t border-white/10 px-6 py-5 text-sm text-obsidian-300">
                        <LanguageSwitcher variant="full" class="flex w-full" />

                        <a :href="telLink" class="block transition hover:text-brand-400"><bdi dir="ltr">{{ company.phone_display }}</bdi></a>
                        <a :href="mailLink" class="block break-all transition hover:text-brand-400">{{ company.email }}</a>
                        <p class="text-obsidian-400">{{ company.address }}</p>
                        <a :href="whatsappHref" target="_blank" rel="noopener" class="btn-primary w-full">
                            {{ t('nav.whatsapp_long') }}
                        </a>
                    </div>
                </div>
            </Transition>
        </nav>
    </header>
</template>
