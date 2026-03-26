<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    declaration: Object // Reçu du backend
});

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Déclaration Confirmée" />

        <div class="max-w-3xl mx-auto py-12 px-4">
            <div id="receipt" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100 relative overflow-hidden">

                <div class="absolute top-0 right-0 p-4 print:hidden">
                    <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Dossier Complet</span>
                </div>

                <div class="text-center mb-10">
                    <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg print:hidden">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h1 class="text-3xl font-black text-gray-900">Soumission Réussie</h1>
                    <p class="text-gray-500 mt-1">Votre déclaration a été transmise aux services CNPS.</p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-8 border-2 border-dashed border-gray-200 text-center mb-8">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Référence unique de suivi</p>
                    <p class="text-4xl font-mono font-black text-primary-600">{{ declaration.reference }}</p>
                </div>

                <div class="grid grid-cols-2 gap-y-4 text-sm border-t pt-8">
                    <div class="text-gray-500">Date de dépôt :</div>
                    <div class="text-right font-bold">{{ new Date(declaration.created_at).toLocaleDateString('fr-FR') }}</div>

                    <div class="text-gray-500">Déclarant :</div>
                    <div class="text-right font-bold">{{ declaration.employer_name }}</div>

                    <div class="text-gray-500">Type de service :</div>
                    <div class="text-right font-bold uppercase">{{ declaration.service_key }}</div>

                    <div class="text-gray-500">Statut actuel :</div>
                    <div class="text-right"><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded font-bold text-xs">EN ATTENTE</span></div>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-100 text-center text-[10px] text-gray-400">
                    Document généré électroniquement. <br>
                    Authenticité vérifiable via le code référence ci-dessus.
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-4 print:hidden">
                <Link :href="route('dashboard')" class="flex-1 text-center py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-200 transition">
                    Tableau de bord
                </Link>
                <button @click="printReceipt" class="flex-1 py-4 bg-primary-600 text-white font-bold rounded-2xl hover:bg-primary-700 transition shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Imprimer le reçu
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    /* Cache tout sauf le reçu */
    body * { visibility: hidden; }
    #receipt, #receipt * { visibility: visible; }
    #receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none;
        box-shadow: none;
    }
}
</style>
