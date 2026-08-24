<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    featuredProducts: Array,
    categories: Array,
});
</script>

<template>
    <Head title="Luxe Marbre - Excellence & Elégance" />

    <div class="min-h-screen bg-stone-50 text-stone-800 font-sans">
        <!-- Navbar -->
        <nav class="flex justify-between items-center px-8 py-6 bg-white shadow-sm border-b border-stone-200 sticky top-0 z-50">
            <div class="text-2xl font-serif font-bold tracking-widest text-amber-800 uppercase">
                LUXE<span class="text-stone-900">MARBRE</span>
            </div>
            <div class="space-x-8 text-sm font-medium tracking-wide uppercase hidden md:flex">
                <a href="#accueil" class="hover:text-amber-700 transition">Accueil</a>
                <a href="#catalogue" class="hover:text-amber-700 transition">Catalogue</a>
                <a href="#realisations" class="hover:text-amber-700 transition">Réalisations</a>
                <a href="#contact" class="hover:text-amber-700 transition">Contact</a>
            </div>
            <a href="#devis" class="bg-amber-800 text-white px-5 py-2.5 rounded text-xs uppercase tracking-wider font-semibold hover:bg-amber-900 transition">
                Demander Devis
            </a>
        </nav>

        <!-- Hero Section -->
        <section class="relative bg-stone-900 text-white py-32 px-8 text-center bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600');">
            <div class="max-w-4xl mx-auto space-y-6">
                <span class="text-amber-400 uppercase tracking-widest text-xs font-semibold">Fournisseur de Marbre & Granit de Luxe</span>
                <h1 class="text-4xl md:text-6xl font-serif font-light leading-tight">
                    L'Élégance Naturelle pour vos Espaces
                </h1>
                <p class="text-stone-300 max-w-2xl mx-auto text-base">
                    Découvrez notre collection exclusive de marbres d'exception pour cuisines, salles de bain et sols de haut standing.
                </p>
                <div class="pt-4 flex justify-center gap-4">
                    <a href="#catalogue" class="bg-amber-700 hover:bg-amber-800 text-white px-8 py-3 rounded font-medium text-sm uppercase tracking-wider transition">
                        Explorer la Collection
                    </a>
                </div>
            </div>
        </section>

        <!-- Section Products -->
        <section id="catalogue" class="max-w-7xl mx-auto px-8 py-20">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-serif font-bold uppercase tracking-wider text-stone-900">Nos Produits Vedettes</h2>
                <div class="w-16 h-0.5 bg-amber-700 mx-auto mt-3"></div>
            </div>

            <!-- Grid -->
            <div v-if="featuredProducts && featuredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="product in featuredProducts" :key="product.id" class="bg-white rounded border border-stone-200 overflow-hidden shadow-sm hover:shadow-md transition">
                    <div class="h-64 bg-stone-200 relative">
                        <img 
                            v-if="product.images && product.images.length > 0" 
                            :src="product.images[0].image_path.startsWith('http') ? product.images[0].image_path : `/storage/${product.images[0].image_path}`" 
                            :alt="product.name" 
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                        />                        <div v-else class="w-full h-full flex items-center justify-center text-stone-400 font-serif">Image Marbre</div>
                    </div>
                    <div class="p-6">
                        <span class="text-xs uppercase font-bold text-amber-800">{{ product.color }}</span>
                        <h3 class="text-xl font-serif font-semibold text-stone-900 mt-1">{{ product.name }}</h3>
                        <p class="text-stone-600 text-sm mt-2 line-clamp-2">{{ product.description }}</p>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 text-stone-500 font-serif">
                Aucun produit vedette disponible pour le moment. (Nous allons en ajouter via un Seeder ou Admin).
            </div>
        </section>
    </div>
</template>