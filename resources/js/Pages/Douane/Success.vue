<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    declaration: Object // { reference, created_at, declarant_name, transport_mode, status, items: [...] }
});

const transportLabels = { air: 'Aérien', sea: 'Maritime', road: 'Routier' };

const globalTotal = props.declaration.items?.reduce(
    (acc, item) => acc + Number(item.total_value), 0
) ?? 0;

const formatCurrency = (value) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(value);

const printReceipt = () => window.print();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Déclaration Douane Confirmée" />

        <div class="max-w-3xl mx-auto py-12 px-4">
            <div id="receipt" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100 relative overflow-hidden">

                <!-- Badge statut -->
                <div class="absolute top-0 right-0 p-4 print:hidden">
                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">Dossier Complet</span>
                </div>

                <!-- Icône succès -->
                <div class="text-center mb-10">
                    <div class="w-16 h-16 bg-amber-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg print:hidden">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-black text-gray-900">Déclaration Enregistrée</h1>
                    <p class="text-gray-500 mt-1">Votre déclaration a été transmise aux services douaniers.</p>
                </div>

                <!-- Référence -->
                <div class="bg-gray-50 rounded-2xl p-8 border-2 border-dashed border-gray-200 text-center mb-8">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Référence unique de suivi</p>
                    <p class="text-4xl font-mono font-black text-amber-600">{{ declaration.reference }}</p>
                </div>

                <!-- Informations -->
                <div class="grid grid-cols-2 gap-y-4 text-sm border-t pt-8">
                    <div class="text-gray-500">Date de dépôt :</div>
                    <div class="text-right font-bold">
                        {{ new Date(declaration.created_at).toLocaleDateString('fr-FR') }}
                    </div>

                    <div class="text-gray-500">Déclarant :</div>
                    <div class="text-right font-bold">{{ declaration.declarant_name }}</div>

                    <div class="text-gray-500">Mode de transport :</div>
                    <div class="text-right font-bold">
                        {{ transportLabels[declaration.transport_mode] ?? declaration.transport_mode }}
                    </div>

                    <div class="text-gray-500">Provenance :</div>
                    <div class="text-right font-bold">{{ declaration.departure_country }}</div>

                    <template v-if="declaration.items?.length">
                        <div class="text-gray-500">Articles déclarés :</div>
                        <div class="text-right font-bold">{{ declaration.items.length }} article(s)</div>

                        <div class="text-gray-500">Valeur totale déclarée :</div>
                        <div class="text-right font-bold text-amber-700">{{ formatCurrency(globalTotal) }}</div>
                    </template>

                    <div class="text-gray-500">Statut actuel :</div>
                    <div class="text-right">
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded font-bold text-xs">EN ATTENTE</span>
                    </div>
                </div>

                <!-- Articles — visible à l'impression -->
                <div v-if="declaration.items?.length" class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Détail des marchandises</p>
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-100">
                                <th class="text-left pb-2 font-bold">Désignation</th>
                                <th class="text-right pb-2 font-bold">Qté</th>
                                <th class="text-right pb-2 font-bold">P.U.</th>
                                <th class="text-right pb-2 font-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in declaration.items" :key="item.id">
                                <td class="py-1.5 text-gray-700 font-semibold">{{ item.name }}</td>
                                <td class="py-1.5 text-right text-gray-500 font-mono">{{ item.quantity }}</td>
                                <td class="py-1.5 text-right text-gray-500 font-mono">{{ formatCurrency(item.unit_value) }}</td>
                                <td class="py-1.5 text-right font-bold font-mono text-amber-700">{{ formatCurrency(item.total_value) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="pt-2 text-right font-bold text-gray-600 pr-4">Total</td>
                                <td class="pt-2 text-right font-extrabold font-mono text-gray-900">{{ formatCurrency(globalTotal) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Footer légal -->
                <div class="mt-12 pt-8 border-t border-gray-100 text-center text-[10px] text-gray-400">
                    Document généré électroniquement.<br>
                    Authenticité vérifiable via le code référence ci-dessus.
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 print:hidden">
                <Link :href="route('dashboard')"
                      class="flex-1 text-center py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-200 transition">
                    Tableau de bord
                </Link>
                <button @click="printReceipt"
                        class="flex-1 py-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl transition shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimer le reçu
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
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
