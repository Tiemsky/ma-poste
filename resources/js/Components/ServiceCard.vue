<!-- resources/js/Components/ServiceCard.vue -->
<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import UserTypeSelection from './UserTypeSelection.vue';

const props = defineProps({
    service: {
        type: Object,
        required: true
    }
});

const showUserTypeModal = ref(false);

const handleServiceClick = () => {
    showUserTypeModal.value = true;
};

const handleUserTypeSelected = (userType) => {
    // Redirection vers le formulaire multi-étapes
    router.get(route('service.form', {
        serviceKey: props.service.key,
        userType: userType
    }));
};
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm border-2 border-gray-100 p-6 hover:shadow-lg transition-all duration-300 cursor-pointer group h-full flex flex-col"
        :class="{
            'opacity-80': service.status !== 'active',
            'hover:border-primary-300': service.status === 'active',
            'hover:-translate-y-1': service.status === 'active'
        }"
      @click="service.status === 'active' && handleServiceClick()"
    >
        <div class="flex flex-col items-center text-center flex-1">
            <!-- Logo Container -->
            <div class="h-20 w-20 mb-4 rounded-xl flex items-center justify-center border border-gray-200"
              :class="{
                'bg-gray-50 group-hover:bg-primary-50 transition-colors duration-300  group-hover:border-primary-200': service.status === 'active'
              }"
              >
                <div class="h-14 w-14 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center text-white font-bold text-2xl shadow-md">
                    {{ service.name.charAt(0) }}
                </div>
            </div>

            <!-- Title -->
            <h3 class="font-bold text-gray-900 mb-2 text-lg group-hover:text-primary-600 transition-colors">
                {{ service.name }}
            </h3>

            <!-- Description -->
            <p class="text-sm text-gray-500 mb-3">
                {{ service.description }}
            </p>

            <!-- Badge Status -->
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border"
                :class="{
                    'bg-green-100 text-green-800 border-green-200': service.status === 'active',
                    'bg-yellow-100 text-yellow-800 border-yellow-200': service.status === 'maintenance'
                }"
            >
                <svg v-if="service.status === 'active'" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                {{ service.status === 'active' ? 'Disponible' : 'Bientôt disponible' }}
            </span>
        </div>
    </div>

    <!-- Modal Selection Type Utilisateur -->
    <UserTypeSelection
        v-if="showUserTypeModal"
        :service="service"
        @close="showUserTypeModal = false"
        @selected="handleUserTypeSelected"
    />
</template>

<style scoped>
.text-primary-600 { color: #047857; }
.bg-primary-50 { background-color: #d1fae5; }
.border-primary-200 { border-color: #6ee7b7; }
.border-primary-300 { border-color: #34d399; }
.from-primary-400 { --tw-gradient-from: #34d399; }
.to-primary-600 { --tw-gradient-to: #047857; }
.hover\:border-primary-300:hover { border-color: #34d399; }
.hover\:bg-primary-50:hover { background-color: #d1fae5; }
.hover\:text-primary-600:hover { color: #047857; }
.hover\:-translate-y-1:hover { transform: translateY(-0.25rem); }
</style>
