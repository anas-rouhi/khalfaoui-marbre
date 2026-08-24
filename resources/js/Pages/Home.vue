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

import { FALLBACK_PRODUCTS, FALLBACK_PROJECTS } from '@/data/catalogue';
import { useCompany } from '@/Composables/useCompany';
import { useReveal } from '@/Composables/useReveal';

const props = defineProps({
    products: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    /** Tarifs de pose administrés depuis /admin › Réglages. */
    installationRates: { type: Array, default: () => [] },
});

const { company } = useCompany();

// Tant que la base n'est pas alimentée, la page reste entièrement démontrable.
const catalogue = computed(() => (props.products.length ? props.products : FALLBACK_PRODUCTS));
const realisations = computed(() => (props.projects.length ? props.projects : FALLBACK_PROJECTS));

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
    <Head>
        <title>Marbre &amp; Granit à Casablanca</title>
        <meta
            name="description"
            :content="`${company.legal_name} — vente et pose de marbre et granit à Casablanca. Plans de travail, sols, escaliers, salles de bain et façades. ${company.address}. Devis gratuit au ${company.phone_display}.`"
        />
    </Head>

    <div ref="root" class="min-h-screen bg-obsidian-950 font-sans text-obsidian-100">
        <SiteHeader />

        <main>
            <HeroSection />
            <ServicesSection />
            <CatalogueSection :products="catalogue" @request-quote="requestQuote" />
            <RealisationsSection :projects="realisations" />
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
