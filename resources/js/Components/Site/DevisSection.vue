<script setup>
import { computed, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useCompany } from '@/Composables/useCompany';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps({
    products: { type: Array, default: () => [] },
    /** Produit pré-sélectionné depuis le catalogue. */
    selected: { type: Object, default: null },
    /**
     * Tarifs de pose au m², administrés depuis /admin › Réglages.
     * Format : [{ key, label, ratePerM2 }]
     */
    installationRates: { type: Array, default: () => [] },
});

const page = usePage();
const { company, whatsappLink } = useCompany();
const { t, locale } = useI18n();

/** Repli si la table des tarifs est vide, pour que l'estimateur reste utilisable. */
const FALLBACK_APPLICATIONS = [
    { key: 'cuisine', ratePerM2: 220 },
    { key: 'sol', ratePerM2: 120 },
    { key: 'salle-de-bain', ratePerM2: 180 },
    { key: 'escalier', ratePerM2: 300 },
    { key: 'facade', ratePerM2: 260 },
];

// Le libellé administré reste la référence ; s'il n'est pas traduit, on
// retombe sur le dictionnaire par clé d'usage.
const applicationLabel = (app) =>
    locale.value === 'fr' && app?.label ? app.label : t(`application.${app?.key}`);

const APPLICATIONS = computed(() =>
    props.installationRates.length ? props.installationRates : FALLBACK_APPLICATIONS,
);

// Première pose proposée : « cuisine » n'est plus garanti, le gérant pouvant
// désactiver n'importe quel type depuis le back-office.
const defaultApplication = APPLICATIONS.value[0]?.key ?? 'cuisine';

const form = useForm({
    client_name: '',
    phone: '',
    email: '',
    location: '',
    product_id: null,
    application: defaultApplication,
    surface_m2: 12,
    message: '',
});

// Le catalogue peut pousser une référence ici via l'événement « request-quote ».
watch(
    () => props.selected,
    (product) => {
        if (!product) {
            return;
        }

        form.product_id = product.id;

        // On n'adopte l'usage de la fiche que s'il correspond à un tarif
        // proposé, sinon le bouton sélectionné n'existerait pas à l'écran.
        const usage = (product.applications ?? []).find((key) =>
            APPLICATIONS.value.some((app) => app.key === key),
        );

        if (usage) {
            form.application = usage;
        }
    },
);

const selectedProduct = computed(
    () => props.products.find((product) => product.id === form.product_id) ?? null,
);

const surface = computed(() => Number(form.surface_m2) || 0);

const materialCost = computed(() =>
    selectedProduct.value?.pricePerM2 ? selectedProduct.value.pricePerM2 * surface.value : 0,
);

const currentApplication = computed(
    () => APPLICATIONS.value.find((app) => app.key === form.application) ?? null,
);

// 150 DH/m² : même valeur de repli que InstallationRate::FALLBACK_RATE côté
// serveur, pour que l'écran et le montant enregistré concordent.
const poseCost = computed(() => (currentApplication.value?.ratePerM2 ?? 150) * surface.value);

const total = computed(() => materialCost.value + poseCost.value);

const canEstimate = computed(() => Boolean(selectedProduct.value?.pricePerM2) && surface.value > 0);

// fr-FR plutôt que fr-MA : le séparateur de milliers est une espace insécable
// et non un point, qu'on pourrait confondre avec une virgule décimale.
const formatMAD = (value) =>
    new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Math.round(value));

/** Message WhatsApp reprenant l'intégralité de l'estimation. */
const whatsappOrder = computed(() => {
    const lines = [
        t('whatsapp.order_intro', { company: company.value.name }),
        '',
        `• ${t('whatsapp.order_material')} : ${selectedProduct.value?.name ?? t('whatsapp.order_tbd')}`,
        `• ${t('whatsapp.order_application')} : ${currentApplication.value ? applicationLabel(currentApplication.value) : '—'}`,
        `• ${t('whatsapp.order_surface')} : ${surface.value} ${t('quote.unit')}`,
    ];

    if (canEstimate.value) {
        lines.push(`• ${t('whatsapp.order_estimate')} : ~${formatMAD(total.value)} ${t('quote.currency')}`);
    }

    lines.push(
        '',
        `${t('whatsapp.order_name')} : ${form.client_name || '—'}`,
        `${t('whatsapp.order_phone')} : ${form.phone || '—'}`,
    );

    if (form.location) {
        lines.push(`${t('whatsapp.order_location')} : ${form.location}`);
    }

    if (form.message) {
        lines.push('', `${t('whatsapp.order_details')} : ${form.message}`);
    }

    return whatsappLink(lines.join('\n'));
});

const succeeded = ref(false);

// ── Devis PDF ──
const pdfLoading = ref(false);
const pdfError = ref(null);

const readCookie = (name) =>
    document.cookie.split('; ').find((c) => c.startsWith(`${name}=`))?.split('=')[1];

/**
 * Le PDF arrive en pièce jointe : on passe par fetch plutôt que par Inertia,
 * qui ne sait pas traiter une réponse binaire, et on récupère ainsi les
 * erreurs de validation sans quitter la page.
 */
const downloadPdf = async () => {
    pdfError.value = null;
    pdfLoading.value = true;
    form.clearErrors();

    try {
        const response = await fetch(route('devis.pdf'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/pdf',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(readCookie('XSRF-TOKEN') ?? ''),
            },
            body: JSON.stringify(form.data()),
            // Garde-fou : sans cela, une redirection serait suivie puis
            // enregistrée telle quelle sous le nom « devis.pdf ».
            redirect: 'error',
        });

        if (response.status === 422) {
            const { errors } = await response.json();
            form.setError(
                Object.fromEntries(Object.entries(errors).map(([key, messages]) => [key, messages[0]])),
            );
            pdfError.value = t('quote.pdf_validation');

            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download =
            response.headers.get('Content-Disposition')?.match(/filename="?([^";]+)"?/)?.[1] ?? 'devis.pdf';

        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    } catch {
        pdfError.value = t('quote.pdf_error');
    } finally {
        pdfLoading.value = false;
    }
};

const submit = () => {
    form.post(route('devis.store'), {
        preserveScroll: true,
        onSuccess: () => {
            succeeded.value = true;
            form.reset('client_name', 'phone', 'email', 'location', 'message');
        },
    });
};
</script>

<template>
    <section id="devis" class="relative overflow-hidden bg-obsidian-950 py-24 sm:py-32">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_75%_15%,rgba(30,157,107,0.16),transparent_58%)]" aria-hidden="true" />

        <div class="relative mx-auto max-w-7xl px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <p class="section-label justify-center">
                    <span class="h-px w-10 bg-brand-500" aria-hidden="true" />
                    {{ t('quote.label') }}
                    <span class="h-px w-10 bg-brand-500" aria-hidden="true" />
                </p>
                <h2 class="heading-display mt-6">{{ t('quote.title') }}</h2>
                <p class="mt-5 text-base leading-relaxed text-obsidian-300">
                    {{ t('quote.lead') }}
                </p>
            </div>

            <div class="mt-16 grid gap-6 lg:grid-cols-5" data-reveal data-reveal-delay="120">
                <!-- Formulaire -->
                <form
                    class="rounded-3xl border border-white/10 bg-obsidian-900/70 p-6 backdrop-blur-xl sm:p-9 lg:col-span-3"
                    novalidate
                    @submit.prevent="submit"
                >
                    <fieldset :disabled="form.processing" class="space-y-7">
                        <!-- Étape 1 : le matériau -->
                        <div>
                            <legend class="mb-4 flex items-center gap-3 text-sm font-semibold text-white">
                                <span class="grid h-6 w-6 place-items-center rounded-full bg-brand-500 text-[0.7rem]">1</span>
                                {{ t('quote.step_material') }}
                            </legend>

                            <label class="field-label" for="devis-product">{{ t('quote.material_label') }}</label>
                            <select id="devis-product" v-model="form.product_id" class="field">
                                <option :value="null">{{ t('quote.material_unknown') }}</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                    <template v-if="product.pricePerM2"> — {{ formatMAD(product.pricePerM2) }} DH/m²</template>
                                </option>
                            </select>
                            <p v-if="form.errors.product_id" class="mt-2 text-xs text-red-400">{{ form.errors.product_id }}</p>
                        </div>

                        <!-- Étape 2 : usage et surface -->
                        <div>
                            <legend class="mb-4 flex items-center gap-3 text-sm font-semibold text-white">
                                <span class="grid h-6 w-6 place-items-center rounded-full bg-brand-500 text-[0.7rem]">2</span>
                                {{ t('quote.step_surface') }}
                            </legend>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="app in APPLICATIONS"
                                    :key="app.key"
                                    type="button"
                                    class="rounded-full border px-4 py-2 text-xs font-medium transition"
                                    :class="
                                        form.application === app.key
                                            ? 'border-brand-400 bg-brand-500 text-white'
                                            : 'border-white/10 bg-white/5 text-obsidian-300 hover:border-white/25 hover:text-white'
                                    "
                                    @click="form.application = app.key"
                                >
                                    {{ applicationLabel(app) }}
                                </button>
                            </div>

                            <div class="mt-6">
                                <div class="mb-2 flex items-end justify-between">
                                    <label class="field-label mb-0" for="devis-surface">{{ t('quote.surface_label') }}</label>
                                    <span class="font-display text-2xl text-brand-400">{{ surface }} {{ t('quote.unit') }}</span>
                                </div>

                                <input
                                    id="devis-surface"
                                    v-model.number="form.surface_m2"
                                    type="range"
                                    min="1"
                                    max="300"
                                    step="1"
                                    class="h-1.5 w-full cursor-pointer appearance-none rounded-full bg-white/10 accent-brand-500"
                                />

                                <div class="mt-3 flex items-center gap-3">
                                    <input
                                        v-model.number="form.surface_m2"
                                        type="number"
                                        min="0.5"
                                        step="0.5"
                                        class="field w-32"
                                        :aria-label="t('quote.surface_exact_aria')"
                                    />
                                    <span class="text-sm text-obsidian-400">{{ t('quote.surface_hint') }}</span>
                                </div>
                                <p v-if="form.errors.surface_m2" class="mt-2 text-xs text-red-400">{{ form.errors.surface_m2 }}</p>
                            </div>
                        </div>

                        <!-- Étape 3 : coordonnées -->
                        <div>
                            <legend class="mb-4 flex items-center gap-3 text-sm font-semibold text-white">
                                <span class="grid h-6 w-6 place-items-center rounded-full bg-brand-500 text-[0.7rem]">3</span>
                                {{ t('quote.step_contact') }}
                            </legend>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="field-label" for="devis-name">{{ t('quote.name') }}</label>
                                    <input id="devis-name" v-model="form.client_name" type="text" class="field" :placeholder="t('quote.name_placeholder')" required />
                                    <p v-if="form.errors.client_name" class="mt-2 text-xs text-red-400">{{ form.errors.client_name }}</p>
                                </div>

                                <div>
                                    <label class="field-label" for="devis-phone">{{ t('quote.phone') }}</label>
                                    <input id="devis-phone" v-model="form.phone" type="tel" class="field" :placeholder="t('quote.phone_placeholder')" required />
                                    <p v-if="form.errors.phone" class="mt-2 text-xs text-red-400">{{ form.errors.phone }}</p>
                                </div>

                                <div>
                                    <label class="field-label" for="devis-email">{{ t('quote.email') }}</label>
                                    <input id="devis-email" v-model="form.email" type="email" class="field" :placeholder="t('quote.email_placeholder')" />
                                    <p v-if="form.errors.email" class="mt-2 text-xs text-red-400">{{ form.errors.email }}</p>
                                </div>

                                <div>
                                    <label class="field-label" for="devis-location">{{ t('quote.city') }}</label>
                                    <input id="devis-location" v-model="form.location" type="text" class="field" :placeholder="t('quote.city_placeholder')" />
                                    <p v-if="form.errors.location" class="mt-2 text-xs text-red-400">{{ form.errors.location }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="field-label" for="devis-message">{{ t('quote.message') }}</label>
                                <textarea id="devis-message" v-model="form.message" rows="3" class="field" :placeholder="t('quote.message_placeholder')" />
                                <p v-if="form.errors.message" class="mt-2 text-xs text-red-400">{{ form.errors.message }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row">
                            <button type="submit" class="btn-primary flex-1 py-4" :class="{ 'opacity-70': form.processing }">
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3" />
                                    <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                </svg>
                                {{ form.processing ? t('quote.submitting') : t('quote.submit') }}
                            </button>

                            <a :href="whatsappOrder" target="_blank" rel="noopener" class="btn-ghost flex-1 py-4">
                                {{ t('quote.whatsapp') }}
                            </a>
                        </div>

                        <!-- Devis PDF : disponible sans passer par l'envoi du
                             formulaire, mais soumis à la même validation. -->
                        <button
                            type="button"
                            class="group flex w-full items-center justify-center gap-2.5 rounded-full border border-brand-500/40 bg-brand-500/10 py-4 text-sm font-semibold text-brand-300 transition-all duration-300 hover:border-brand-400 hover:bg-brand-500 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-obsidian-950 disabled:opacity-60"
                            :disabled="pdfLoading"
                            @click="downloadPdf"
                        >
                            <svg v-if="pdfLoading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3" />
                                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            <svg v-else class="h-4 w-4 transition-transform duration-300 group-hover:translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 3v12m0 0-4.5-4.5M12 15l4.5-4.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" stroke-linecap="round" />
                            </svg>
                            {{ pdfLoading ? t('quote.pdf_loading') : t('quote.pdf') }}
                        </button>

                        <p v-if="pdfError" class="text-xs text-red-400" role="alert">{{ pdfError }}</p>

                        <p class="text-xs leading-relaxed text-obsidian-500">
                            {{ t('quote.required_note') }}
                        </p>
                    </fieldset>
                </form>

                <!-- Récapitulatif chiffré -->
                <aside class="lg:col-span-2">
                    <div class="sticky top-28 space-y-4">
                        <div class="overflow-hidden rounded-3xl border border-brand-500/25 bg-gradient-to-b from-brand-950/60 to-obsidian-900/80 backdrop-blur-xl">
                            <div class="border-b border-white/10 px-7 py-5">
                                <h3 class="font-display text-xl font-light text-white">{{ t('quote.summary_title') }}</h3>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-brand-400">{{ t('quote.summary_live') }}</p>
                            </div>

                            <dl class="space-y-4 px-7 py-6 text-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-obsidian-400">{{ t('quote.summary_material') }}</dt>
                                    <dd class="text-end font-medium text-white">
                                        {{ selectedProduct?.name ?? t('quote.summary_tbd') }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-obsidian-400">{{ t('quote.summary_application') }}</dt>
                                    <dd class="text-end font-medium text-white">
                                        {{ currentApplication ? applicationLabel(currentApplication) : '—' }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-obsidian-400">{{ t('quote.summary_surface') }}</dt>
                                    <dd class="font-medium text-white">{{ surface }} {{ t('quote.unit') }}</dd>
                                </div>

                                <div v-if="canEstimate" class="space-y-3 border-t border-white/10 pt-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-obsidian-400">{{ t('quote.summary_supply') }}</dt>
                                        <dd class="text-white">{{ formatMAD(materialCost) }} {{ t('quote.currency') }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-obsidian-400">{{ t('quote.summary_install') }}</dt>
                                        <dd class="text-white">{{ formatMAD(poseCost) }} {{ t('quote.currency') }}</dd>
                                    </div>
                                </div>
                            </dl>

                            <div class="border-t border-white/10 bg-obsidian-950/50 px-7 py-6">
                                <template v-if="canEstimate">
                                    <p class="text-xs uppercase tracking-[0.2em] text-obsidian-400">{{ t('quote.summary_total') }}</p>
                                    <p class="mt-2 font-display text-4xl font-light text-brand-300">
                                        {{ formatMAD(total) }}
                                        <span class="font-sans text-base text-obsidian-400">{{ t('quote.currency') }}</span>
                                    </p>
                                    <p class="mt-3 text-xs leading-relaxed text-obsidian-500">
                                        {{ t('quote.summary_disclaimer') }}
                                    </p>
                                </template>
                                <template v-else>
                                    <p class="text-sm leading-relaxed text-obsidian-400">
                                        {{ t('quote.summary_empty') }}
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- Confirmation d'envoi -->
                        <Transition
                            enter-active-class="transition duration-500 ease-out"
                            enter-from-class="translate-y-3 opacity-0"
                        >
                            <div
                                v-if="succeeded && page.props.flash?.success"
                                class="flex items-start gap-3 rounded-2xl border border-brand-500/40 bg-brand-950/50 p-5 text-sm text-brand-100"
                                role="status"
                            >
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                {{ page.props.flash.success }}
                            </div>
                        </Transition>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-sm text-obsidian-300">
                            <p class="font-medium text-white">{{ t('quote.faster_title') }}</p>
                            <p class="mt-1.5 leading-relaxed">
                                {{ t('quote.faster_text') }}
                                <a :href="`tel:${company.phone_e164}`" class="text-brand-400 transition hover:underline">
                                    <bdi dir="ltr">{{ company.phone_display }}</bdi>
                                </a>.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>
