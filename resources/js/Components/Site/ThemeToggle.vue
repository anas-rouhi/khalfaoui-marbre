<script setup>
import { useI18n } from '@/Composables/useI18n';
import { useTheme } from '@/Composables/useTheme';

defineProps({
    /** « compact » pour la navbar, « full » pour le menu mobile. */
    variant: { type: String, default: 'compact' },
});

const { t } = useI18n();
const { isDark, setTheme, toggleTheme } = useTheme();

const SUN = 'M12 3v1.5M12 19.5V21M4.6 4.6l1.1 1.1M18.3 18.3l1.1 1.1M3 12h1.5M19.5 12H21M4.6 19.4l1.1-1.1M18.3 5.7l1.1-1.1M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z';
const MOON = 'M20.4 14.6A8.5 8.5 0 0 1 9.4 3.6a8.5 8.5 0 1 0 11 11Z';
</script>

<template>
    <!-- Navbar : un seul bouton, l'icône du mode actif pivote vers l'autre. -->
    <button
        v-if="variant === 'compact'"
        type="button"
        class="relative grid h-9 w-9 place-items-center overflow-hidden rounded-full border border-stone-200 bg-stone-100/80 text-stone-700 transition-all duration-300 hover:border-champagne-500/50 hover:text-stone-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 dark:border-white/10 dark:bg-white/5 dark:text-champagne-200 dark:hover:border-champagne-300/40 dark:hover:text-champagne-100"
        :aria-label="isDark ? t('theme.to_light') : t('theme.to_dark')"
        :title="isDark ? t('theme.to_light') : t('theme.to_dark')"
        @click="toggleTheme"
    >
        <svg
            class="absolute h-[18px] w-[18px] text-champagne-600 transition-all duration-500 ease-out dark:text-champagne-300"
            :class="isDark ? 'rotate-90 scale-50 opacity-0' : 'rotate-0 scale-100 opacity-100'"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <path :d="SUN" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <svg
            class="absolute h-[17px] w-[17px] transition-all duration-500 ease-out"
            :class="isDark ? 'rotate-0 scale-100 opacity-100' : '-rotate-90 scale-50 opacity-0'"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true"
        >
            <path :d="MOON" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <!-- Menu mobile : deux options explicites, sur le modèle du sélecteur
         de langue. -->
    <div
        v-else
        class="inline-flex items-center rounded-full border border-stone-200 bg-stone-100/80 p-0.5 dark:border-white/10 dark:bg-white/5"
        role="group"
        :aria-label="t('theme.switch')"
    >
        <button
            v-for="option in [
                { dark: false, label: t('theme.light'), icon: SUN },
                { dark: true, label: t('theme.dark'), icon: MOON },
            ]"
            :key="option.label"
            type="button"
            class="flex flex-1 items-center justify-center gap-2 rounded-full py-2.5 text-sm font-semibold transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
            :class="
                option.dark === isDark
                    ? 'bg-white text-stone-900 shadow-stone dark:bg-obsidian-800 dark:text-champagne-100 dark:shadow-none dark:ring-1 dark:ring-champagne-300/30'
                    : 'text-stone-500 hover:text-stone-900 dark:text-obsidian-300 dark:hover:text-white'
            "
            :aria-pressed="option.dark === isDark ? 'true' : 'false'"
            @click="setTheme(option.dark ? 'dark' : 'light')"
        >
            <svg
                class="h-4 w-4"
                :class="option.dark ? 'dark:text-champagne-300' : 'text-champagne-600 dark:text-champagne-300'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path :d="option.icon" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            {{ option.label }}
        </button>
    </div>
</template>
