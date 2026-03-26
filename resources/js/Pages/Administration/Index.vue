<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdministrationCard from '@/Components/AdministrationCard.vue';

const props = defineProps({
    auth: Object,
    administrations: Object,
    recentRequests: Array,
});


const newRequestModal = ref(false);


const handleFiltersUpdated = (filters) => {
    console.log('Filtres mis à jour:', filters);
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout
        :user="auth.user"
        @filters-updated="handleFiltersUpdated"
    >

        <!-- Section Liste des Administrations -->
         <div class="mb-4">
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
