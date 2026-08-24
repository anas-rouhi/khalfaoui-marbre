<script setup>
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();

const services = [
    { key: 'worktops', icon: 'M3 10h18M5 10V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3M6 10v9M18 10v9' },
    { key: 'floors', icon: 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z' },
    { key: 'stairs', icon: 'M4 20h4v-4h4v-4h4V8h4' },
    { key: 'bathrooms', icon: 'M4 12h16v3a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5zM7 12V6a2 2 0 0 1 4 0' },
    { key: 'facades', icon: 'M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6' },
    { key: 'building', icon: 'm14 7 3-3 4 4-3 3M14 7 4 17v3h3L17 10M14 7l3 3' },
];
</script>

<template>
    <section class="relative border-y border-white/5 bg-obsidian-900 py-24 sm:py-28">
        <div class="mx-auto max-w-7xl px-6">
            <div class="max-w-2xl" data-reveal>
                <p class="section-label">
                    <span class="h-px w-10 bg-brand-500" aria-hidden="true" />
                    {{ t('services.label') }}
                </p>
                <h2 class="heading-display mt-6">{{ t('services.title') }}</h2>
                <p class="mt-5 text-base leading-relaxed text-obsidian-300">
                    {{ t('services.lead') }}
                </p>
            </div>

            <div class="mt-14 grid gap-px overflow-hidden rounded-3xl border border-white/10 bg-white/10 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="(service, index) in services"
                    :key="service.key"
                    class="group bg-obsidian-900 p-8 transition-colors duration-500 hover:bg-obsidian-800"
                    data-reveal
                    :data-reveal-delay="index * 70"
                >
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-brand-500/15 text-brand-400 transition-all duration-500 group-hover:bg-brand-500 group-hover:text-white">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path :d="service.icon" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>

                    <h3 class="mt-6 font-display text-xl font-medium text-white">{{ t(`services.${service.key}.title`) }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-obsidian-400">{{ t(`services.${service.key}.text`) }}</p>
                </article>
            </div>
        </div>
    </section>
</template>
