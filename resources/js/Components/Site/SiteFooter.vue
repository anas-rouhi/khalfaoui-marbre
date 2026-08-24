<script setup>
import BrandLogo from '@/Components/Site/BrandLogo.vue';
import { useCompany } from '@/Composables/useCompany';
import { useI18n } from '@/Composables/useI18n';

const { company, telLink, mailLink, mapsDirectionsUrl } = useCompany();
const { t } = useI18n();

const year = new Date().getFullYear();

const navLinks = [
    { key: 'nav.home', href: '#accueil' },
    { key: 'nav.catalogue', href: '#catalogue' },
    { key: 'nav.projects', href: '#realisations' },
    { key: 'nav.quote', href: '#devis' },
    { key: 'nav.contact', href: '#contact' },
];

const specialities = [
    'footer.spec_worktops',
    'footer.spec_floors',
    'footer.spec_stairs',
    'footer.spec_bathrooms',
    'footer.spec_facades',
    'footer.spec_granite',
];
</script>

<template>
    <footer class="border-t border-white/10 bg-obsidian-950">
        <div class="mx-auto max-w-7xl px-6 py-16">
            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <BrandLogo size="lg" />
                    <!-- La raison sociale figure déjà dans le logo : seule la
                         signature d'activité est répétée ici. -->
                    <p class="mt-6 max-w-sm text-sm leading-relaxed text-obsidian-400">
                        {{ company.tagline }}
                    </p>
                </div>

                <nav class="lg:col-span-2" aria-label="Navigation du pied de page">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-white">{{ t('footer.navigation') }}</h2>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li v-for="link in navLinks" :key="link.href">
                            <a :href="link.href" class="text-obsidian-400 transition hover:text-brand-400">{{ t(link.key) }}</a>
                        </li>
                    </ul>
                </nav>

                <div class="lg:col-span-3">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-white">{{ t('footer.specialities') }}</h2>
                    <ul class="mt-5 space-y-3 text-sm text-obsidian-400">
                        <li v-for="speciality in specialities" :key="speciality">{{ t(speciality) }}</li>
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-white">{{ t('footer.contact') }}</h2>
                    <ul class="mt-5 space-y-4 text-sm">
                        <li>
                            <a :href="mapsDirectionsUrl" target="_blank" rel="noopener" class="text-obsidian-400 transition hover:text-brand-400">
                                {{ company.address }}
                            </a>
                        </li>
                        <li>
                            <a :href="telLink" class="text-obsidian-400 transition hover:text-brand-400"><bdi dir="ltr">{{ company.phone_display }}</bdi></a>
                        </li>
                        <li>
                            <a :href="mailLink" class="break-all text-obsidian-400 transition hover:text-brand-400">{{ company.email }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-14 flex flex-col gap-4 border-t border-white/10 pt-8 text-xs text-obsidian-500 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ year }} {{ company.legal_name }}. {{ t('footer.rights') }}</p>
                <p>{{ t('footer.city') }}</p>
            </div>
        </div>
    </footer>
</template>
