<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    required: {
        type: Boolean,
        default: true,
    },
});

// Même contrat d'émission que CNIUpload :
//   @uploaded="(file) => { ... }"  — émet un File object ou null
const emit = defineEmits(['uploaded']);

const file = ref(null);
const preview = ref(null);

const handleFileSelect = (event) => {
    const selected = event.target.files[0];
    if (!selected) return;

    // Validation — identique à CNIUpload
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    const maxSize = 10 * 1024 * 1024; // 10 MB

    if (!validTypes.includes(selected.type)) {
        alert('Format non supporté. Utilisez JPG, PNG ou PDF.');
        return;
    }
    if (selected.size > maxSize) {
        alert('Fichier trop lourd. Maximum 10 MB.');
        return;
    }

    file.value = selected;

    // Preview image uniquement (pas pour les PDF)
    if (selected.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => { preview.value = e.target.result; };
        reader.readAsDataURL(selected);
    } else {
        preview.value = null;
    }

    emit('uploaded', selected);
};

const removeFile = () => {
    file.value = null;
    preview.value = null;
    emit('uploaded', null);
};
</script>

<template>
    <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">
            Facture commerciale / Invoice
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Zone d'upload — même structure que CNIUpload mais sur une seule colonne -->
        <div class="border-2 border-dashed rounded-xl p-4 text-center transition-all"
             :class="{
                 'border-green-400 bg-green-50': file,
                 'border-gray-300 hover:border-blue-400': !file,
             }">
            <input
                type="file"
                id="invoice-file"
                @change="handleFileSelect"
                accept="image/jpeg,image/jpg,image/png,application/pdf"
                class="hidden"
            />

            <label for="invoice-file" class="cursor-pointer block">
                <!-- Preview image -->
                <div v-if="preview" class="mb-3">
                    <img :src="preview" alt="Aperçu facture" class="h-32 mx-auto rounded-lg object-cover shadow-sm" />
                </div>

                <!-- Fichier PDF sélectionné -->
                <div v-else-if="file" class="mb-3">
                    <div class="h-32 mx-auto rounded-lg bg-white border border-green-200 flex flex-col items-center justify-center p-2 max-w-xs mx-auto">
                        <span class="text-2xl mb-1">📄</span>
                        <span class="text-gray-600 text-xs truncate w-full px-2 text-center">{{ file.name }}</span>
                    </div>
                </div>

                <!-- Zone vide -->
                <div v-else class="py-4">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 font-bold">Facture commerciale</p>
                    <p class="text-xs text-gray-400">JPG, PNG ou PDF (Max 10 MB)</p>
                </div>
            </label>

            <button
                v-if="file"
                type="button"
                @click.stop="removeFile"
                class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium transition"
            >
                Retirer le fichier
            </button>
        </div>

        <!-- Confirmation — même badge que CNIUpload -->
        <div v-if="file"
             class="flex items-center text-green-600 text-xs font-bold justify-center bg-green-50 py-2 rounded-lg border border-green-100">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
            </svg>
            Facture prête pour l'envoi
        </div>
    </div>
</template>
