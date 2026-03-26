<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CNIUpload from '@/Components/CNIUpload.vue';
import InvoiceUpload from '@/Components/InvoiceUpload.vue';

const props = defineProps({
    auth: Object,
    service: Object,
    userData: Object, // Informations pré-remplies de l'utilisateur
});

const currentStep = ref(1);
const totalSteps = 3;

// Initialisation du formulaire Inertia
const form = useForm({
    // Step 1: Déclarant
    declarant_type: 'individual', // individual ou company
    declarant_name: props.userData?.name || '',
    declarant_id_number: '',
    cni: { recto: null, verso: null },

    // Step 2: Transport & Items
    transport_mode: 'air',
    departure_country: '',
    arrival_country: 'Côte d\'Ivoire',
    transport_company: '',
    tracking_number: '',
    items: [
        { name: '', category: '', quantity: 1, unit_value: 0, weight: '', origin_country: '' }
    ],

    // Step 3: Documents
    invoice: null,
});

// Navigation
const nextStep = () => { if (currentStep.value < totalSteps) currentStep.value++; };
const prevStep = () => { if (currentStep.value > 1) currentStep.value--; };

// Gestion des Items
const addItem = () => {
    form.items.push({ name: '', category: '', quantity: 1, unit_value: 0, weight: '', origin_country: '' });
};
const removeItem = (index) => {
    if (form.items.length > 1) form.items.splice(index, 1);
};

// Calcul du total global
const globalTotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (item.quantity * item.unit_value), 0);
});

// Soumission finale
const submit = () => {
    form.post(route('customs.store'), {
        forceFormData: true,
        onSuccess: () => {
            // Logique après succès (ex: redirection vers mes demandes)
        },
    });
};

const breadcrumb = [
    { name: 'Tableau de bord', href: route('dashboard') },
    { name: 'Nouvelle Déclaration Douane', href: null }
];
</script>

<template>
    <Head title="Déclaration de Douane" />

    <AuthenticatedLayout :user="auth.user" :breadcrumb="breadcrumb">
        <div class="max-w-5xl mx-auto">

            <div class="mb-10">
                <div class="flex items-center justify-between max-w-2xl mx-auto relative">
                    <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-200 -translate-y-1/2 z-0"></div>
                    <div v-for="step in totalSteps" :key="step"
                        class="relative z-10 flex items-center justify-center w-10 h-10 rounded-full font-black text-sm transition-all duration-500"
                        :class="currentStep >= step ? 'bg-secondary-500 text-white shadow-lg shadow-secondary-200' : 'bg-white border-2 border-gray-200 text-gray-400'">
                        {{ step }}
                    </div>
                    <div class="absolute top-1/2 left-0 h-0.5 bg-secondary-500 -translate-y-1/2 transition-all duration-500 z-0"
                        :style="{ width: ((currentStep - 1) / (totalSteps - 1) * 100) + '%' }"></div>
                </div>
                <div class="flex justify-between max-w-2xl mx-auto mt-3">
                    <span class="text-[10px] font-black uppercase tracking-widest" :class="currentStep >= 1 ? 'text-secondary-600' : 'text-gray-400'">Identité</span>
                    <span class="text-[10px] font-black uppercase tracking-widest" :class="currentStep >= 2 ? 'text-secondary-600' : 'text-gray-400'">Déclaration</span>
                    <span class="text-[10px] font-black uppercase tracking-widest" :class="currentStep >= 3 ? 'text-secondary-600' : 'text-gray-400'">Validation</span>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

                <div v-if="currentStep === 1" class="p-8 space-y-8 animate-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest">Type de déclarant</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" @click="form.declarant_type = 'individual'"
                                    :class="form.declarant_type === 'individual' ? 'border-secondary-500 bg-secondary-50 text-secondary-600' : 'border-gray-200 text-gray-400'"
                                    class="p-4 border-2 rounded-2xl font-bold transition-all text-sm">Particulier</button>
                                <button type="button" @click="form.declarant_type = 'company'"
                                    :class="form.declarant_type === 'company' ? 'border-secondary-500 bg-secondary-50 text-secondary-600' : 'border-gray-200 text-gray-400'"
                                    class="p-4 border-2 rounded-2xl font-bold transition-all text-sm">Entreprise</button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest">Nom / Raison Sociale</label>
                            <input v-model="form.declarant_name" type="text" class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-secondary-500 transition-all font-bold" />
                        </div>
                    </div>

                    <CNIUpload v-model="form.cni" />
                </div>

                <div v-if="currentStep === 2" class="p-8 space-y-10 animate-in">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mode de transport</label>
                            <select v-model="form.transport_mode" class="w-full bg-gray-50 border-none rounded-xl p-3 font-bold">
                                <option value="air">Aérien</option>
                                <option value="sea">Maritime</option>
                                <option value="road">Routier</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pays de départ</label>
                            <input v-model="form.departure_country" type="text" placeholder="Ex: France" class="w-full bg-gray-50 border-none rounded-xl p-3 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Compagnie</label>
                            <input v-model="form.transport_company" type="text" placeholder="Ex: Air France" class="w-full bg-gray-50 border-none rounded-xl p-3 font-bold" />
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tighter text-secondary-600">Liste des articles</h3>
                            <button @click="addItem" type="button" class="bg-primary-500 text-white px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-primary-600 transition-all shadow-lg shadow-primary-500/20">
                                + Ajouter un article
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(item, index) in form.items" :key="index" class="p-6 bg-gray-50 rounded-2xl relative group">
                                <button v-if="form.items.length > 1" @click="removeItem(index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-2">
                                        <input v-model="item.name" type="text" placeholder="Désignation de la marchandise" class="w-full bg-white border-none rounded-xl p-3 text-sm font-bold" />
                                    </div>
                                    <input v-model="item.category" type="text" placeholder="Catégorie" class="w-full bg-white border-none rounded-xl p-3 text-sm font-bold" />
                                    <input v-model="item.origin_country" type="text" placeholder="Origine" class="w-full bg-white border-none rounded-xl p-3 text-sm font-bold" />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase">Qté</label>
                                        <input v-model.number="item.quantity" type="number" class="w-full bg-white border-none rounded-xl p-3 text-sm font-bold" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase">Prix Unitaire</label>
                                        <input v-model.number="item.unit_value" type="number" class="w-full bg-white border-none rounded-xl p-3 text-sm font-bold" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase">Poids</label>
                                        <input v-model="item.weight" type="text" placeholder="Ex: 5kg" class="w-full bg-white border-none rounded-xl p-3 text-sm font-bold" />
                                    </div>
                                    <div class="flex items-center justify-end bg-secondary-100 rounded-xl px-4 py-2 border border-secondary-200">
                                        <span class="text-xs font-black text-secondary-600 uppercase">{{ (item.quantity * item.unit_value).toLocaleString() }} CFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <div class="bg-gray-900 text-white p-6 rounded-2xl shadow-xl">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Valeur Totale Déclarée</p>
                                <p class="text-3xl font-black mt-1">{{ globalTotal.toLocaleString() }} <span class="text-sm font-bold text-primary-500 uppercase">CFA</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="currentStep === 3" class="p-8 space-y-8 animate-in text-center">
                    <div class="max-w-md mx-auto">
                        <InvoiceUpload v-model="form.invoice" :error="form.errors.invoice" />
                    </div>

                    <div class="bg-primary-50 border border-primary-100 rounded-2xl p-6 text-left max-w-2xl mx-auto">
                        <h4 class="font-black text-primary-600 uppercase tracking-widest text-xs mb-4">Récapitulatif Final</h4>
                        <div class="grid grid-cols-2 gap-y-3 text-sm">
                            <span class="text-gray-500 font-bold">Déclarant :</span>
                            <span class="font-black text-gray-900">{{ form.declarant_name }}</span>
                            <span class="text-gray-500 font-bold">Provenance :</span>
                            <span class="font-black text-gray-900">{{ form.departure_country }} ({{ form.transport_mode }})</span>
                            <span class="text-gray-500 font-bold">Articles :</span>
                            <span class="font-black text-gray-900">{{ form.items.length }} article(s) enregistré(s)</span>
                            <span class="text-gray-500 font-bold">Total à déclarer :</span>
                            <span class="font-black text-secondary-600">{{ globalTotal.toLocaleString() }} CFA</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-3 text-gray-400 text-xs font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                        Vos données sont cryptées et transmises en toute sécurité aux services douaniers.
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex items-center justify-between">
                    <button v-if="currentStep > 1" @click="prevStep" type="button" class="px-8 py-3 bg-white border border-gray-200 text-gray-500 font-black rounded-2xl hover:bg-gray-100 transition-all uppercase tracking-widest text-xs">
                        Précédent
                    </button>
                    <div v-else></div>

                    <button v-if="currentStep < totalSteps" @click="nextStep" type="button" class="px-8 py-3 bg-secondary-600 text-white font-black rounded-2xl hover:bg-secondary-700 transition-all shadow-lg shadow-secondary-500/20 uppercase tracking-widest text-xs">
                        Étape Suivante
                    </button>
                    <button v-else @click="submit" :disabled="form.processing" type="button" class="px-8 py-3 bg-primary-500 text-white font-black rounded-2xl hover:bg-primary-600 transition-all shadow-lg shadow-primary-500/20 uppercase tracking-widest text-xs flex items-center gap-2">
                        <span v-if="form.processing" class="animate-spin text-lg">↻</span>
                        Confirmer la déclaration
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.bg-primary-500 { background-color: #f39200; }
.bg-secondary-500 { background-color: #006633; }
.bg-secondary-600 { background-color: #00572c; }
.text-secondary-600 { color: #00572c; }
.text-primary-600 { color: #f39200; }
.animate-in { animation: slideIn 0.4s ease forwards; }
@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>
