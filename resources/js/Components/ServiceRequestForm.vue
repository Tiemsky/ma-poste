<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <button @click="$emit('back')" class="inline-flex items-center text-gray-600 hover:text-primary-500 mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour aux services
            </button>
            <h1 class="text-2xl font-bold text-gray-900">{{ service.name }}</h1>
            <p class="text-gray-500 mt-1">{{ service.description }}</p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div v-for="(step, index) in steps" :key="index" class="flex-1 relative">
                    <div class="flex items-center justify-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-300',
                                currentStep >= index + 1
                                    ? 'bg-primary-500 text-white'
                                    : 'bg-gray-200 text-gray-500'
                            ]"
                        >
                            {{ index + 1 }}
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <p class="text-sm font-medium" :class="currentStep >= index + 1 ? 'text-primary-500' : 'text-gray-500'">
                            {{ step.title }}
                        </p>
                        <p class="text-xs text-gray-400">{{ step.description }}</p>
                    </div>
                    <div
                        v-if="index < steps.length - 1"
                        class="absolute top-5 left-1/2 w-full h-0.5 -mt-0.5"
                        :class="currentStep > index + 1 ? 'bg-primary-500' : 'bg-gray-200'"
                        style="transform: translateX(-50%);"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="bg-white rounded-xl shadow-card border border-gray-200 p-6">
            <!-- Step 1: User Information -->
            <div v-if="currentStep === 1">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations personnelles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input
                            v-model="formData.last_name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                            placeholder="Votre nom"
                        />
                        <p v-if="errors.last_name" class="mt-1 text-xs text-red-500">{{ errors.last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénom(s) *</label>
                        <input
                            v-model="formData.first_name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                            placeholder="Votre prénom"
                        />
                        <p v-if="errors.first_name" class="mt-1 text-xs text-red-500">{{ errors.first_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                        <input
                            v-model="formData.phone"
                            type="tel"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                            placeholder="07 00 00 00 00"
                        />
                        <p v-if="errors.phone" class="mt-1 text-xs text-red-500">{{ errors.phone }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            v-model="formData.email"
                            type="email"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                            placeholder="votre@email.com"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                        <select v-model="formData.district" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                            <option value="">Sélectionnez un district</option>
                            <option value="abidjan">District d'Abidjan</option>
                            <option value="yamoussoukro">Yamoussoukro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commune</label>
                        <select v-model="formData.commune" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                            <option value="">Sélectionnez une commune</option>
                            <option value="marcory">Marcory</option>
                            <option value="plateau">Plateau</option>
                            <option value="cocody">Cocody</option>
                            <option value="yopougon">Yopougon</option>
                            <option value="abobo">Abobo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 2: Service Details -->
            <div v-if="currentStep === 2">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de la demande</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de demande *</label>
                        <select v-model="formData.request_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                            <option value="">Sélectionnez un type</option>
                            <option value="new">Nouvelle demande</option>
                            <option value="renewal">Renouvellement</option>
                            <option value="modification">Modification</option>
                        </select>
                        <p v-if="errors.request_type" class="mt-1 text-xs text-red-500">{{ errors.request_type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea
                            v-model="formData.description"
                            rows="4"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
                            placeholder="Décrivez votre demande..."
                        ></textarea>
                        <p v-if="errors.description" class="mt-1 text-xs text-red-500">{{ errors.description }}</p>
                    </div>

                    <!-- Service specific fields -->
                    <div v-if="service.id === 'courrier'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de courrier</label>
                            <select v-model="formData.courrier_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                                <option value="standard">Standard</option>
                                <option value="express">Express</option>
                                <option value="recommande">Recommandé</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Poids estimé (kg)</label>
                            <input v-model="formData.weight" type="number" step="0.1" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200" />
                        </div>
                    </div>

                    <div v-if="service.id === 'boite-postale'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Taille de boîte</label>
                            <select v-model="formData.box_size" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                                <option value="small">Petite (A4)</option>
                                <option value="medium">Moyenne (A3)</option>
                                <option value="large">Grande (A2)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Période d'abonnement</label>
                            <select v-model="formData.subscription_period" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                                <option value="3">3 mois</option>
                                <option value="6">6 mois</option>
                                <option value="12">12 mois</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Documents Upload -->
            <div v-if="currentStep === 3">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Documents à fournir</h2>
                <div class="space-y-4">
                    <div v-for="doc in requiredDocuments" :key="doc.id" class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ doc.name }}</p>
                                <p class="text-sm text-gray-500">{{ doc.description }}</p>
                                <p class="text-xs text-gray-400 mt-1">Formats acceptés: {{ doc.accepted_formats }} | Max: {{ doc.max_size }}MB</p>
                            </div>
                            <div class="flex-shrink-0">
                                <button
                                    @click="triggerFileUpload(doc.id)"
                                    class="px-4 py-2 border-2 border-primary-500 text-primary-500 rounded-lg hover:bg-primary-50 transition-colors text-sm"
                                >
                                    {{ formData.documents[doc.id] ? 'Changer' : 'Télécharger' }}
                                </button>
                                <input
                                    :ref="`fileInput_${doc.id}`"
                                    type="file"
                                    :accept="doc.accept"
                                    class="hidden"
                                    @change="handleFileUpload($event, doc.id)"
                                />
                            </div>
                        </div>
                        <div v-if="formData.documents[doc.id]" class="mt-3 flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600">{{ formData.documents[doc.id].name }}</span>
                            <button @click="removeDocument(doc.id)" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Confirmation -->
            <div v-if="currentStep === 4">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Confirmation de la demande</h2>
                <div class="space-y-4">
                    <!-- Résumé des informations -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Informations personnelles</h3>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <p class="text-gray-500">Nom complet:</p>
                            <p class="text-gray-900">{{ formData.last_name }} {{ formData.first_name }}</p>
                            <p class="text-gray-500">Téléphone:</p>
                            <p class="text-gray-900">{{ formData.phone }}</p>
                            <p class="text-gray-500">Email:</p>
                            <p class="text-gray-900">{{ formData.email || 'Non renseigné' }}</p>
                            <p class="text-gray-500">Adresse:</p>
                            <p class="text-gray-900">{{ formData.district }} - {{ formData.commune }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Détails de la demande</h3>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <p class="text-gray-500">Type de demande:</p>
                            <p class="text-gray-900">{{ getRequestTypeLabel(formData.request_type) }}</p>
                            <p class="text-gray-500">Description:</p>
                            <p class="text-gray-900">{{ formData.description }}</p>
                            <template v-if="service.id === 'courrier'">
                                <p class="text-gray-500">Type de courrier:</p>
                                <p class="text-gray-900">{{ formData.courrier_type }}</p>
                                <p class="text-gray-500">Poids:</p>
                                <p class="text-gray-900">{{ formData.weight }} kg</p>
                            </template>
                            <template v-if="service.id === 'boite-postale'">
                                <p class="text-gray-500">Taille de boîte:</p>
                                <p class="text-gray-900">{{ formData.box_size }}</p>
                                <p class="text-gray-500">Abonnement:</p>
                                <p class="text-gray-900">{{ formData.subscription_period }} mois</p>
                            </template>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Documents fournis</h3>
                        <div class="space-y-1">
                            <div v-for="doc in requiredDocuments" :key="doc.id" class="flex items-center gap-2 text-sm">
                                <svg v-if="formData.documents[doc.id]" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span :class="formData.documents[doc.id] ? 'text-gray-900' : 'text-gray-500'">
                                    {{ doc.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <label class="flex items-start gap-3 mt-4">
                        <input v-model="formData.confirmed" type="checkbox" class="mt-1 rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        <span class="text-sm text-gray-600">
                            Je certifie que les informations fournies sont exactes et complètes. Je m'engage à fournir les documents originaux si nécessaire.
                        </span>
                    </label>
                    <p v-if="errors.confirmed" class="text-xs text-red-500">{{ errors.confirmed }}</p>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8 pt-4 border-t border-gray-200">
                <button
                    v-if="currentStep > 1"
                    @click="currentStep--"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Précédent
                </button>
                <div class="flex-1"></div>
                <button
                    v-if="currentStep < 4"
                    @click="nextStep"
                    class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors"
                >
                    Suivant
                </button>
                <button
                    v-if="currentStep === 4"
                    @click="submitRequest"
                    :disabled="submitting"
                    class="px-6 py-2 bg-secondary-500 text-white rounded-lg hover:bg-secondary-600 transition-colors disabled:opacity-50"
                >
                    <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Soumettre la demande
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    service: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['back', 'submitted']);

const currentStep = ref(1);
const submitting = ref(false);

const steps = [
    { title: 'Informations', description: 'Vos coordonnées' },
    { title: 'Détails', description: 'Description de la demande' },
    { title: 'Documents', description: 'Pièces jointes' },
    { title: 'Confirmation', description: 'Vérification finale' }
];

const requiredDocuments = computed(() => {
    const documents = {
        'courrier': [
            { id: 'id_card', name: 'Pièce d\'identité', description: 'CNI ou passeport valide', accepted_formats: 'PDF, JPG, PNG', max_size: 5, accept: '.pdf,.jpg,.jpeg,.png' },
            { id: 'proof_address', name: 'Justificatif de domicile', description: 'Facture d\'électricité ou eau de moins de 3 mois', accepted_formats: 'PDF, JPG, PNG', max_size: 5, accept: '.pdf,.jpg,.jpeg,.png' }
        ],
        'boite-postale': [
            { id: 'id_card', name: 'Pièce d\'identité', description: 'CNI ou passeport valide', accepted_formats: 'PDF, JPG, PNG', max_size: 5, accept: '.pdf,.jpg,.jpeg,.png' },
            { id: 'proof_address', name: 'Justificatif de domicile', description: 'Facture d\'électricité ou eau de moins de 3 mois', accepted_formats: 'PDF, JPG, PNG', max_size: 5, accept: '.pdf,.jpg,.jpeg,.png' },
            { id: 'business_license', name: 'Registre de commerce', description: 'Pour les entreprises', accepted_formats: 'PDF', max_size: 5, accept: '.pdf' }
        ],
        'default': [
            { id: 'id_card', name: 'Pièce d\'identité', description: 'CNI ou passeport valide', accepted_formats: 'PDF, JPG, PNG', max_size: 5, accept: '.pdf,.jpg,.jpeg,.png' }
        ]
    };
    return documents[props.service.id] || documents.default;
});

const formData = reactive({
    last_name: props.user?.last_name || '',
    first_name: props.user?.first_name || '',
    phone: props.user?.phone || '',
    email: props.user?.email || '',
    district: props.user?.district || '',
    commune: props.user?.commune || '',
    request_type: '',
    description: '',
    courrier_type: 'standard',
    weight: '',
    box_size: 'small',
    subscription_period: '12',
    documents: {},
    confirmed: false
});

const errors = reactive({});

const validateStep = () => {
    const newErrors = {};

    if (currentStep.value === 1) {
        if (!formData.last_name) newErrors.last_name = 'Le nom est requis';
        if (!formData.first_name) newErrors.first_name = 'Le prénom est requis';
        if (!formData.phone) newErrors.phone = 'Le téléphone est requis';
    } else if (currentStep.value === 2) {
        if (!formData.request_type) newErrors.request_type = 'Le type de demande est requis';
        if (!formData.description) newErrors.description = 'La description est requise';
    } else if (currentStep.value === 4) {
        if (!formData.confirmed) newErrors.confirmed = 'Vous devez confirmer les informations';
    }

    Object.assign(errors, newErrors);
    return Object.keys(newErrors).length === 0;
};

const nextStep = () => {
    if (validateStep()) {
        currentStep.value++;
    }
};

const triggerFileUpload = (docId) => {
    const input = document.querySelector(`input[data-doc-id="${docId}"]`);
    if (input) input.click();
};

const handleFileUpload = (event, docId) => {
    const file = event.target.files[0];
    if (file) {
        // Vérification de la taille
        const maxSize = requiredDocuments.value.find(d => d.id === docId).max_size * 1024 * 1024;
        if (file.size > maxSize) {
            alert(`Le fichier est trop volumineux. Taille maximale: ${maxSize / 1024 / 1024}MB`);
            return;
        }

        formData.documents[docId] = {
            file: file,
            name: file.name,
            size: file.size,
            type: file.type
        };
    }
};

const removeDocument = (docId) => {
    delete formData.documents[docId];
};

const getRequestTypeLabel = (type) => {
    const labels = {
        'new': 'Nouvelle demande',
        'renewal': 'Renouvellement',
        'modification': 'Modification'
    };
    return labels[type] || type;
};

const submitRequest = async () => {
    if (!validateStep()) return;

    submitting.value = true;

    try {
        // Création du FormData pour l'envoi
        const submitData = new FormData();
        Object.keys(formData).forEach(key => {
            if (key !== 'documents' && formData[key] !== null && formData[key] !== undefined) {
                submitData.append(key, formData[key]);
            }
        });

        // Ajout des documents
        Object.keys(formData.documents).forEach(docId => {
            submitData.append(`documents[${docId}]`, formData.documents[docId].file);
        });

        submitData.append('service_id', props.service.id);
        submitData.append('user_type', props.user?.type || 'individual');

        // Envoi de la requête
        const response = await axios.post(route('service.request.submit'), submitData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            emit('submitted', response.data.request);
        }
    } catch (error) {
        console.error('Erreur lors de la soumission:', error);
        alert('Une erreur est survenue. Veuillez réessayer.');
    } finally {
        submitting.value = false;
    }
};
</script>
