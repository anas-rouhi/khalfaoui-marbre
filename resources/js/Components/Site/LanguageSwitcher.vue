<script setup>
import { useI18n } from '@/Composables/useI18n';

defineProps({
    /** « compact » pour la navbar, « full » pour le menu mobile. */
    variant: { type: String, default: 'compact' },
});

const { t, locale, available, switchLocale } = useI18n();
</script>

<template>
    <div
        class="inline-flex items-center rounded-full border border-white/10 bg-white/5 p-0.5"
        role="group"
        :aria-label="t('locale.switch')"
    >
        <button
            v-for="(item, index) in available"
            :key="item.code"
            type="button"
            class="relative rounded-full px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
            :class="[
                item.code === locale
                    ? 'bg-brand-500 text-white shadow-brand-glow'
                    : 'text-obsidian-300 hover:text-white',
                variant === 'full' ? 'flex-1 py-2.5 text-sm' : '',
            ]"
            :aria-current="item.code === locale ? 'true' : 'false'"
            :lang="item.code"
            @click="switchLocale(item.code)"
        >
            <span v-if="variant === 'full'">{{ item.native }}</span>
            <span v-else>{{ item.short }}</span>

            <!-- Séparateur visuel entre les deux options inactives -->
            <span
                v-if="variant === 'compact' && index === 0 && item.code !== locale && available[1]?.code !== locale"
                class="absolute inset-y-1.5 -end-px w-px bg-white/15"
                aria-hidden="true"
            />
        </button>
    </div>
</template>
