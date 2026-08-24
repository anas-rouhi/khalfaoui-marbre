<script setup>
import { useCompany } from '@/Composables/useCompany';
import { useI18n } from '@/Composables/useI18n';

const { company, telLink, mailLink, whatsappLink, mapsEmbedUrl, mapsDirectionsUrl } = useCompany();
const { t } = useI18n();
</script>

<template>
    <section id="contact" class="relative bg-obsidian-900 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <p class="section-label justify-center">
                    <span class="h-px w-10 bg-brand-500" aria-hidden="true" />
                    {{ t('contact.label') }}
                    <span class="h-px w-10 bg-brand-500" aria-hidden="true" />
                </p>
                <h2 class="heading-display mt-6">{{ t('contact.title') }}</h2>
                <p class="mt-5 text-base leading-relaxed text-obsidian-300">
                    {{ t('contact.lead') }}
                </p>
            </div>

            <div class="mt-16 grid gap-6 lg:grid-cols-5" data-reveal data-reveal-delay="120">
                <!-- Coordonnées -->
                <div class="space-y-4 lg:col-span-2">
                    <a
                        :href="mapsDirectionsUrl"
                        target="_blank"
                        rel="noopener"
                        class="group flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-6 transition-all duration-300 hover:border-brand-500/50 hover:bg-white/10"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-500/15 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs uppercase tracking-[0.2em] text-obsidian-400">{{ t('contact.address') }}</span>
                            <span class="mt-1.5 block font-medium text-white">{{ company.address }}</span>
                            <span class="mt-1 block text-sm text-brand-400">{{ t('contact.directions') }}</span>
                        </span>
                    </a>

                    <a
                        :href="telLink"
                        class="group flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-6 transition-all duration-300 hover:border-brand-500/50 hover:bg-white/10"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-500/15 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs uppercase tracking-[0.2em] text-obsidian-400">{{ t('contact.phone') }}</span>
                            <span class="mt-1.5 block font-medium text-white"><bdi dir="ltr">{{ company.phone_display }}</bdi></span>
                        </span>
                    </a>

                    <a
                        :href="mailLink"
                        class="group flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-6 transition-all duration-300 hover:border-brand-500/50 hover:bg-white/10"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-500/15 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="m2 7 10 6 10-6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs uppercase tracking-[0.2em] text-obsidian-400">{{ t('contact.email') }}</span>
                            <span class="mt-1.5 block break-all font-medium text-white">{{ company.email }}</span>
                        </span>
                    </a>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <p class="text-xs uppercase tracking-[0.2em] text-obsidian-400">{{ t('contact.hours') }}</p>
                        <dl class="mt-4 space-y-2.5 text-sm">
                            <div v-for="slot in company.hours" :key="slot.days" class="flex justify-between gap-4">
                                <dt class="text-obsidian-300">{{ slot.key ? t(slot.key) : slot.days }}</dt>
                                <dd class="font-medium text-white">{{ slot.value_key ? t(slot.value_key) : slot.time }}</dd>
                            </div>
                        </dl>
                    </div>

                    <a
                        :href="whatsappLink(t('whatsapp.visit'))"
                        target="_blank"
                        rel="noopener"
                        class="btn-primary w-full py-4"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.33 4.97L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91a9.85 9.85 0 0 0-9.91-9.93Zm5.43 12.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.3-.02-.46.13-.6.14-.14.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35Z" />
                        </svg>
                        {{ t('contact.whatsapp') }}
                    </a>
                </div>

                <!-- Carte -->
                <div class="relative min-h-[420px] overflow-hidden rounded-3xl border border-white/10 lg:col-span-3">
                    <iframe
                        :src="mapsEmbedUrl"
                        class="absolute inset-0 h-full w-full"
                        style="border: 0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                        :title="t('contact.map_title', { company: company.legal_name, address: company.address })"
                    />

                    <!-- Cartouche d'adresse posé sur la carte -->
                    <div class="pointer-events-none absolute inset-x-4 bottom-4 sm:inset-x-6 sm:bottom-6">
                        <div class="pointer-events-auto flex flex-col gap-4 rounded-2xl border border-white/15 bg-obsidian-950/85 p-5 backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-display text-lg text-white">{{ company.legal_name }}</p>
                                <p class="mt-1 text-sm text-obsidian-300">{{ company.address }}</p>
                            </div>
                            <a
                                :href="mapsDirectionsUrl"
                                target="_blank"
                                rel="noopener"
                                class="shrink-0 rounded-full border border-brand-500/50 px-5 py-2.5 text-sm font-semibold text-brand-300 transition hover:bg-brand-500 hover:text-white"
                            >
                                {{ t('contact.directions_short') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
