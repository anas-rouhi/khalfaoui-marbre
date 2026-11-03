<script setup>
import { computed, nextTick, ref } from 'vue';
import { Head } from '@inertiajs/vue3';

import SiteHeader from '@/Components/Site/SiteHeader.vue';
import HeroSection from '@/Components/Site/HeroSection.vue';
import ServicesSection from '@/Components/Site/ServicesSection.vue';
import CatalogueSection from '@/Components/Site/CatalogueSection.vue';
import RealisationsSection from '@/Components/Site/RealisationsSection.vue';
import DevisSection from '@/Components/Site/DevisSection.vue';
import ContactSection from '@/Components/Site/ContactSection.vue';
import SiteFooter from '@/Components/Site/SiteFooter.vue';

import {
    FALLBACK_APPLICATIONS,
    FALLBACK_CATEGORIES,
    FALLBACK_COLOR_FAMILIES,
    FALLBACK_PRODUCTS,
    FALLBACK_PROJECT_TYPES,
    FALLBACK_PROJECTS,
} from '@/data/catalogue';
import { useI18n } from '@/Composables/useI18n';
import { useReveal } from '@/Composables/useReveal';

const props = defineProps({
    products: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    /** Vrai dès qu'une réalisation existe en base, publiée ou non. */
    hasPortfolio: { type: Boolean, default: false },
    /** Types de projet du filtre Réalisations, administrés depuis /admin › Catalogue › Types de projet. */
    projectTypes: { type: Array, default: () => [] },
    /** Familles de matière du filtre catalogue, administrées depuis /admin › Catalogue › Familles de matière. */
    categories: { type: Array, default: () => [] },
    /** Teintes du filtre catalogue, administrées depuis /admin › Réglages › Teintes. */
    colorFamilies: { type: Array, default: () => [] },
    /** Usages du filtre catalogue, administrés depuis /admin › Réglages › Usages & tarifs. */
    applications: { type: Array, default: () => [] },
    /** Tarifs de pose actifs, pour l'estimateur de devis. */
    installationRates: { type: Array, default: () => [] },
});

const { t } = useI18n();

// Tant que la base n'est pas alimentée, la page reste entièrement démontrable.
const isDemo = computed(() => !props.products.length);
const catalogue = computed(() => (isDemo.value ? FALLBACK_PRODUCTS : props.products));
// Démonstration seulement si la base n'a aucune réalisation : si le gérant
// les a toutes masquées, aucune ne doit apparaître, pas même celles de démo.
const isDemoPortfolio = computed(() => !props.projects.length && !props.hasPortfolio);
const realisations = computed(() => (isDemoPortfolio.value ? FALLBACK_PROJECTS : props.projects));
const projectTypes = computed(() =>
    isDemoPortfolio.value
        ? FALLBACK_PROJECT_TYPES.map((type) => ({ ...type, label: t(`project_type.${type.key}`) }))
        : props.projectTypes,
);

// Les filtres suivent le catalogue affiché : ceux de la base avec les vraies
// fiches, ceux de démonstration (libellés du dictionnaire) sinon.
const colorFamilies = computed(() =>
    isDemo.value
        ? FALLBACK_COLOR_FAMILIES.map((family) => ({ ...family, label: t(`colour.${family.key}`) }))
        : props.colorFamilies,
);
const applications = computed(() =>
    isDemo.value
        ? FALLBACK_APPLICATIONS.map((application) => ({ ...application, label: t(`application.${application.key}`) }))
        : props.applications,
);
const categories = computed(() =>
    isDemo.value
        ? FALLBACK_CATEGORIES.map((category) => ({ ...category, label: t(`family.${category.key}`) }))
        : props.categories,
);

const root = ref(null);
useReveal(root);

// Un clic sur « Devis » depuis une fiche produit pré-remplit l'estimateur.
const selectedProduct = ref(null);

const requestQuote = (product) => {
    selectedProduct.value = product;

    nextTick(() => {
        document.querySelector('#devis')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
};
</script>

<template>
    <!-- Description, Open Graph et données structurées sont posés côté serveur
         (app.blade.php), dans la langue de la page : seul le titre est géré ici. -->
    <Head :title="t('seo.title')" />

    <div ref="root" class="min-h-screen bg-obsidian-950 font-sans text-obsidian-100">
        <SiteHeader />

        <main>
            <HeroSection />
            <ServicesSection />
            <CatalogueSection
                :products="catalogue"
                :color-families="colorFamilies"
                :applications="applications"
                :categories="categories"
                @request-quote="requestQuote"
            />
            <RealisationsSection :projects="realisations" :project-types="projectTypes" />
            <DevisSection
                :products="catalogue"
                :selected="selectedProduct"
                :installation-rates="installationRates"
            />
            <ContactSection />
        </main>

        <SiteFooter />
    </div>
</template>
