<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    breadcrumb: { type: Array, default: () => [] },
    showHero: { type: Boolean, default: true }
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const sidebarOpen = ref(false);

const navigation = [
    { name: 'Tableau de bord', href: route('dashboard'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', active: route().current('dashboard') },
    { name: 'Nouvelle Demande', href: route('administration-services-list'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', active: route().current('administration-services-list') },
    { name: 'Mes Déclarations', href: route('document.index'), icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', active: route().current('document.index')},
    // { name: 'Mon Profil', href: route('profile.edit'), icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', active: route().current('profile.edit') },
];
</script>

<template>
    <div class="h-screen flex overflow-hidden bg-gray-50">

        <aside
            :class="[
                'fixed lg:static inset-y-0 left-0 z-50 w-72 bg-secondary-500 flex flex-col h-full shadow-xl transition-transform duration-300 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <div class="h-24 flex items-center px-8 border-b border-secondary-600/50 bg-white flex-shrink-0">
                <Link :href="route('dashboard')" class="flex items-center">
                    <ApplicationLogo class="w-auto text-white fill-current" />
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2 custom-sidebar">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center px-4 py-3.5 text-sm font-bold rounded-xl transition-all group"
                    :class="[
                        item.active
                            ? 'bg-white text-secondary-600 shadow-md'
                            : 'text-secondary-50 hover:bg-secondary-600 hover:text-white'
                    ]"
                >
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <div class="p-6 border-t border-secondary-600/50 flex-shrink-0">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-11 w-11 rounded-xl bg-primary-500 flex items-center justify-center text-white font-black shadow-sm">
                        {{ user.last_name?.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate leading-none mb-1">{{ user.first_name }} {{ user.last_name }}</p>
                        <p class="text-[10px] text-secondary-200 uppercase tracking-widest font-bold">Connecté</p>
                    </div>
                </div>

                <Link
                    :href="route('logout')"
                    method="post" as="button"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold bg-white text-secondary-600 rounded-xl hover:bg-primary-500 hover:text-white transition-all shadow-sm"
                >
                    Déconnexion
                </Link>
            </div>
        </aside>

        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

            <button v-if="!sidebarOpen" @click="sidebarOpen = true" class="lg:hidden fixed top-4 left-4 z-40 p-2 bg-secondary-500 text-white rounded-lg shadow-lg">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>


             <header class="bg-white border-b border-gray-200 h-24 flex items-center sticky top-0 z-30 px-4 sm:px-8">

                <div class="flex-1 flex items-center justify-between">


                    <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-500 hover:bg-gray-100 rounded-lg">

                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>

                    </button>


                    <nav class="hidden sm:flex" aria-label="Breadcrumb">

                        <ol class="flex items-center space-x-3">

                            <li v-for="(item, index) in breadcrumb" :key="index" class="flex items-center gap-3 text-sm">

                                <span v-if="index > 0" class="text-gray-300">/</span>

                                <Link

                                    v-if="item.href" :href="item.href"

                                    class="font-medium" :class="index === breadcrumb.length - 1 ? 'text-secondary-500 font-bold' : 'text-gray-500 hover:text-secondary-500'"

                                >

                                    {{ item.name }}

                                </Link>

                                <span v-else class="text-gray-900 font-bold">{{ item.name }}</span>

                            </li>

                        </ol>

                    </nav>


                    <div class="flex items-center gap-3">

                        <div class="hidden sm:block text-right">

                            <p class="text-sm font-bold text-gray-900 leading-tight">{{ user.last_name }}</p>

                            <p class="text-xs text-primary-600 font-bold uppercase tracking-wider">Session Active</p>

                        </div>

                        <div class="h-10 w-10 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center text-secondary-500 font-black">

                            {{ user.last_name?.charAt(0) }}

                        </div>

                    </div>

                </div>

            </header>



            <main class="flex-1 overflow-y-auto bg-gray-50 scroll-smooth">
                <div class="py-10 px-8 max-w-7xl mx-auto">
                    <div class="animate-in fade-in slide-in-from-bottom-3 duration-500">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Scrollbar personnalisée pour la sidebar pour rester discret */
.custom-sidebar::-webkit-scrollbar {
    width: 4px;
}
.custom-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

</style>
