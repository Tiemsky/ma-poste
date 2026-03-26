<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdministrationCard from '@/Components/AdministrationCard.vue';

const props = defineProps({
    auth: Object,
    recentRequests: Array,
});

const breadcrumb = [
    { name: 'Accueil', href: route('dashboard') }
];

const newRequestModal = ref(false);

const administrations = [
    {
        id: 1,
        name: 'La Poste',
        subtitle: 'Poste 5.0',
        logo: '/images/logos/poste.png',
        available: true,
        // route: 'services.poste'
    },
    {
        id: 2,
        name: 'ONECI',
        subtitle: 'Identification',
        logo: '/images/logos/oneci.png',
        available: true,
        // route: 'services.oneci'
    },
    {
        id: 3,
        name: 'Impôts',
        subtitle: 'Direction Générale des Impôts',
        logo: '/images/logos/impots.png',
        available: false
    },
    {
        id: 4,
        name: 'Mairie / Sous-préfecture',
        subtitle: 'Administration locale',
        logo: '/images/logos/mairie.png',
        available: false
    },
    {
        id: 5,
        name: 'Ministère de la Construction',
        subtitle: 'MCLU',
        logo: '/images/logos/mclu.png',
        available: false
    },
    {
        id: 6,
        name: 'Fonction Publique',
        subtitle: 'MFPRA',
        logo: '/images/logos/mfpra.png',
        available: false
    },
    {
        id: 7,
        name: 'Ministère de la Justice',
        subtitle: 'Justice',
        logo: '/images/logos/justice.png',
        available: false
    },
    {
        id: 8,
        name: 'Police Nationale',
        subtitle: 'Sécurité',
        logo: '/images/logos/police.png',
        available: false
    }
];

const handleFiltersUpdated = (filters) => {
    console.log('Filtres mis à jour:', filters);
    // Ici vous pouvez recharger les données en fonction des filtres
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout
        :user="auth.user"
        :breadcrumb="breadcrumb"
        @filters-updated="handleFiltersUpdated"
    >


        <!-- Section Liste des Administrations -->
        <!-- <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                Liste des administrations
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <AdministrationCard
                    v-for="admin in administrations"
                    :key="admin.id"
                    :administration="admin"
                />
            </div>
        </div> -->

        <!-- Section Mes Demandes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Mes Demandes</h2>
            </div>

            <div class="p-6">
                <div v-if="recentRequests && recentRequests.length > 0">
                    <div class="space-y-4">
                        <div
                            v-for="request in recentRequests"
                            :key="request.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ request.title }}</h3>
                                    <p class="text-sm text-gray-500">{{ request.administration }} • {{ request.date }}</p>
                                </div>
                            </div>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-800': request.status === 'approuvée',
                                    'bg-yellow-100 text-yellow-800': request.status === 'en attente',
                                    'bg-red-100 text-red-800': request.status === 'rejetée'
                                }"
                            >
                                {{ request.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune demande</h3>
                    <p class="mt-1 text-sm text-gray-500">Vous n'avez aucune demande pour le moment.</p>
                    <div class="mt-6">
                        <Link
                           :href="route('administration-services-list')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nouvelle demande
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.text-primary-500 {
    color: #059669;
}
.bg-primary-500 {
    background-color: #059669;
}
.bg-primary-600 {
    background-color: #047857;
}
.hover\:bg-primary-600:hover {
    background-color: #047857;
}
.hover\:bg-primary-700:hover {
    background-color: #065f46;
}
.text-primary-600 {
    color: #047857;
}
.bg-primary-50 {
    background-color: #d1fae5;
}
.bg-primary-100 {
    background-color: #a7f3d0;
}
</style>
