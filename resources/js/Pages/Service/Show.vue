<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ServiceCard from '@/Components/ServiceCard.vue';

const props = defineProps({
    auth: Object,
    administration: Object,
    services: Array,
});

const searchQuery = ref('');

const filteredServices = ref(props.services || []);

const handleSearch = () => {
    if (!searchQuery.value) {
        filteredServices.value = props.services;
        return;
    }

    filteredServices.value = props.services.filter(service =>
        service.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (service.description && service.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
};
</script>

<template>
    <Head :title="`${administration?.name} - Services`" />

    <AuthenticatedLayout :user="auth.user">
        <!-- Header Section -->
        <div class="mb-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ administration?.name }}
                    </h1>
                    <p class="text-gray-600">
                        {{ administration?.description || 'Sélectionnez un service pour commencer votre demande' }}
                    </p>
                </div>

                <!-- Search Bar -->
                <div class="relative w-full md:w-96">
                    <input
                        v-model="searchQuery"
                        @input="handleSearch"
                        type="text"
                        placeholder="Rechercher un service..."
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    />
                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Administration Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-lg bg-primary-100 flex items-center justify-center mr-4">
                        <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Administration</p>
                        <p class="font-semibold text-gray-900">{{ administration?.name }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Services disponibles</p>
                        <p class="font-semibold text-gray-900">{{ services?.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Temps moyen</p>
                        <p class="font-semibold text-gray-900">24-48h</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Grid -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                Nos Services
            </h2>

            <div v-if="filteredServices.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ServiceCard
                        v-for="service in filteredServices"
                        :key="service.id"
                        :service="service"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Aucun service trouvé</h3>
                <p class="mt-2 text-gray-500">
                    {{ searchQuery ? 'Essayez avec d\'autres termes de recherche' : 'Aucun service disponible pour le moment' }}
                </p>
                <button
                    v-if="searchQuery"
                    @click="searchQuery = ''; filteredServices = services"
                    class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Réinitialiser la recherche
                </button>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.text-primary-600 { color: #047857; }
.bg-primary-100 { background-color: #a7f3d0; }
.bg-primary-500 { background-color: #059669; }
.bg-primary-600 { background-color: #047857; }
.from-primary-500 { --tw-gradient-from: #059669; }
.to-primary-600 { --tw-gradient-to: #047857; }
.hover\:bg-primary-700:hover { background-color: #065f46; }
.hover\:bg-primary-50:hover { background-color: #d1fae5; }
</style>
