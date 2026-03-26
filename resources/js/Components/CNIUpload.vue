<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ recto: null, verso: null })
    },
    required: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'uploaded']);

// On initialise les refs avec les valeurs du modelValue si elles existent
const rectoFile = ref(props.modelValue?.recto || null);
const versoFile = ref(props.modelValue?.verso || null);

const rectoPreview = ref(null);
const versoPreview = ref(null);

const handleFileSelect = (event, side) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validation
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (!validTypes.includes(file.type)) {
        alert('Format non supporté. Utilisez JPG, PNG ou PDF.');
        return;
    }

    if (file.size > maxSize) {
        alert('Fichier trop lourd. Maximum 5MB.');
        return;
    }

    if (side === 'recto') {
        rectoFile.value = file;
    } else {
        versoFile.value = file;
    }

    // Gestion de la preview
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            if (side === 'recto') {
                rectoPreview.value = e.target.result;
            } else {
                versoPreview.value = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    } else {
        // Reset preview si c'est un PDF
        side === 'recto' ? rectoPreview.value = null : versoPreview.value = null;
    }

    updateModel();
};

const updateModel = () => {
    // Émettre l'objet contenant les fichiers réels pour Inertia
    const payload = {
        recto: rectoFile.value,
        verso: versoFile.value
    };

    emit('update:modelValue', payload);

    // Émettre un événement simple pour le parent (utile pour la validation visuelle)
    emit('uploaded', payload);
};

const removeFile = (side) => {
    if (side === 'recto') {
        rectoFile.value = null;
        rectoPreview.value = null;
    } else {
        versoFile.value = null;
        versoPreview.value = null;
    }
    updateModel();
};

// Watcher pour synchroniser si le parent reset le formulaire
watch(() => props.modelValue, (newVal) => {
    if (!newVal.recto) {
        rectoFile.value = null;
        rectoPreview.value = null;
    }
    if (!newVal.verso) {
        versoFile.value = null;
        versoPreview.value = null;
    }
}, { deep: true });

</script>

<template>
    <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">
            Carte Nationale d'Identité (CNI)
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border-2 border-dashed rounded-xl p-4 text-center transition-all"
                 :class="{
                     'border-green-400 bg-green-50': rectoFile,
                     'border-gray-300 hover:border-blue-400': !rectoFile
                 }">
                <input
                    type="file"
                    @change="(e) => handleFileSelect(e, 'recto')"
                    accept="image/jpeg,image/jpg,image/png,application/pdf"
                    class="hidden"
                    id="cni-recto"
                />

                <label for="cni-recto" class="cursor-pointer block">
                    <div v-if="rectoPreview" class="mb-3">
                        <img :src="rectoPreview" alt="CNI Recto" class="h-32 mx-auto rounded-lg object-cover shadow-sm" />
                    </div>
                    <div v-else-if="rectoFile" class="mb-3">
                        <div class="h-32 mx-auto rounded-lg bg-white border border-green-200 flex flex-col items-center justify-center p-2">
                            <span class="text-2xl mb-1">📄</span>
                            <span class="text-gray-600 text-xs truncate w-full px-2">{{ rectoFile.name }}</span>
                        </div>
                    </div>
                    <div v-else class="py-4">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 font-bold">Recto de la CNI</p>
                        <p class="text-xs text-gray-400">JPG, PNG ou PDF (Max 5MB)</p>
                    </div>
                </label>

                <button
                    v-if="rectoFile"
                    type="button"
                    @click.stop="removeFile('recto')"
                    class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium transition"
                >
                    Retirer le fichier
                </button>
            </div>

            <div class="border-2 border-dashed rounded-xl p-4 text-center transition-all"
                 :class="{
                     'border-green-400 bg-green-50': versoFile,
                     'border-gray-300 hover:border-blue-400': !versoFile
                 }">
                <input
                    type="file"
                    @change="(e) => handleFileSelect(e, 'verso')"
                    accept="image/jpeg,image/jpg,image/png,application/pdf"
                    class="hidden"
                    id="cni-verso"
                />

                <label for="cni-verso" class="cursor-pointer block">
                    <div v-if="versoPreview" class="mb-3">
                        <img :src="versoPreview" alt="CNI Verso" class="h-32 mx-auto rounded-lg object-cover shadow-sm" />
                    </div>
                    <div v-else-if="versoFile" class="mb-3">
                        <div class="h-32 mx-auto rounded-lg bg-white border border-green-200 flex flex-col items-center justify-center p-2">
                            <span class="text-2xl mb-1">📄</span>
                            <span class="text-gray-600 text-xs truncate w-full px-2">{{ versoFile.name }}</span>
                        </div>
                    </div>
                    <div v-else class="py-4">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 font-bold">Verso de la CNI</p>
                        <p class="text-xs text-gray-400">JPG, PNG ou PDF (Max 5MB)</p>
                    </div>
                </label>

                <button
                    v-if="versoFile"
                    type="button"
                    @click.stop="removeFile('verso')"
                    class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium transition"
                >
                    Retirer le fichier
                </button>
            </div>
        </div>

        <div v-if="rectoFile && versoFile" class="flex items-center text-green-600 text-xs font-bold justify-center bg-green-50 py-2 rounded-lg border border-green-100">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
            Documents prêts pour l'envoi
        </div>
    </div>
</template>
