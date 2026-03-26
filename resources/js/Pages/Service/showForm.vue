<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    service: Object,
    userType: String,
    userData: Object,
    serviceFields: Array
});

const page = usePage();
const currentStep = ref(1);
const isSubmitting = ref(false);

// Données du formulaire
const formData = ref({
    // Informations utilisateur (pré-remplies)
    userInfo: {
        last_name: props.userData?.last_name || '',
        first_name: props.userData?.first_name || '',
        email: props.userData?.email || '',
        phone: props.userData?.phone || '',
        address: props.userData?.address || '',
        city: props.userData?.city || '',
        postal_code: props.userData?.postal_code || '',
        // Champs spécifiques entreprise
        company_name: props.userData?.company_name || '',
        company_registration: props.userData?.company_registration || '',
        company_address: props.userData?.company_address || '',
    },
    // Données du service
    serviceData: {},
    // Documents
    documents: []
});

// Initialiser les champs de service
props.serviceFields?.forEach(field => {
    formData.value.serviceData[field.key] = field.default || '';
});

// Étapes du formulaire
const steps = computed(() => [
    {
        id: 1,
        title: 'Informations personnelles',
        icon: 'user',
        completed: currentStep.value > 1
    },
    {
        id: 2,
        title: 'Détails du service',
        icon: 'document',
        completed: currentStep.value > 2
    },
    {
        id: 3,
        title: 'Documents',
        icon: 'upload',
        completed: currentStep.value > 3
    },
    {
        id: 4,
        title: 'Confirmation',
        icon: 'check',
        completed: false
    }
]);

// Validation
const validateStep = (step) => {
    if (step === 1) {
        const info = formData.value.userInfo;
        if (!info.last_name || !info.first_name || !info.email || !info.phone) {
            alert('Veuillez remplir tous les champs obligatoires');
            return false;
        }
        if (props.userType === 'entreprise' && (!info.company_name || !info.company_registration)) {
            alert('Veuillez remplir les informations de l\'entreprise');
            return false;
        }
    }
    if (step === 2) {
        // Valider les champs spécifiques au service
        const requiredFields = props.serviceFields?.filter(f => f.required) || [];
        for (let field of requiredFields) {
            if (!formData.value.serviceData[field.key]) {
                alert(`Veuillez remplir le champ: ${field.label}`);
                return false;
            }
        }
    }
    return true;
};

// Navigation
const nextStep = () => {
    if (validateStep(currentStep.value)) {
        if (currentStep.value < steps.value.length) {
            currentStep.value++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
};

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const goToStep = (step) => {
    if (step < currentStep.value || validateStep(currentStep.value)) {
        currentStep.value = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

// Upload de documents
const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        formData.value.documents.push({
            file: file,
            name: file.name,
            size: (file.size / 1024 / 1024).toFixed(2) + ' MB',
            type: file.type
        });
    });
};

const removeDocument = (index) => {
    formData.value.documents.splice(index, 1);
};

// Soumission
const submitForm = async () => {
    if (!validateStep(3)) return;

    isSubmitting.value = true;

    try {
        const formDataToSend = new FormData();
        formDataToSend.append('service_key', props.service.key);
        formDataToSend.append('user_type', props.userType);
        formDataToSend.append('user_info', JSON.stringify(formData.value.userInfo));
        formDataToSend.append('service_data', JSON.stringify(formData.value.serviceData));

        formData.value.documents.forEach((doc, index) => {
            formDataToSend.append(`documents[${index}]`, doc.file);
        });

        router.post(route('service.submit'), formDataToSend, {
            onFinish: () => {
                isSubmitting.value = false;
            },
            onSuccess: () => {
                // Redirection vers la page de succès
            }
        });
    } catch (error) {
        console.error('Erreur lors de la soumission:', error);
        isSubmitting.value = false;
    }
};

const breadcrumb = [
    { name: 'Accueil', href: route('dashboard') },
    { name: props.service?.name || 'Service' }
];
</script>

<template>
    <Head :title="`Formulaire - ${service?.name}`" />

    <AuthenticatedLayout :user="page.props.auth.user" >
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-2 text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    {{ service?.name }}
                </h1>
                <p class="text-gray-600">
                    {{ userType === 'particulier' ? 'Demande en tant que Particulier' : 'Demande en tant qu\'Entreprise' }}
                </p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="relative">
                    <!-- Progress Bar Background -->
                    <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2 rounded-full"></div>

                    <!-- Progress Bar Active -->
                    <div
                        class="absolute top-1/2 left-0 h-1 bg-primary-500 -translate-y-1/2 rounded-full transition-all duration-500"
                        :style="{ width: `${((currentStep - 1) / (steps.length - 1)) * 100}%` }"
                    ></div>

                    <!-- Steps -->
                    <div class="relative flex justify-between">
                        <div
                            v-for="step in steps"
                            :key="step.id"
                            class="flex flex-col items-center cursor-pointer"
                            @click="goToStep(step.id)"
                        >
                            <!-- Circle -->
                            <div
                                class="w-12 h-12 rounded-full flex items-center justify-center border-4 transition-all duration-300 mb-2"
                                :class="{
                                    'bg-primary-500 border-primary-500 text-white': step.completed || currentStep >= step.id,
                                    'bg-white border-gray-300 text-gray-400': !step.completed && currentStep < step.id,
                                    'hover:border-primary-400': currentStep < step.id
                                }"
                            >
                                <svg v-if="step.completed" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="font-bold">{{ step.id }}</span>
                            </div>

                            <!-- Label -->
                            <span
                                class="text-xs font-medium text-center max-w-[100px]"
                                :class="{
                                    'text-primary-600': currentStep >= step.id,
                                    'text-gray-500': currentStep < step.id
                                }"
                            >
                                {{ step.title }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200">
                <div class="p-8">
                    <!-- Step 1: User Information -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Informations {{ userType === 'entreprise' ? 'de l\'entreprise' : 'personnelles' }}</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="formData.userInfo.last_name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    placeholder="Votre nom"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Prénom <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="formData.userInfo.first_name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    placeholder="Votre prénom"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="formData.userInfo.email"
                                    type="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    placeholder="votre@email.com"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Téléphone <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="formData.userInfo.phone"
                                    type="tel"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    placeholder="+225 XX XX XX XX"
                                    required
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Adresse
                                </label>
                                <textarea
                                    v-model="formData.userInfo.address"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    placeholder="Votre adresse complète"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Entreprise Fields -->
                        <div v-if="userType === 'entreprise'" class="mt-8 pt-8 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations de l'entreprise</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nom de l'entreprise <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.userInfo.company_name"
                                        type="text"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                        placeholder="Nom de l'entreprise"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Numéro RCCM/IFU <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.userInfo.company_registration"
                                        type="text"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                        placeholder="Numéro d'enregistrement"
                                        required
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Adresse de l'entreprise
                                    </label>
                                    <textarea
                                        v-model="formData.userInfo.company_address"
                                        rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                        placeholder="Adresse complète de l'entreprise"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Service Fields -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Détails du service</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="field in serviceFields" :key="field.key">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </label>

                                <!-- Text Input -->
                                <input
                                    v-if="field.type === 'text' || field.type === 'email' || field.type === 'tel'"
                                    :type="field.type"
                                    v-model="formData.serviceData[field.key]"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    :placeholder="field.placeholder || ''"
                                    :required="field.required"
                                />

                                <!-- Select -->
                                <select
                                    v-else-if="field.type === 'select'"
                                    v-model="formData.serviceData[field.key]"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    :required="field.required"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option
                                        v-for="option in field.options"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>

                                <!-- Textarea -->
                                <textarea
                                    v-else-if="field.type === 'textarea'"
                                    v-model="formData.serviceData[field.key]"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    :placeholder="field.placeholder || ''"
                                    :required="field.required"
                                ></textarea>

                                <!-- Date -->
                                <input
                                    v-else-if="field.type === 'date'"
                                    type="date"
                                    v-model="formData.serviceData[field.key]"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    :required="field.required"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Documents -->
                    <div v-if="currentStep === 3" class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Documents à uploader</h2>

                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-primary-400 transition-colors">
                            <input
                                type="file"
                                multiple
                                @change="handleFileUpload"
                                class="hidden"
                                id="file-upload"
                            />
                            <label for="file-upload" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mt-4 text-sm text-gray-600">
                                    <span class="font-medium text-primary-600">Cliquez pour uploader</span> ou glissez-déposez
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    PDF, JPG, PNG jusqu'à 10MB
                                </p>
                            </label>
                        </div>

                        <!-- Document List -->
                        <div v-if="formData.documents.length > 0" class="space-y-3">
                            <h3 class="font-semibold text-gray-900">Documents uploadés ({{ formData.documents.length }})</h3>
                            <div
                                v-for="(doc, index) in formData.documents"
                                :key="index"
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ doc.name }}</p>
                                        <p class="text-sm text-gray-500">{{ doc.size }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="removeDocument(index)"
                                    class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Confirmation -->
                    <div v-if="currentStep === 4" class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Confirmation et résumé</h2>

                        <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Informations personnelles</h3>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <span class="text-gray-600">Nom:</span>
                                    <span class="font-medium">{{ formData.userInfo.last_name }} {{ formData.userInfo.first_name }}</span>
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium">{{ formData.userInfo.email }}</span>
                                    <span class="text-gray-600">Téléphone:</span>
                                    <span class="font-medium">{{ formData.userInfo.phone }}</span>
                                </div>
                            </div>

                            <div v-if="userType === 'entreprise'" class="pt-4 border-t border-gray-200">
                                <h3 class="font-semibold text-gray-900 mb-2">Informations entreprise</h3>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <span class="text-gray-600">Entreprise:</span>
                                    <span class="font-medium">{{ formData.userInfo.company_name }}</span>
                                    <span class="text-gray-600">RCCM/IFU:</span>
                                    <span class="font-medium">{{ formData.userInfo.company_registration }}</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <h3 class="font-semibold text-gray-900 mb-2">Détails du service</h3>
                                <div class="space-y-2 text-sm">
                                    <div v-for="(value, key) in formData.serviceData" :key="key" class="grid grid-cols-2 gap-2">
                                        <span class="text-gray-600">{{ key }}:</span>
                                        <span class="font-medium">{{ value }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <h3 class="font-semibold text-gray-900 mb-2">Documents ({{ formData.documents.length }})</h3>
                                <ul class="text-sm text-gray-600">
                                    <li v-for="(doc, index) in formData.documents" :key="index" class="flex items-center">
                                        <svg class="h-4 w-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ doc.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm text-yellow-800">
                                    En soumettant ce formulaire, vous confirmez que toutes les informations fournies sont exactes et complètes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 rounded-b-2xl flex justify-between">
                    <button
                        v-if="currentStep > 1"
                        @click="previousStep"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-white font-medium transition-colors flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Précédent
                    </button>
                    <div v-else></div>

                    <button
                        v-if="currentStep < steps.length"
                        @click="nextStep"
                        class="px-8 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors shadow-lg flex items-center"
                    >
                        Suivant
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <button
                        v-else
                        @click="submitForm"
                        :disabled="isSubmitting"
                        class="px-8 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors shadow-lg flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="!isSubmitting" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Soumission...' : 'Soumettre la demande' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.text-primary-600 { color: #047857; }
.bg-primary-500 { background-color: #059669; }
.border-primary-500 { border-color: #059669; }
.bg-primary-600 { background-color: #059669; }
.hover\:bg-primary-700:hover { background-color: #047857; }
.hover\:border-primary-400:hover { border-color: #34d399; }
</style>
