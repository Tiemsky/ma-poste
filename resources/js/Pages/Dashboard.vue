<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    auth: Object,
    statistics: Object,        // { total, pending, approved, rejected, breakdown }
    requests: Object,          // { cnps: [...], customs: [...] }
    services: Array,           // [{ id, name, key, administration: {...} }]
    status_labels: Object,     // { pending: 'En attente', approved: 'Approuvée', rejected: 'Rejetée' }
});

const breadcrumb = [
    { name: 'Tableau de bord', href: null }
];

// 🔹 Fusionner CNPS + Customs en une seule liste triée par date
const recentRequests = computed(() => {
    const cnps = props.requests?.cnps?.map(req => ({
        id: req.id,
        type: 'cnps',
        title: req.reference || `CNPS #${req.id}`,
        administration: req.service?.administration?.name || 'Non défini',
        service_name: req.service?.name || '',
        date: req.created_at,
        status: req.status,
        // route: route('cnps.show', req.id)
    })) || [];

    const customs = props.requests?.customs?.map(req => ({
        id: req.id,
        type: 'customs',
        title: req.reference || `DOUANE #${req.id}`,
        administration: req.service?.administration?.name || 'Non défini',
        service_name: req.service?.name || '',
        date: req.created_at,
        status: req.status,
        // route: route('customs.show', req.id)
        route: route('dashboard')
    })) || [];

    // Fusion + tri par date décroissante + limite à 10 récents
    return [...cnps, ...customs]
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, 10);
});

// 🔹 Configuration des cartes de statistiques (utilise les données du controller)
const statCards = computed(() => [
    {
        name: 'Total Demandes',
        value: props.statistics?.total ?? 0,
        icon: 'Mbox',
        color: 'bg-blue-500',
        text: 'text-blue-600',
        light: 'bg-blue-50'
    },
    {
        name: props.status_labels?.pending || 'En attente',
        value: props.statistics?.pending ?? 0,
        icon: 'Mclock',
        color: 'bg-primary-500',
        text: 'text-primary-600',
        light: 'bg-primary-50'
    },
    {
        name: props.status_labels?.approved || 'Approuvées',
        value: props.statistics?.approved ?? 0,
        icon: 'Mcheck',
        color: 'bg-secondary-500',
        text: 'text-secondary-600',
        light: 'bg-secondary-50'
    },
    {
        name: props.status_labels?.rejected || 'Rejetées',
        value: props.statistics?.rejected ?? 0,
        icon: 'Mx',
        color: 'bg-red-500',
        text: 'text-red-600',
        light: 'bg-red-50'
    },
]);

// 🔹 Helper pour les classes de statut (dynamique selon les labels)
const getStatusClasses = (status) => {
    const map = {
        'pending': 'bg-primary-50 text-primary-600 border border-primary-100',
        'approved': 'bg-secondary-50 text-secondary-600 border border-secondary-100',
        'rejected': 'bg-red-50 text-red-600 border border-red-100',
    };
    return map[status] || 'bg-gray-50 text-gray-600 border border-gray-100';
};

// 🔹 Helper pour formater la date
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const quickActions = [
    {
        name: 'Nouvelle Déclaration',
        description: 'Effectuer une démarche administrative',
        route: 'administration-services-list',
        icon: 'Mplus',
        color: 'text-primary-500'
    },
    {
        name: 'Mes Documents',
        description: 'Consulter vos justificatifs approuvés',
        route: 'document.index',
        icon: 'Mfolder',
        color: 'text-secondary-500'
    },

];
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout :user="auth.user" :breadcrumb="breadcrumb">

        <div class="space-y-8">
            <!-- 📊 Cartes de statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in statCards" :key="stat.name"
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5 transition-hover hover:shadow-md">
                    <div :class="['h-14 w-14 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0', stat.color]">
                        <!-- Icône : Boîte (Total) -->
                        <svg v-if="stat.name.includes('Total')" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <!-- Icône : Horloge (En attente) -->
                        <svg v-else-if="stat.name.includes('attente')" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Icône : Check (Approuvée) -->
                        <svg v-else-if="stat.name.includes('Approu')" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Icône : X (Rejetée) -->
                        <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-tight">{{ stat.name }}</p>
                        <p class="text-3xl font-black text-gray-900">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- 📋 Section principale : Requêtes récentes + Sidebar -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                <!-- 🗂️ Liste des dossiers récents -->
                <div class="xl:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <h2 class="text-xl font-black text-gray-900 uppercase tracking-tighter">Dossiers Récents</h2>
                            <Link :href="route('document.index')" class="text-sm font-bold text-secondary-500 hover:text-primary-500 transition-colors">
                                Voir tout →
                            </Link>
                        </div>

                        <div class="p-0">
                            <div v-if="recentRequests && recentRequests.length > 0" class="divide-y divide-gray-100">
                                <Link
                                    v-for="request in recentRequests"
                                    :key="`${request.type}-${request.id}`"
                                    :href="request.route"
                                    class="group flex items-center justify-between p-6 hover:bg-gray-50 transition-all"
                                >
                                    <div class="flex items-center gap-5">
                                        <div class="h-12 w-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-white group-hover:text-primary-500 transition-all shadow-sm">
                                            <!-- Icône selon le type de demande -->
                                            <svg v-if="request.type === 'cnps'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 group-hover:text-secondary-600 transition-colors">
                                                {{ request.title }}
                                            </h3>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                                    {{ request.administration }}
                                                </span>
                                                <span v-if="request.service_name" class="text-gray-200">•</span>
                                                <span v-if="request.service_name" class="text-xs text-gray-400 font-medium">
                                                    {{ request.service_name }}
                                                </span>
                                                <span class="text-gray-200">•</span>
                                                <span class="text-xs text-gray-400 font-medium">
                                                    {{ formatDate(request.date) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span :class="[
                                            'px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm',
                                            getStatusClasses(request.status)
                                        ]">
                                            {{ status_labels?.[request.status] || request.status }}
                                        </span>
                                        <svg class="w-5 h-5 text-gray-300 group-hover:text-gray-500 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </Link>
                            </div>

                            <!-- État vide -->
                            <div v-else class="text-center py-20">
                                <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Aucun dossier en cours</h3>
                                <p class="text-gray-400 text-sm max-w-xs mx-auto mt-2">Commencez une nouvelle démarche pour la voir apparaître ici.</p>
                                <Link
                                    :href="route('administration-services-list')"
                                    class="mt-6 inline-flex items-center px-4 py-2 bg-primary-500 text-white text-sm font-bold rounded-xl hover:bg-primary-600 transition-colors"
                                >
                                    + Nouvelle demande
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!--  Sidebar : CTA + Actions rapides -->
                <div class="space-y-6">
                    <!-- Carte d'aide -->
                    <div class="bg-secondary-600 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 h-40 w-40 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all"></div>

                        <h2 class="text-2xl font-black leading-tight mb-4">Besoin d'aide pour vos démarches ?</h2>
                        <p class="text-secondary-100 text-sm mb-8 leading-relaxed">Accédez à notre guide interactif ou prenez rendez-vous avec un conseiller.</p>

                        <Link :href="route('administration-services-list')"
                            class="inline-flex items-center justify-center w-full px-6 py-4 bg-primary-500 hover:bg-white hover:text-secondary-600 text-white font-black rounded-2xl transition-all shadow-lg shadow-black/20 uppercase tracking-widest text-xs">
                            Nouvelle Demande
                        </Link>
                    </div>

                    <!-- Actions rapides -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-tighter mb-6">Actions rapides</h3>
                        <div class="space-y-4">
                            <Link
                                v-for="action in quickActions"
                                :key="action.name"
                                :href="route(action.route)"
                                class="flex items-center gap-4 p-4 rounded-2xl hover:bg-gray-50 border border-transparent hover:border-gray-100 transition-all group"
                            >
                                <div :class="['h-10 w-10 rounded-xl bg-gray-50 flex items-center justify-center group-hover:bg-white group-hover:shadow-sm transition-all']">
                                    <!-- Icône : Plus -->
                                    <svg v-if="action.icon === 'Mplus'" class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <!-- Icône : Dossier -->
                                    <svg v-else-if="action.icon === 'Mfolder'" class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                    <!-- Icône : Chat -->
                                    <svg v-else class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 leading-none">{{ action.name }}</p>
                                    <p class="text-[11px] text-gray-400 mt-1 font-medium">{{ action.description }}</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
