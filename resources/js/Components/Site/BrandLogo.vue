<script setup>
import { computed } from 'vue';
import { useCompany } from '@/Composables/useCompany';

const props = defineProps({
    /** sm pour la navbar compacte, md par défaut, lg pour le pied de page. */
    size: { type: String, default: 'md' },
    /** N'affiche que l'emblème, quelle que soit la largeur d'écran. */
    markOnly: { type: Boolean, default: false },
    /**
     * Pose la signature sur un socle sombre : indispensable dès que
     * l'arrière-plan est clair, le logo étant dessiné en blanc.
     */
    plinth: { type: Boolean, default: false },
});

const { company } = useCompany();

// Hauteurs pensées pour la signature complète (ratio ≈ 5.6:1).
const lockupHeight = computed(
    () => ({ sm: 'h-8', md: 'h-11', lg: 'h-14' })[props.size] ?? 'h-11',
);

// L'emblème est carré : il lui faut une hauteur légèrement inférieure pour
// occuper visuellement la même place que la signature.
const markHeight = computed(
    () => ({ sm: 'h-9', md: 'h-11', lg: 'h-14' })[props.size] ?? 'h-11',
);
</script>

<template>
    <span
        class="inline-flex items-center transition-all duration-500"
        :class="plinth ? 'rounded-xl border border-white/10 bg-obsidian-900 px-4 py-2 shadow-luxe' : ''"
    >
        <!-- Emblème seul : écrans étroits, ou sur demande explicite. -->
        <img
            :src="company.logo_mark"
            :alt="company.legal_name"
            class="w-auto object-contain"
            :class="[markHeight, markOnly ? 'block' : 'block sm:hidden']"
            decoding="async"
        />

        <!-- Signature complète : emblème + KHALFAOUI MARBRE S.A.R.L.
             Aucun texte n'est ajouté à côté, le lettrage fait partie du logo. -->
        <img
            v-if="!markOnly"
            :src="company.logo"
            :alt="company.legal_name"
            class="hidden w-auto object-contain sm:block"
            :class="lockupHeight"
            decoding="async"
        />
    </span>
</template>
