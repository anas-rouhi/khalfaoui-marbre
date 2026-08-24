<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps({
    before: { type: String, required: true },
    after: { type: String, required: true },
    beforeLabel: { type: String, default: null },
    afterLabel: { type: String, default: null },
    beforeCaption: { type: String, default: null },
    afterCaption: { type: String, default: null },
    /** Position initiale de la séparation, en pourcentage. */
    start: { type: Number, default: 50 },
});

const { t } = useI18n();

const beforeText = computed(() => props.beforeLabel ?? t('compare.before'));
const afterText = computed(() => props.afterLabel ?? t('compare.after'));

const container = ref(null);
const position = ref(props.start);
const dragging = ref(false);

const clamp = (value) => Math.min(100, Math.max(0, value));

/**
 * Le volet « après » est révélé par un rognage à droite de la séparation :
 * les deux photos restent ainsi parfaitement superposées, quelle que soit
 * la largeur de l'écran.
 */
const revealStyle = computed(() => ({
    clipPath: `inset(0 0 0 ${position.value}%)`,
}));

const moveTo = (clientX) => {
    const bounds = container.value?.getBoundingClientRect();

    if (!bounds?.width) {
        return;
    }

    position.value = clamp(((clientX - bounds.left) / bounds.width) * 100);
};

const onPointerDown = (event) => {
    dragging.value = true;
    container.value?.setPointerCapture?.(event.pointerId);
    moveTo(event.clientX);
};

const onPointerMove = (event) => {
    if (dragging.value) {
        moveTo(event.clientX);
    }
};

const stopDragging = (event) => {
    if (!dragging.value) {
        return;
    }

    dragging.value = false;
    container.value?.releasePointerCapture?.(event.pointerId);
};

// Accessibilité : la poignée est un slider, pilotable au clavier.
const onKeydown = (event) => {
    const step = event.shiftKey ? 10 : 2;

    if (event.key === 'ArrowLeft') {
        position.value = clamp(position.value - step);
    } else if (event.key === 'ArrowRight') {
        position.value = clamp(position.value + step);
    } else if (event.key === 'Home') {
        position.value = 0;
    } else if (event.key === 'End') {
        position.value = 100;
    } else {
        return;
    }

    event.preventDefault();
};

onBeforeUnmount(() => (dragging.value = false));
</script>

<template>
    <div
        ref="container"
        class="group relative aspect-[16/10] w-full touch-pan-y select-none overflow-hidden rounded-2xl border border-white/10 bg-obsidian-900"
        :class="dragging ? 'cursor-grabbing' : 'cursor-grab'"
        @pointerdown.prevent="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="stopDragging"
        @pointercancel="stopDragging"
    >
        <!-- Photo « avant » : plan de fond -->
        <img
            :src="before"
            :alt="beforeCaption || beforeText"
            class="pointer-events-none absolute inset-0 h-full w-full object-cover"
            draggable="false"
            loading="lazy"
        />

        <!-- Photo « après » : rognée à la position de la séparation -->
        <img
            :src="after"
            :alt="afterCaption || afterText"
            class="pointer-events-none absolute inset-0 h-full w-full object-cover"
            :style="revealStyle"
            draggable="false"
            loading="lazy"
        />

        <!-- Étiquettes -->
        <span
            class="pointer-events-none absolute left-4 top-4 rounded-full border border-white/20 bg-obsidian-950/75 px-3 py-1 text-[0.65rem] uppercase tracking-[0.2em] text-obsidian-200 backdrop-blur transition-opacity duration-300"
            :class="position < 18 ? 'opacity-0' : 'opacity-100'"
        >
            {{ beforeText }}
        </span>
        <span
            class="pointer-events-none absolute right-4 top-4 rounded-full border border-brand-400/40 bg-brand-500/90 px-3 py-1 text-[0.65rem] uppercase tracking-[0.2em] text-white backdrop-blur transition-opacity duration-300"
            :class="position > 82 ? 'opacity-0' : 'opacity-100'"
        >
            {{ afterText }}
        </span>

        <!-- Séparation -->
        <div
            class="pointer-events-none absolute inset-y-0 w-px bg-white/90 shadow-[0_0_18px_rgba(0,0,0,0.65)]"
            :style="{ left: `${position}%` }"
        />

        <!-- Poignée -->
        <button
            type="button"
            class="absolute top-1/2 grid h-12 w-12 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-white bg-obsidian-950/80 text-white shadow-luxe backdrop-blur transition-transform duration-200 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-obsidian-950"
            :style="{ left: `${position}%` }"
            role="slider"
            :aria-valuenow="Math.round(position)"
            aria-valuemin="0"
            aria-valuemax="100"
            :aria-label="t('compare.aria')"
            @keydown="onKeydown"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M9 6 3 12l6 6M15 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <!-- Légende de la vue actuellement dominante -->
        <p
            v-if="beforeCaption || afterCaption"
            class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-obsidian-950/95 to-transparent px-5 pb-4 pt-12 text-sm text-obsidian-200"
        >
            {{ position > 50 ? (beforeCaption ?? afterCaption) : (afterCaption ?? beforeCaption) }}
        </p>
    </div>
</template>
