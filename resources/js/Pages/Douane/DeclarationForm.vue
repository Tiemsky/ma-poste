<script setup>
import { ref, computed, nextTick } from 'vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CNIUpload from '@/Components/CNIUpload.vue';
import InvoiceUpload from '@/Components/InvoiceUpload.vue';

const props = defineProps({
    service: Object,
    userData: Object,
});

const page = usePage();
const currentStep = ref(1);
const clientErrors = ref({});
const cniUploaded = ref({ recto: false, verso: false });

// ── Formulaire Inertia avec clés plates ──────────────────────────────────────
const form = useForm({
  // Step 1 — Identité
    service_id: props.service.id,
    declarant_type: 'individual',
    declarant_name: props.userData?.name || '',
    declarant_id_number: '',
    cni_recto: null,
    cni_verso: null,

    // Step 2 — Transport
    transport_mode: 'air',
    departure_country: '',
    arrival_country: "Côte d'Ivoire",
    transport_company: '',
    tracking_number: '',

    // Step 3 — Articles (sera sérialisé en JSON string)
    items_json: '',

    // Step 4 — Facture
    invoice: null,
});

// Items gérés localement (réactif), sérialisés avant soumission
const items = ref([
    { name: '', category: '', quantity: 1, unit_value: 0, weight: '', origin_country: '' },
]);

const steps = [
    { id: 1, title: 'Identité' },
    { id: 2, title: 'Transport' },
    { id: 3, title: 'Articles' },
    { id: 4, title: 'Documents' },
    { id: 5, title: 'Confirmation' },
];

const stepTitle = computed(() => ({
    1: 'Informations du déclarant',
    2: 'Détails du transport',
    3: 'Liste des marchandises',
    4: 'Facture & documents',
    5: 'Récapitulatif final',
}[currentStep.value]));

const transportModes = [
    { value: 'air', label: 'Aérien', icon: '✈' },
    { value: 'sea', label: 'Maritime', icon: '🚢' },
    { value: 'road', label: 'Routier', icon: '🚛' },
];

const itemCategories = [
    'Électronique', 'Vêtements & Textiles', 'Alimentation', 'Cosmétiques',
    'Médicaments', 'Matériaux de construction', 'Machines & Équipements',
    'Mobilier', 'Véhicules & Pièces', 'Autre',
];

// ── Calculs ──────────────────────────────────────────────────────────────────
const globalTotal = computed(() =>
    items.value.reduce((acc, item) => acc + (Number(item.quantity) * Number(item.unit_value)), 0)
);

const formatCurrency = (value) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(value || 0);

// ── Gestion des articles ─────────────────────────────────────────────────────
const addItem = () => {
    items.value.push({ name: '', category: '', quantity: 1, unit_value: 0, weight: '', origin_country: '' });
};

const removeItem = (index) => {
    if (items.value.length > 1) items.value.splice(index, 1);
};

// ── Uploads ──────────────────────────────────────────────────────────────────
const handleCNIUploaded = (files) => {
    if (files.recto instanceof File) { form.cni_recto = files.recto; cniUploaded.value.recto = true; }
    if (files.verso instanceof File) { form.cni_verso = files.verso; cniUploaded.value.verso = true; }
};

const handleInvoiceUploaded = (file) => {
    if (file instanceof File) form.invoice = file;
};

// ── Validation par step ───────────────────────────────────────────────────────
const validateStep = (step) => {
    const errors = {};
    const req = (field, msg) => { if (!form[field] || String(form[field]).trim() === '') errors[field] = msg; };

    if (step === 1) {
        req('declarant_name', 'Nom / raison sociale requis');
        if (!(form.cni_recto instanceof File)) errors.cni_recto = 'Recto CNI requis';
        if (!(form.cni_verso instanceof File)) errors.cni_verso = 'Verso CNI requis';
    }

    if (step === 2) {
        req('departure_country', 'Pays de départ requis');
        req('transport_company', 'Compagnie de transport requise');
    }

    if (step === 3) {
        const invalid = items.value.some(
            (it) => !it.name.trim() || !it.category || !it.origin_country.trim() || Number(it.quantity) < 1 || Number(it.unit_value) <= 0
        );
        if (invalid) errors.items = 'Tous les articles doivent être complets (nom, catégorie, origine, quantité > 0, valeur > 0)';
        if (items.value.length === 0) errors.items = 'Ajoutez au moins un article';
    }

    if (step === 4) {
        if (!(form.invoice instanceof File)) errors.invoice = 'La facture est requise';
    }

    clientErrors.value = errors;
    return Object.keys(errors).length === 0;
};

// ── Scroll ────────────────────────────────────────────────────────────────────
const formCardRef = ref(null);
const scrollToFormTop = () => {
    nextTick(() => {
        if (formCardRef.value) {
            const top = formCardRef.value.getBoundingClientRect().top + window.scrollY - 24;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    });
};

// ── Navigation ────────────────────────────────────────────────────────────────
const nextStep = () => {
    if (validateStep(currentStep.value) && currentStep.value < steps.length) {
        currentStep.value++;
        scrollToFormTop();
    }
};

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        clientErrors.value = {};
        scrollToFormTop();
    }
};

// ── Soumission ────────────────────────────────────────────────────────────────
const submit = () => {
    // Sérialisation des articles avant envoi (le backend les reçoit en JSON)
    form.items_json = JSON.stringify(
        items.value.map((it) => ({
            ...it,
            quantity: Number(it.quantity),
            unit_value: Number(it.unit_value),
            total_value: Number(it.quantity) * Number(it.unit_value),
        }))
    );

    form.post(route('guichet.douane.store'), {
        forceFormData: true,
        onError: () => scrollToFormTop(),
    });
};
</script>

<template>
    <Head :title="`Douane - ${service?.name || 'Déclaration'}`" />
    <AuthenticatedLayout :user="page.props.auth.user">
        <div class="max-w-5xl mx-auto px-4 py-8">

            <!-- ── Header ── -->
            <div class="mb-8 text-center">
                <div class="inline-flex items-center px-4 py-2 bg-amber-50 text-amber-800 rounded-full text-sm font-bold mb-4 shadow-sm border border-amber-200">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-5h3l2 3H10V9h7a1 1 0 011 1v5h.95a2.5 2.5 0 014.9 0H21a1 1 0 001-1v-5a1 1 0 00-1-1h-1V5a1 1 0 00-1-1H3z"/>
                    </svg>
                    Déclaration en douane
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ service?.name || 'Déclaration de marchandises' }}</h1>
                <p class="text-gray-500 max-w-2xl mx-auto">{{ service?.description }}</p>
            </div>

            <!-- ── Progress bar ── -->
            <div class="mb-10 relative">
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2 rounded-full"></div>
                <div class="absolute top-1/2 left-0 h-1 bg-amber-500 -translate-y-1/2 rounded-full transition-all duration-500 ease-in-out"
                     :style="{ width: `${((currentStep - 1) / (steps.length - 1)) * 100}%` }"></div>
                <div class="relative flex justify-between">
                    <div v-for="step in steps" :key="step.id" class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-4 transition-all duration-300 mb-2 text-sm font-bold shadow-sm"
                             :class="{
                                 'bg-amber-500 border-amber-500 text-white': currentStep > step.id,
                                 'bg-amber-500 border-amber-500 text-white ring-4 ring-amber-100': currentStep === step.id,
                                 'bg-white border-gray-200 text-gray-400': currentStep < step.id,
                             }">
                            <span v-if="currentStep > step.id">✓</span>
                            <span v-else>{{ step.id }}</span>
                        </div>
                        <span class="text-xs font-bold text-center transition-colors duration-300 hidden sm:block"
                              :class="currentStep >= step.id ? 'text-amber-700' : 'text-gray-400'">
                            {{ step.title }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── Card formulaire ── -->
            <div ref="formCardRef" class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 border-b pb-4">{{ stepTitle }}</h2>

                    <!-- Erreurs serveur -->
                    <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl">
                        <p class="font-bold mb-1">Erreurs de validation :</p>
                        <ul class="text-sm list-disc pl-4 space-y-1">
                            <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                        </ul>
                    </div>

                    <Transition name="step" mode="out-in">
                        <div :key="currentStep">

                            <!-- ══ STEP 1 — Identité ══ -->
                            <template v-if="currentStep === 1">
                                <div class="space-y-6">

                                    <!-- Type de déclarant -->
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Type de déclarant <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <button type="button" @click="form.declarant_type = 'individual'"
                                                    class="p-4 border-2 rounded-xl font-bold text-sm transition-all"
                                                    :class="form.declarant_type === 'individual'
                                                        ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                        : 'border-gray-200 text-gray-500 hover:border-amber-300'">
                                                <span class="block text-2xl mb-1">👤</span>
                                                Particulier
                                            </button>
                                            <button type="button" @click="form.declarant_type = 'company'"
                                                    class="p-4 border-2 rounded-xl font-bold text-sm transition-all"
                                                    :class="form.declarant_type === 'company'
                                                        ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                        : 'border-gray-200 text-gray-500 hover:border-amber-300'">
                                                <span class="block text-2xl mb-1">🏢</span>
                                                Entreprise
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                                {{ form.declarant_type === 'individual' ? 'Nom complet' : 'Raison sociale' }}
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input v-model="form.declarant_name" type="text"
                                                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-500 transition-colors"
                                                   :class="clientErrors.declarant_name ? 'border-red-300 bg-red-50' : 'border-gray-300'" />
                                            <p v-if="clientErrors.declarant_name" class="text-red-500 text-xs mt-1">{{ clientErrors.declarant_name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                                {{ form.declarant_type === 'individual' ? 'N° CNI / Passeport' : 'N° RCCM / IFU' }}
                                                <span class="text-gray-400 text-xs font-normal">(optionnel)</span>
                                            </label>
                                            <input v-model="form.declarant_id_number" type="text"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" />
                                        </div>
                                    </div>

                                    <!-- CNI Upload -->
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Pièce d'identité (CNI recto/verso) <span class="text-red-500">*</span></label>
                                        <CNIUpload @uploaded="handleCNIUploaded" />
                                        <div v-if="clientErrors.cni_recto || clientErrors.cni_verso" class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3 space-y-1">
                                            <p v-if="clientErrors.cni_recto" class="text-red-600 text-sm font-semibold">{{ clientErrors.cni_recto }}</p>
                                            <p v-if="clientErrors.cni_verso" class="text-red-600 text-sm font-semibold">{{ clientErrors.cni_verso }}</p>
                                        </div>
                                        <div v-if="form.cni_recto || form.cni_verso" class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3 space-y-1">
                                            <p v-if="form.cni_recto" class="text-green-700 text-sm flex items-center gap-2">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                Recto : {{ form.cni_recto.name }}
                                            </p>
                                            <p v-if="form.cni_verso" class="text-green-700 text-sm flex items-center gap-2">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                Verso : {{ form.cni_verso.name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- ══ STEP 2 — Transport ══ -->
                            <template v-if="currentStep === 2">
                                <div class="space-y-6">

                                    <!-- Mode de transport -->
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Mode de transport <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-3 gap-4">
                                            <button v-for="mode in transportModes" :key="mode.value"
                                                    type="button" @click="form.transport_mode = mode.value"
                                                    class="p-4 border-2 rounded-xl font-bold text-sm transition-all text-center"
                                                    :class="form.transport_mode === mode.value
                                                        ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                        : 'border-gray-200 text-gray-500 hover:border-amber-300'">
                                                <span class="block text-2xl mb-1">{{ mode.icon }}</span>
                                                {{ mode.label }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Pays de départ <span class="text-red-500">*</span></label>
                                            <input v-model="form.departure_country" type="text" placeholder="Ex: France, Chine, USA…"
                                                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-500 transition-colors"
                                                   :class="clientErrors.departure_country ? 'border-red-300 bg-red-50' : 'border-gray-300'" />
                                            <p v-if="clientErrors.departure_country" class="text-red-500 text-xs mt-1">{{ clientErrors.departure_country }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Pays d'arrivée</label>
                                            <input v-model="form.arrival_country" type="text"
                                                   class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed" disabled />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Compagnie de transport <span class="text-red-500">*</span></label>
                                            <input v-model="form.transport_company" type="text" placeholder="Ex: Air France, MSC, DHL…"
                                                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-500 transition-colors"
                                                   :class="clientErrors.transport_company ? 'border-red-300 bg-red-50' : 'border-gray-300'" />
                                            <p v-if="clientErrors.transport_company" class="text-red-500 text-xs mt-1">{{ clientErrors.transport_company }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">N° de tracking / connaissement <span class="text-gray-400 text-xs font-normal">(optionnel)</span></label>
                                            <input v-model="form.tracking_number" type="text" placeholder="Ex: 1Z999AA10123456784"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono" />
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- ══ STEP 3 — Articles ══ -->
                            <template v-if="currentStep === 3">
                                <div class="space-y-4">

                                    <!-- Erreur globale articles -->
                                    <div v-if="clientErrors.items" class="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-red-600 text-sm font-semibold">{{ clientErrors.items }}</p>
                                    </div>

                                    <!-- Liste des articles -->
                                    <TransitionGroup name="item-list" tag="div" class="space-y-4">
                                        <div v-for="(item, index) in items" :key="index"
                                             class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50">

                                            <!-- Header article -->
                                            <div class="flex items-center justify-between px-5 py-3 bg-white border-b border-gray-100">
                                                <span class="text-sm font-bold text-gray-700">
                                                    Article {{ index + 1 }}
                                                    <span v-if="item.name" class="text-amber-600 font-semibold ml-1">— {{ item.name }}</span>
                                                </span>
                                                <button v-if="items.length > 1" type="button" @click="removeItem(index)"
                                                        class="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700 font-semibold hover:bg-red-50 px-2 py-1 rounded-lg transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    Retirer
                                                </button>
                                            </div>

                                            <!-- Corps article -->
                                            <div class="p-5 space-y-4">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 mb-1">Désignation <span class="text-red-500">*</span></label>
                                                        <input v-model="item.name" type="text" placeholder="Ex: Téléphone portable"
                                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 text-sm" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 mb-1">Catégorie <span class="text-red-500">*</span></label>
                                                        <select v-model="item.category"
                                                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 text-sm">
                                                            <option value="" disabled>Sélectionner…</option>
                                                            <option v-for="cat in itemCategories" :key="cat" :value="cat">{{ cat }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 mb-1">Quantité <span class="text-red-500">*</span></label>
                                                        <input v-model.number="item.quantity" type="number" min="1"
                                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 text-sm font-mono" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 mb-1">Valeur unitaire (FCFA) <span class="text-red-500">*</span></label>
                                                        <input v-model.number="item.unit_value" type="number" min="0" step="100"
                                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 text-sm font-mono" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 mb-1">Poids <span class="text-gray-400 font-normal">(optionnel)</span></label>
                                                        <input v-model="item.weight" type="text" placeholder="Ex: 2.5kg"
                                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 text-sm" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 mb-1">Pays d'origine <span class="text-red-500">*</span></label>
                                                        <input v-model="item.origin_country" type="text" placeholder="Ex: Chine"
                                                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400 text-sm" />
                                                    </div>
                                                </div>

                                                <!-- Sous-total article -->
                                                <div class="flex justify-end">
                                                    <div class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2">
                                                        <span class="text-xs font-semibold text-amber-700">Sous-total :</span>
                                                        <span class="text-sm font-bold text-amber-800 font-mono">
                                                            {{ formatCurrency(item.quantity * item.unit_value) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </TransitionGroup>

                                    <!-- Bouton ajouter un article -->
                                    <button type="button" @click="addItem"
                                            class="w-full py-3 border-2 border-dashed border-amber-300 rounded-xl text-amber-600 font-bold text-sm hover:border-amber-500 hover:bg-amber-50 transition-all flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Ajouter un article
                                    </button>

                                    <!-- Total global -->
                                    <div class="flex justify-end pt-2">
                                        <div class="bg-gray-900 text-white rounded-xl px-8 py-4 flex items-center gap-6">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-0.5">{{ items.length }} article(s) — Valeur totale déclarée</p>
                                                <p class="text-2xl font-extrabold font-mono">{{ formatCurrency(globalTotal) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- ══ STEP 4 — Documents ══ -->
                            <template v-if="currentStep === 4">
                                <div class="space-y-6">
                                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                                        <p class="font-bold mb-1">Document requis</p>
                                        <p>Uploadez la facture commerciale originale (invoice) couvrant toutes les marchandises déclarées. Formats acceptés : PDF, JPG, PNG — 10 Mo max.</p>
                                    </div>

                                    <InvoiceUpload @uploaded="handleInvoiceUploaded" />

                                    <div v-if="clientErrors.invoice" class="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-red-600 text-sm font-semibold">{{ clientErrors.invoice }}</p>
                                    </div>
                                    <div v-if="form.invoice" class="bg-green-50 border border-green-200 rounded-lg p-3">
                                        <p class="text-green-700 text-sm flex items-center gap-2">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            Facture : {{ form.invoice.name }}
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <!-- ══ STEP 5 — Récapitulatif ══ -->
                            <template v-if="currentStep === 5">
                                <div class="space-y-6">

                                    <!-- Déclarant -->
                                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                        <h3 class="font-bold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">1</span>
                                            Déclarant
                                        </h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                            <div><dt class="text-gray-500 font-semibold">Type</dt><dd class="font-bold text-gray-900 mt-0.5">{{ form.declarant_type === 'individual' ? 'Particulier' : 'Entreprise' }}</dd></div>
                                            <div><dt class="text-gray-500 font-semibold">Nom / Raison sociale</dt><dd class="font-bold text-gray-900 mt-0.5">{{ form.declarant_name }}</dd></div>
                                        </dl>
                                    </div>

                                    <!-- Transport -->
                                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                        <h3 class="font-bold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">2</span>
                                            Transport
                                        </h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                            <div><dt class="text-gray-500 font-semibold">Mode</dt><dd class="font-bold text-gray-900 mt-0.5 capitalize">{{ transportModes.find(m => m.value === form.transport_mode)?.label }}</dd></div>
                                            <div><dt class="text-gray-500 font-semibold">Compagnie</dt><dd class="font-bold text-gray-900 mt-0.5">{{ form.transport_company }}</dd></div>
                                            <div><dt class="text-gray-500 font-semibold">Départ</dt><dd class="font-bold text-gray-900 mt-0.5">{{ form.departure_country }}</dd></div>
                                            <div><dt class="text-gray-500 font-semibold">Arrivée</dt><dd class="font-bold text-gray-900 mt-0.5">{{ form.arrival_country }}</dd></div>
                                            <div v-if="form.tracking_number"><dt class="text-gray-500 font-semibold">N° tracking</dt><dd class="font-mono font-bold text-gray-900 mt-0.5">{{ form.tracking_number }}</dd></div>
                                        </dl>
                                    </div>

                                    <!-- Articles -->
                                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                        <h3 class="font-bold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">3</span>
                                            Marchandises ({{ items.length }} article{{ items.length > 1 ? 's' : '' }})
                                        </h3>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-200">
                                                        <th class="text-left pb-2">Désignation</th>
                                                        <th class="text-left pb-2">Catégorie</th>
                                                        <th class="text-right pb-2">Qté</th>
                                                        <th class="text-right pb-2">P.U.</th>
                                                        <th class="text-right pb-2">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    <tr v-for="(item, i) in items" :key="i" class="py-2">
                                                        <td class="py-2 font-semibold text-gray-800">{{ item.name }}</td>
                                                        <td class="py-2 text-gray-500">{{ item.category }}</td>
                                                        <td class="py-2 text-right font-mono">{{ item.quantity }}</td>
                                                        <td class="py-2 text-right font-mono">{{ formatCurrency(item.unit_value) }}</td>
                                                        <td class="py-2 text-right font-mono font-bold text-amber-700">{{ formatCurrency(item.quantity * item.unit_value) }}</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot class="border-t-2 border-gray-200">
                                                    <tr>
                                                        <td colspan="4" class="pt-3 font-bold text-gray-700 text-right pr-4">Valeur totale déclarée</td>
                                                        <td class="pt-3 text-right font-extrabold text-gray-900 font-mono">{{ formatCurrency(globalTotal) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Documents -->
                                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                        <h3 class="font-bold text-gray-900 mb-3 border-b pb-2 flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">4</span>
                                            Documents joints
                                        </h3>
                                        <div class="flex flex-wrap gap-3 text-sm">
                                            <span v-if="form.cni_recto" class="flex items-center gap-2 text-green-700 bg-green-50 px-3 py-1.5 rounded-lg border border-green-200">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                CNI Recto
                                            </span>
                                            <span v-if="form.cni_verso" class="flex items-center gap-2 text-green-700 bg-green-50 px-3 py-1.5 rounded-lg border border-green-200">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                CNI Verso
                                            </span>
                                            <span v-if="form.invoice" class="flex items-center gap-2 text-green-700 bg-green-50 px-3 py-1.5 rounded-lg border border-green-200">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                Facture ({{ form.invoice.name }})
                                            </span>
                                        </div>
                                    </div>

                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
                                        <p class="font-semibold mb-1">Avant de soumettre</p>
                                        <p>Vérifiez que toutes les informations sont exactes. Toute fausse déclaration en douane est passible de sanctions. Une fois soumise, votre déclaration sera transmise aux services douaniers pour traitement.</p>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </Transition>
                </div>

                <!-- ── Footer navigation ── -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center rounded-b-2xl">
                    <button v-if="currentStep > 1" @click="previousStep"
                            class="inline-flex items-center gap-2 text-gray-600 font-semibold hover:text-gray-900 transition-colors px-4 py-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Retour
                    </button>
                    <div v-else></div>

                    <button v-if="currentStep < 5" @click="nextStep"
                            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2.5 rounded-lg shadow transition-colors">
                        Suivant
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <button v-else @click="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold px-8 py-2.5 rounded-lg shadow transition-colors">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ form.processing ? 'Envoi en cours…' : 'Confirmer et soumettre' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transition entre les steps */
.step-enter-active { transition: opacity 0.28s ease, transform 0.28s ease; }
.step-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.step-enter-from { opacity: 0; transform: translateX(20px); }
.step-leave-to { opacity: 0; transform: translateX(-20px); }

/* Transition ajout/suppression d'articles */
.item-list-enter-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.item-list-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.item-list-enter-from { opacity: 0; transform: translateY(-8px); }
.item-list-leave-to { opacity: 0; transform: translateY(-8px); }
.item-list-move { transition: transform 0.3s ease; }
</style>
