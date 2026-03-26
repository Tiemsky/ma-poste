<script setup>
import { ref } from 'vue';

const props = defineProps({
    service: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'selected']);
const selectedType = ref(null);

const handleSubmit = () => {
    if (selectedType.value) {
        emit('selected', selectedType.value);
    }
};

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="closeModal"
        ></div>

        <!-- Modal Center -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all">
                <!-- Close Button -->
                <button
                    @click="closeModal"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="h-16 w-16 mx-auto mb-4 rounded-full bg-primary-100 flex items-center justify-center">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ service.name }}
                    </h3>
                    <p class="text-gray-600">
                        Sélectionnez votre type d'utilisateur pour continuer
                    </p>
                </div>

                <!-- Options -->
                <div class="space-y-4 mb-8">
                    <label
                        class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-primary-300 hover:bg-primary-50"
                        :class="{ 'border-primary-500 bg-primary-50': selectedType === 'particulier' }"
                    >
                        <input
                            v-model="selectedType"
                            type="radio"
                            value="particulier"
                            class="h-5 w-5 text-primary-600 border-gray-300 focus:ring-primary-500"
                        />
                        <div class="ml-4 flex-1">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-semibold text-gray-900">Particulier</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1 ml-8">
                                Je fais cette demande pour moi-même
                            </p>
                        </div>
                    </label>

                    <label
                        class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-primary-300 hover:bg-primary-50"
                        :class="{ 'border-primary-500 bg-primary-50': selectedType === 'entreprise' }"
                    >
                        <input
                            v-model="selectedType"
                            type="radio"
                            value="entreprise"
                            class="h-5 w-5 text-primary-600 border-gray-300 focus:ring-primary-500"
                        />
                        <div class="ml-4 flex-1">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="font-semibold text-gray-900">Entreprise</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1 ml-8">
                                Je fais cette demande pour mon entreprise
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button
                        @click="closeModal"
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="!selectedType"
                        class="flex-1 px-4 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                    >
                        Continuer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-primary-600 { color: #047857; }
.bg-primary-50 { background-color: #d1fae5; }
.bg-primary-100 { background-color: #a7f3d0; }
.border-primary-500 { border-color: #059669; }
.border-primary-300 { border-color: #34d399; }
.bg-primary-600 { background-color: #059669; }
.hover\:bg-primary-700:hover { background-color: #047857; }
.hover\:bg-primary-50:hover { background-color: #d1fae5; }
.hover\:border-primary-300:hover { border-color: #34d399; }
</style>
