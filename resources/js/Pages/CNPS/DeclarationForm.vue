<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CNPSNumberInput from '@/Components/CNPSNumberInput.vue';
import CNIUpload from '@/Components/CNIUpload.vue';

const props = defineProps({
    service: Object,
    userType: String,
    userData: Object,
});

const page = usePage();
const currentStep = ref(1);
const cnpsVerified = ref(false);
const cniUploaded = ref({ recto: false, verso: false });
const hasCNPSNumber = ref(null);
const clientErrors = ref({});

const form = useForm({
    employer_cnps_number: '',
    employer_name: props.userData?.company_name || '',
    employer_registration: props.userData?.company_registration || '',
    employee_cnps_number: '',
    employee_last_name: props.userData?.last_name || '',
    employee_first_name: props.userData?.first_name || '',
    employee_nif: props.userData?.nif || '',
    hire_date: '',
    contract_type: '',
    monthly_gross_salary: '',
    professional_category: '',
    declaration_type: 'normale',
    has_cnps_number: null,
    cni_recto: null,
    cni_verso: null,
});

const isParticulier = computed(() => props.userType === 'particulier');

const steps = computed(() => {
    const baseSteps = isParticulier.value
        ? [{ id: 1, title: 'Vos infos', icon: 'user' }, { id: 2, title: 'Employeur', icon: 'building' }]
        : [{ id: 1, title: 'Employeur', icon: 'building' }, { id: 2, title: 'Salarié', icon: 'user' }];
    return [
        ...baseSteps,
        { id: 3, title: 'Contrat & Salaire', icon: 'document' },
        { id: 4, title: 'Documents', icon: 'upload' },
        { id: 5, title: 'Confirmation', icon: 'check' },
    ].map(step => ({ ...step, completed: currentStep.value > step.id }));
});

const stepTitle = computed(() => {
    const titles = isParticulier.value
        ? { 1: 'Vos informations personnelles', 2: "Informations de votre employeur" }
        : { 1: "Informations de l'entreprise", 2: 'Informations du salarié' };
    return { ...titles, 3: 'Détails du contrat', 4: 'Documents à fournir', 5: 'Récapitulatif et confirmation' }[currentStep.value];
});

const contractTypes = [
    { value: 'cdi', label: 'CDI - Contrat à Durée Indéterminée' },
    { value: 'cdd', label: 'CDD - Contrat à Durée Déterminée' },
    { value: 'interim', label: 'Intérim' },
    { value: 'stage', label: 'Stage' },
    { value: 'apprentissage', label: "Contrat d'Apprentissage" },
    { value: 'essai', label: "Période d'essai" },
];

const professionalCategories = [
    { value: 'ouvrier', label: 'Ouvrier / Employé' },
    { value: 'technicien', label: 'Technicien' },
    { value: 'agent_maitrise', label: 'Agent de maîtrise' },
    { value: 'cadre', label: 'Cadre' },
    { value: 'dirigeant', label: 'Dirigeant' },
];

const declarationTypes = [
    { value: 'normale', label: 'Déclaration Normale', desc: 'Première déclaration ou périodique' },
    { value: 'rectificative', label: 'Déclaration Rectificative', desc: "Correction d'une déclaration précédente" },
    { value: 'complementaire', label: 'Déclaration Complémentaire', desc: "Ajout d'informations manquantes" },
];

const handleCNPSVerified = (data) => { cnpsVerified.value = data.valid; };

// FIX CNI — attend { recto: File|null, verso: File|null } depuis CNIUpload
const handleCNIUploaded = (files) => {
    if (files.recto instanceof File) {
        form.cni_recto = files.recto;
        cniUploaded.value.recto = true;
    }
    if (files.verso instanceof File) {
        form.cni_verso = files.verso;
        cniUploaded.value.verso = true;
    }
};

watch(hasCNPSNumber, (newVal) => {
    form.has_cnps_number = newVal;
    if (newVal === false) { form.employee_cnps_number = ''; cnpsVerified.value = true; }
    else { cnpsVerified.value = false; }
});

const validateStep = (step) => {
    const errors = {};
    const requireField = (field, message) => {
        if (!form[field] || String(form[field]).trim() === '') errors[field] = message;
    };

    if (step === 1) {
        if (isParticulier.value) {
            requireField('employee_last_name', 'Nom requis');
            requireField('employee_first_name', 'Prénom requis');
            requireField('hire_date', "Date d'embauche requise");
            if (hasCNPSNumber.value === null) errors.hasCNPSNumber = 'Veuillez répondre à cette question';
            else if (hasCNPSNumber.value === true && !cnpsVerified.value) errors.employee_cnps_number = 'Veuillez vérifier votre numéro CNPS';
        } else {
            requireField('employer_name', "Nom de l'entreprise requis");
            requireField('employer_cnps_number', 'Matricule CNPS requis');
        }
    }
    if (step === 2) {
        if (isParticulier.value) {
            requireField('employer_name', "Nom de l'employeur requis");
            requireField('employer_cnps_number', 'Matricule CNPS requis');
        } else {
            requireField('employee_last_name', 'Nom du salarié requis');
            requireField('employee_first_name', 'Prénom du salarié requis');
            requireField('hire_date', "Date d'embauche requise");
            if (hasCNPSNumber.value === null) errors.hasCNPSNumber = 'Veuillez répondre à cette question';
            else if (hasCNPSNumber.value === true && !cnpsVerified.value) errors.employee_cnps_number = 'Veuillez vérifier le numéro CNPS du salarié';
        }
    }
    if (step === 3) {
        requireField('contract_type', 'Type de contrat requis');
        requireField('professional_category', 'Catégorie professionnelle requise');
        if (!form.monthly_gross_salary || Number(form.monthly_gross_salary) <= 0) errors.monthly_gross_salary = 'Salaire brut valide requis (> 0)';
    }
    if (step === 4) {
        // Validation instanceof File — jamais de booléen ou string
        if (!(form.cni_recto instanceof File)) errors.cni_recto = 'Le recto de la CNI est requis';
        if (!(form.cni_verso instanceof File)) errors.cni_verso = 'Le verso de la CNI est requis';
    }

    clientErrors.value = errors;
    return Object.keys(errors).length === 0;
};

// SCROLL FIX — ref sur le card, scroll smooth vers le haut du formulaire
const formCardRef = ref(null);

const scrollToFormTop = () => {
    nextTick(() => {
        if (formCardRef.value) {
            const top = formCardRef.value.getBoundingClientRect().top + window.scrollY - 24;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    });
};

const nextStep = () => {
    if (validateStep(currentStep.value) && currentStep.value < steps.value.length) {
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

const submit = () => {
    form.post(route('cnps.submit'), {
        forceFormData: true,
        onError: () => scrollToFormTop(),
    });
};

const formatCurrency = (value) => {
    if (!value) return '';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(value);
};
</script>

<template>
    <Head :title="`CNPS - ${service?.name || 'Déclaration'}`" />
    <AuthenticatedLayout :user="page.props.auth.user">
        <div class="max-w-5xl mx-auto py-8 px-4">

            <div class="mb-10 text-center">
                <div class="inline-flex items-center px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-bold mb-4 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" /></svg>
                    {{ isParticulier ? 'Déclaration Salarié' : 'Déclaration Employeur' }}
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ service?.name || 'Nouvelle Déclaration' }}</h1>
                <p class="text-gray-500 max-w-2xl mx-auto">{{ service?.description }}</p>
            </div>

            <div class="mb-10 relative">
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2 rounded-full"></div>
                <div class="absolute top-1/2 left-0 h-1 bg-primary-500 -translate-y-1/2 rounded-full transition-all duration-500 ease-in-out"
                     :style="{ width: `${((currentStep - 1) / (steps.length - 1)) * 100}%` }"></div>
                <div class="relative flex justify-between">
                    <div v-for="step in steps" :key="step.id" class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-4 transition-all duration-300 mb-2 text-sm font-bold shadow-sm"
                             :class="{ 'bg-primary-500 border-primary-500 text-white': step.completed || currentStep >= step.id, 'bg-white border-gray-200 text-gray-400': !step.completed && currentStep < step.id }">
                            <span v-if="step.completed">✓</span><span v-else>{{ step.id }}</span>
                        </div>
                        <span class="text-xs font-bold text-center transition-colors duration-300"
                              :class="currentStep >= step.id ? 'text-primary-700' : 'text-gray-400'">{{ step.title }}</span>
                    </div>
                </div>
            </div>

            <!-- ref formCardRef pour le scroll ciblé -->
            <div ref="formCardRef" class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 border-b pb-4">{{ stepTitle }}</h2>

                    <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl">
                        <p class="font-bold mb-1">Erreurs de validation :</p>
                        <ul class="text-sm list-disc pl-4 space-y-1">
                            <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                        </ul>
                    </div>

                    <!-- Transition directionnelle step-enter (droite) / step-leave (gauche) -->
                    <Transition name="step" mode="out-in">
                        <div :key="currentStep">

                            <template v-if="currentStep === 1 || currentStep === 2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <template v-if="(isParticulier && currentStep === 1) || (!isParticulier && currentStep === 2)">
                                        <div class="md:col-span-2 bg-gray-50 p-6 rounded-xl border border-gray-100">
                                            <label class="block text-sm font-bold text-gray-800 mb-4">
                                                {{ isParticulier ? 'Avez-vous un numéro CNPS ?' : 'Le salarié a-t-il un numéro CNPS ?' }}
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <div class="flex flex-wrap gap-4">
                                                <label class="flex items-center p-3 border rounded-lg cursor-pointer bg-white hover:border-primary-500 transition-colors"
                                                       :class="{ 'border-primary-500 ring-1 ring-primary-500': hasCNPSNumber === true }">
                                                    <input type="radio" v-model="hasCNPSNumber" :value="true" class="h-4 w-4 text-primary-600" />
                                                    <span class="ml-2 font-medium text-gray-700">Oui, {{ isParticulier ? "j'en ai un" : "il en a un" }}</span>
                                                </label>
                                                <label class="flex items-center p-3 border rounded-lg cursor-pointer bg-white hover:border-primary-500 transition-colors"
                                                       :class="{ 'border-primary-500 ring-1 ring-primary-500': hasCNPSNumber === false }">
                                                    <input type="radio" v-model="hasCNPSNumber" :value="false" class="h-4 w-4 text-primary-600" />
                                                    <span class="ml-2 font-medium text-gray-700">Non, première affiliation</span>
                                                </label>
                                            </div>
                                            <p v-if="clientErrors.hasCNPSNumber" class="text-red-500 text-xs mt-2 font-semibold">{{ clientErrors.hasCNPSNumber }}</p>
                                            <div v-if="hasCNPSNumber === true" class="mt-6">
                                                <CNPSNumberInput v-model="form.employee_cnps_number"
                                                    :label="isParticulier ? 'Votre numéro CNPS' : 'Numéro CNPS du salarié'"
                                                    placeholder="12 chiffres" :required="true" @verified="handleCNPSVerified" />
                                                <p v-if="clientErrors.employee_cnps_number" class="text-red-500 text-xs mt-1 font-semibold">{{ clientErrors.employee_cnps_number }}</p>
                                                <div v-if="cnpsVerified" class="mt-3 bg-green-50 text-green-700 p-3 rounded-lg flex items-center text-sm font-bold border border-green-200">
                                                    <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                    Identité CNPS confirmée
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                                            <input v-model="form.employee_last_name" type="text" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 transition-colors" :class="clientErrors.employee_last_name ? 'border-red-300 bg-red-50' : 'border-gray-300'" />
                                            <p v-if="clientErrors.employee_last_name" class="text-red-500 text-xs mt-1">{{ clientErrors.employee_last_name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                                            <input v-model="form.employee_first_name" type="text" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 transition-colors" :class="clientErrors.employee_first_name ? 'border-red-300 bg-red-50' : 'border-gray-300'" />
                                            <p v-if="clientErrors.employee_first_name" class="text-red-500 text-xs mt-1">{{ clientErrors.employee_first_name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">NIF <span class="text-gray-400 text-xs font-normal">(optionnel)</span></label>
                                            <input v-model="form.employee_nif" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="Numéro d'Identification Fiscale" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Date d'embauche <span class="text-red-500">*</span></label>
                                            <input v-model="form.hire_date" type="date" :max="new Date().toISOString().split('T')[0]" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 transition-colors" :class="clientErrors.hire_date ? 'border-red-300 bg-red-50' : 'border-gray-300'" />
                                            <p v-if="clientErrors.hire_date" class="text-red-500 text-xs mt-1">{{ clientErrors.hire_date }}</p>
                                        </div>
                                    </template>

                                    <template v-if="(isParticulier && currentStep === 2) || (!isParticulier && currentStep === 1)">
                                        <div class="md:col-span-2">
                                            <CNPSNumberInput v-model="form.employer_cnps_number" label="Matricule CNPS de l'employeur" placeholder="Ex: 123456789012" :required="true" />
                                            <p v-if="clientErrors.employer_cnps_number" class="text-red-500 text-xs mt-1">{{ clientErrors.employer_cnps_number }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Nom de l'entreprise <span class="text-red-500">*</span></label>
                                            <input v-model="form.employer_name" type="text" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 transition-colors" :class="clientErrors.employer_name ? 'border-red-300 bg-red-50' : 'border-gray-300'" />
                                            <p v-if="clientErrors.employer_name" class="text-red-500 text-xs mt-1">{{ clientErrors.employer_name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">RCCM / IFU <span class="text-gray-400 text-xs font-normal">(optionnel)</span></label>
                                            <input v-model="form.employer_registration" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template v-if="currentStep === 3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Type de contrat <span class="text-red-500">*</span></label>
                                        <select v-model="form.contract_type" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 transition-colors" :class="clientErrors.contract_type ? 'border-red-300 bg-red-50' : 'border-gray-300'">
                                            <option value="" disabled>Sélectionner...</option>
                                            <option v-for="opt in contractTypes" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                        </select>
                                        <p v-if="clientErrors.contract_type" class="text-red-500 text-xs mt-1">{{ clientErrors.contract_type }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Catégorie professionnelle <span class="text-red-500">*</span></label>
                                        <select v-model="form.professional_category" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 transition-colors" :class="clientErrors.professional_category ? 'border-red-300 bg-red-50' : 'border-gray-300'">
                                            <option value="" disabled>Sélectionner...</option>
                                            <option v-for="opt in professionalCategories" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                        </select>
                                        <p v-if="clientErrors.professional_category" class="text-red-500 text-xs mt-1">{{ clientErrors.professional_category }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Salaire brut mensuel (FCFA) <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm">FCFA</span>
                                            <input v-model="form.monthly_gross_salary" type="number" min="1" step="100" class="w-full pl-20 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 font-mono transition-colors" :class="clientErrors.monthly_gross_salary ? 'border-red-300 bg-red-50' : 'border-gray-300'" placeholder="Ex: 250000" />
                                        </div>
                                        <p v-if="clientErrors.monthly_gross_salary" class="text-red-500 text-xs mt-1">{{ clientErrors.monthly_gross_salary }}</p>
                                        <p v-else-if="form.monthly_gross_salary && Number(form.monthly_gross_salary) > 0" class="text-green-600 text-xs mt-1 font-bold">{{ formatCurrency(form.monthly_gross_salary) }}</p>
                                    </div>
                                    <div class="md:col-span-2 mt-4 pt-6 border-t">
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Type de déclaration <span class="text-red-500">*</span></label>
                                        <div class="space-y-3">
                                            <label v-for="decl in declarationTypes" :key="decl.value"
                                                   class="flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-primary-300"
                                                   :class="{ 'border-primary-500 bg-primary-50': form.declaration_type === decl.value, 'border-gray-200': form.declaration_type !== decl.value }">
                                                <input v-model="form.declaration_type" type="radio" :value="decl.value" class="mt-1 h-4 w-4 text-primary-600 flex-shrink-0" />
                                                <div class="ml-3">
                                                    <span class="font-bold text-gray-900">{{ decl.label }}</span>
                                                    <p class="text-sm text-gray-500 mt-0.5">{{ decl.desc }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-if="currentStep === 4">
                                <div class="space-y-6">
                                    <!-- CNIUpload doit émettre @uploaded="{ recto: File, verso: File }" -->
                                    <CNIUpload @uploaded="handleCNIUploaded" />
                                    <div v-if="clientErrors.cni_recto || clientErrors.cni_verso" class="bg-red-50 border border-red-200 rounded-lg p-4 space-y-1">
                                        <p v-if="clientErrors.cni_recto" class="text-red-600 text-sm font-semibold">{{ clientErrors.cni_recto }}</p>
                                        <p v-if="clientErrors.cni_verso" class="text-red-600 text-sm font-semibold">{{ clientErrors.cni_verso }}</p>
                                    </div>
                                    <div v-if="form.cni_recto || form.cni_verso" class="bg-green-50 border border-green-200 rounded-lg p-4 space-y-1">
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
                            </template>

                            <template v-if="currentStep === 5">
                                <div class="bg-gray-50 rounded-xl p-6 space-y-6 border border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div>
                                            <h3 class="font-bold text-gray-900 mb-3 border-b pb-2">Employeur</h3>
                                            <ul class="text-sm space-y-2 text-gray-600">
                                                <li><span class="font-semibold text-gray-800">Nom :</span> {{ form.employer_name }}</li>
                                                <li><span class="font-semibold text-gray-800">Matricule CNPS :</span> <span class="font-mono">{{ form.employer_cnps_number }}</span></li>
                                                <li v-if="form.employer_registration"><span class="font-semibold text-gray-800">RCCM/IFU :</span> {{ form.employer_registration }}</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 mb-3 border-b pb-2">Salarié</h3>
                                            <ul class="text-sm space-y-2 text-gray-600">
                                                <li><span class="font-semibold text-gray-800">Nom complet :</span> {{ form.employee_first_name }} {{ form.employee_last_name }}</li>
                                                <li><span class="font-semibold text-gray-800">CNPS :</span> <span class="font-mono">{{ hasCNPSNumber ? form.employee_cnps_number : 'Première affiliation' }}</span></li>
                                                <li><span class="font-semibold text-gray-800">Date d'embauche :</span> {{ new Date(form.hire_date).toLocaleDateString('fr-FR') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="pt-4 border-t border-gray-200">
                                        <h3 class="font-bold text-gray-900 mb-3">Détails de la déclaration</h3>
                                        <ul class="text-sm space-y-2 text-gray-600 grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <li><span class="font-semibold text-gray-800">Contrat :</span> <span class="uppercase">{{ form.contract_type }}</span></li>
                                            <li><span class="font-semibold text-gray-800">Catégorie :</span> {{ professionalCategories.find(c => c.value === form.professional_category)?.label }}</li>
                                            <li><span class="font-semibold text-gray-800">Salaire brut :</span> <span class="font-mono font-bold">{{ formatCurrency(form.monthly_gross_salary) }}</span></li>
                                            <li><span class="font-semibold text-gray-800">Type :</span> {{ declarationTypes.find(d => d.value === form.declaration_type)?.label }}</li>
                                        </ul>
                                    </div>
                                    <div class="pt-4 border-t border-gray-200">
                                        <h3 class="font-bold text-gray-900 mb-3">Documents joints</h3>
                                        <div class="flex flex-wrap gap-4 text-sm">
                                            <span v-if="form.cni_recto" class="flex items-center gap-2 text-green-700">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                CNI recto ({{ form.cni_recto.name }})
                                            </span>
                                            <span v-if="form.cni_verso" class="flex items-center gap-2 text-green-700">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                CNI verso ({{ form.cni_verso.name }})
                                            </span>
                                        </div>
                                    </div>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
                                        <p class="font-semibold mb-1">Avant de soumettre</p>
                                        <p>Vérifiez que toutes les informations ci-dessus sont correctes. Une fois soumise, la déclaration sera transmise aux services CNPS.</p>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </Transition>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center rounded-b-2xl">
                    <button v-if="currentStep > 1" @click="previousStep"
                            class="inline-flex items-center gap-2 text-gray-600 font-semibold hover:text-gray-900 transition-colors px-4 py-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Retour
                    </button>
                    <div v-else></div>

                    <button v-if="currentStep < 5" @click="nextStep"
                            class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white font-bold px-6 py-2.5 rounded-lg shadow transition-colors">
                        Suivant
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <button v-else @click="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold px-8 py-2.5 rounded-lg shadow transition-colors">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ form.processing ? 'Envoi en cours...' : 'Confirmer et soumettre' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.step-enter-active { transition: opacity 0.28s ease, transform 0.28s ease; }
.step-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.step-enter-from { opacity: 0; transform: translateX(20px); }
.step-leave-to { opacity: 0; transform: translateX(-20px); }
</style>
