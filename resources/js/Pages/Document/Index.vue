<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    auth: Object,
    documents: Array,
    allDocumentsCount: Number,
    administrations: Array,
    filters: Object,
    status_labels: Object,
});

const breadcrumb = [
    { name: 'Tableau de bord', href: route('dashboard') },
    { name: 'Mes Documents', href: null }
];

const searchQuery = ref(props.filters?.search || '');
const selectedAdmin = ref(
    (!props.filters?.administration || props.filters.administration === 'all')
    ? 'Tous'
    : props.filters.administration
);

// 🔹 Filtrage côté client (en plus du filtrage serveur pour réactivité)
const filteredDocuments = computed(() => {
    return props.documents.filter(doc => {
        const matchesSearch = doc.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             doc.reference.toLowerCase().includes(searchQuery.value.toLowerCase());

        // On compare avec 'Tous' pour la réactivité locale
        const matchesAdmin = selectedAdmin.value === 'Tous' || doc.admin === selectedAdmin.value;

        return matchesSearch && matchesAdmin;
    });
});

// 🔹 Appliquer les filtres avec rechargement Inertia (pour pagination future)
const applyFilters = () => {
    router.get(route('document.index'), {
        search: searchQuery.value,
        // On renvoie 'all' au backend si 'Tous' est sélectionné pour rester cohérent avec le controller
        administration: selectedAdmin.value === 'Tous' ? 'all' : selectedAdmin.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// 🔹 Formatage de la date pour affichage
const formatDate = (timestamp) => {
    if (!timestamp) return '';
    return new Date(timestamp).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

// 🔹 Calcul de la date d'expiration
const getExpirationInfo = (doc) => {
    if (doc.status === 'valid') {
        const expires = new Date(doc.expires_at);
        const now = new Date();
        const daysLeft = Math.ceil((expires - now) / (1000 * 60 * 60 * 24));
        return daysLeft > 30 ? `Valide ${daysLeft} jours` : `Expire dans ${daysLeft}j`;
    }
    return 'Expiré';
};
</script>

<template>
    <Head title="Mes Documents" />

    <AuthenticatedLayout :user="auth.user" :breadcrumb="breadcrumb">
        <div class="space-y-8">

            <!-- 🔍 Barre de recherche + Filtres -->
            <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="relative w-full md:w-96">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="searchQuery"
                        @input="applyFilters"
                        type="text"
                        placeholder="Rechercher un document..."
                        class="block w-full pl-11 pr-4 py-3 border-transparent bg-gray-50 rounded-2xl focus:ring-secondary-500 focus:bg-white transition-all font-medium text-sm"
                    />
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto">
                    <button
                        v-for="admin in administrations" :key="admin"
                        @click="selectedAdmin = admin; applyFilters()"
                        :class="[
                            'px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all shrink-0',
                            selectedAdmin === admin
                                ? 'bg-secondary-500 text-white shadow-md shadow-secondary-500/20'
                                : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                        ]"
                    >
                        {{ admin }}
                    </button>
                </div>
            </div>

            <!-- 📊 Résumé rapide -->
            <div class="flex items-center justify-between text-sm text-gray-500">
                <p>
                    <span class="font-bold text-gray-900">{{ filteredDocuments.length }}</span>
                    document{{ filteredDocuments.length > 1 ? 's' : '' }} trouvé{{ filteredDocuments.length > 1 ? 's' : '' }}
                    <span v-if="allDocumentsCount > filteredDocuments.length" class="text-gray-400">
                        sur {{ allDocumentsCount }} au total
                    </span>
                </p>
                <div v-if="searchQuery || selectedAdmin !== 'Tous'" class="flex items-center gap-2">
                    <span class="text-gray-400">Filtres actifs:</span>
                    <button
                        @click="searchQuery = ''; selectedAdmin = 'Tous'; applyFilters()"
                        class="text-secondary-500 font-bold hover:underline"
                    >
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- 📁 Grille des documents -->
            <div v-if="filteredDocuments.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div
                    v-for="doc in filteredDocuments"
                    :key="`${doc.type}-${doc.id}`"
                    class="group bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-300 flex flex-col"
                >
                    <div class="flex items-start justify-between mb-6">
                        <div :class="[
                            'h-14 w-14 rounded-2xl flex items-center justify-center shadow-inner transition-colors',
                            doc.type === 'cnps' ? 'bg-blue-50 text-blue-500' : 'bg-emerald-50 text-emerald-500'
                        ]">
                            <!-- Icône CNPS -->
                            <svg v-if="doc.type === 'cnps'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <!-- Icône Customs -->
                            <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                            </svg>
                        </div>
                        <div class="flex flex-col items-end">
                            <span :class="[
                                'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border',
                                doc.status === 'valid'
                                    ? 'bg-secondary-50 text-secondary-600 border-secondary-100'
                                    : 'bg-orange-50 text-orange-600 border-orange-100'
                            ]">
                                {{ status_labels?.[doc.status] || doc.status }}
                            </span>
                            <span class="text-[10px] text-gray-400 font-bold mt-2 uppercase">{{ doc.size }}</span>
                        </div>
                    </div>

                    <div class="flex-1">
                        <p class="text-[10px] font-black text-primary-500 uppercase tracking-widest mb-1">
                            {{ doc.admin }}
                        </p>
                        <h3 class="text-lg font-black text-gray-900 leading-tight group-hover:text-secondary-600 transition-colors">
                            {{ doc.title }}
                        </h3>
                        <p class="text-sm text-gray-400 mt-2 font-medium">
                            Status: {{ doc.status }} depuis le  {{ doc.date }}
                        </p>
                        <p v-if="doc.status === 'valid'" class="text-xs text-secondary-600 font-bold mt-1">
                            • {{ getExpirationInfo(doc) }}
                        </p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-50 flex items-center gap-3">
                        <Link
                            :href="doc.view_url"
                            class="flex-1 bg-gray-50 hover:bg-secondary-500 hover:text-white text-gray-600 font-black text-xs py-3 rounded-xl transition-all uppercase tracking-widest flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Voir
                        </Link>
                        <a
                            :href="doc.download_url"
                            class="h-11 w-11 bg-primary-500 hover:bg-primary-600 text-white flex items-center justify-center rounded-xl shadow-lg shadow-primary-500/30 transition-all active:scale-95"
                            download
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 🚫 État vide -->
            <div v-else class="bg-white rounded-3xl border border-gray-100 py-24 text-center">
                <div class="h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 uppercase">Aucun document trouvé</h3>
                <p class="text-gray-400 mt-2 max-w-sm mx-auto font-medium">
                    <span v-if="searchQuery || selectedAdmin !== 'Tous'">
                        Ajustez vos filtres pour trouver vos justificatifs.
                    </span>
                    <span v-else>
                        Une fois vos demandes approuvées, vos documents apparaîtront ici.
                    </span>
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <button
                        @click="searchQuery = ''; selectedAdmin = 'Tous'; applyFilters()"
                        class="text-secondary-500 font-black uppercase tracking-widest text-xs hover:underline"
                    >
                        Réinitialiser les filtres
                    </button>
                    <Link
                        :href="route('administration-services-list')"
                        class="text-primary-500 font-black uppercase tracking-widest text-xs hover:underline"
                    >
                        + Nouvelle demande
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Couleurs Solid */
.bg-secondary-500 { background-color: #006633; }
.text-secondary-500 { color: #006633; }
.text-secondary-600 { color: #00572c; }
.bg-secondary-50 { background-color: #e6f5ef; }

.bg-primary-500 { background-color: #f39200; }
.text-primary-500 { color: #f39200; }

/* Animation d'entrée */
.grid > div {
    animation: fadeIn 0.5s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
