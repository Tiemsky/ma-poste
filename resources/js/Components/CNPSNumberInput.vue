<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        default: 'Numéro CNPS'
    },
    required: {
        type: Boolean,
        default: true
    },
    placeholder: {
        type: String,
        default: 'Ex: 123456789012'
    },
    endpoint: {
        type: String,
        default: '/api/cnps/verify'
    }
});

const emit = defineEmits(['update:modelValue', 'verified', 'error']);

const localValue = ref(props.modelValue);
const verificationStatus = ref('idle'); // idle, waiting, verifying, verified, failed
const verificationMessage = ref('');
const isVerifying = ref(false);
let verificationTimer = null;

const CNPS_LENGTH = 12;
const formattedValue = computed(() => {
    const digits = (localValue.value || '').replace(/\D/g, '');
    // Format: XXX-XXX-XXX-XXX
    return digits.replace(/(\d{3})(?=\d)/g, '$1-');
});

const progressPercent = computed(() => {
    const digits = (localValue.value || '').replace(/\D/g, '').length;
    return Math.min((digits / CNPS_LENGTH) * 100, 100);
});

const isComplete = computed(() => {
    const digits = (localValue.value || '').replace(/\D/g, '');
    return digits.length === CNPS_LENGTH && /^\d+$/.test(digits);
});

watch(() => props.modelValue, (newVal) => {
    localValue.value = newVal;
});

watch(localValue, (newVal) => {
    const digits = newVal.replace(/\D/g, '');

    // Émettre la valeur brute (sans formatage) au parent
    emit('update:modelValue', digits);

    // Gestion de la vérification
    if (verificationTimer) clearTimeout(verificationTimer);

    if (digits.length === CNPS_LENGTH && /^\d+$/.test(digits)) {
        // 12 chiffres atteints → En attente
        verificationStatus.value = 'waiting';
        verificationMessage.value = 'En attente de vérification...';

        // Après 1 seconde, lancer la vérification
        verificationTimer = setTimeout(() => {
            startVerification(digits);
        }, 1000);
    } else if (digits.length > 0) {
        verificationStatus.value = 'typing';
        verificationMessage.value = `${CNPS_LENGTH - digits.length} chiffre${CNPS_LENGTH - digits.length > 1 ? 's' : ''} restant${CNPS_LENGTH - digits.length > 1 ? 's' : ''}`;
    } else {
        verificationStatus.value = 'idle';
        verificationMessage.value = '';
    }
});

const startVerification = async (cnpsNumber) => {
    verificationStatus.value = 'verifying';
    verificationMessage.value = 'Vérification en cours...';
    isVerifying.value = true;

    try {
        // Simulation API - Remplacer par vrai appel
        await new Promise(resolve => setTimeout(resolve, 4000));

        // Mock response - À remplacer par appel API réel
        const isValid = Math.random() > 0.2; // 80% de succès pour démo

        if (isValid) {
            verificationStatus.value = 'verified';
            verificationMessage.value = '✓ Numéro vérifié avec succès';
            emit('verified', { cnpsNumber, valid: true });
        } else {
            verificationStatus.value = 'failed';
            verificationMessage.value = '✗ Numéro non trouvé dans la base CNPS';
            emit('error', { cnpsNumber, valid: false, message: 'Numéro invalide' });
        }
    } catch (error) {
        verificationStatus.value = 'failed';
        verificationMessage.value = 'Erreur de vérification. Réessayez.';
        emit('error', { error: error.message });
    } finally {
        isVerifying.value = false;
    }
};

const resetVerification = () => {
    verificationStatus.value = 'idle';
    verificationMessage.value = '';
    localValue.value = '';
    emit('update:modelValue', '');
};

const statusColors = {
    idle: 'text-gray-500',
    typing: 'text-blue-600',
    waiting: 'text-yellow-600',
    verifying: 'text-purple-600',
    verified: 'text-green-600',
    failed: 'text-red-600'
};

const statusIcons = {
    idle: null,
    typing: null,
    waiting: '⏳',
    verifying: '🔄',
    verified: '✓',
    failed: '✗'
};
</script>

<template>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="relative">
            <input
                :value="formattedValue"
                @input="localValue = $event.target.value.replace(/[^0-9]/g, '').slice(0, CNPS_LENGTH)"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="15"
                :placeholder="placeholder"
                class="w-full pl-4 pr-12 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-mono text-lg tracking-wider"
                :class="{
                    'border-gray-300': verificationStatus === 'idle' || verificationStatus === 'typing',
                    'border-yellow-400 bg-yellow-50': verificationStatus === 'waiting',
                    'border-purple-400 bg-purple-50 animate-pulse': verificationStatus === 'verifying',
                    'border-green-500 bg-green-50': verificationStatus === 'verified',
                    'border-red-500 bg-red-50': verificationStatus === 'failed',
                }"
                :disabled="isVerifying"
            />

            <!-- Progress Bar -->
            <div
                v-if="localValue.replace(/\D/g, '').length > 0 && verificationStatus !== 'verified' && verificationStatus !== 'failed'"
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-200 rounded-b-lg overflow-hidden"
            >
                <div
                    class="h-full bg-primary-500 transition-all duration-300"
                    :style="{ width: `${progressPercent}%` }"
                ></div>
            </div>

            <!-- Status Icon -->
            <div
                v-if="statusIcons[verificationStatus]"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-lg"
            >
                {{ statusIcons[verificationStatus] }}
            </div>
        </div>

        <!-- Status Message -->
        <div
            v-if="verificationMessage"
            class="flex items-center text-sm"
            :class="statusColors[verificationStatus]"
        >
            <span v-if="verificationStatus === 'verifying'" class="animate-spin mr-1">⟳</span>
            {{ verificationMessage }}
        </div>

        <!-- Helper Text -->
        <p class="text-xs text-gray-500">
            Numéro à 12 chiffres. Ce numéro suit le salarié dans toutes ses affiliations CNPS.
        </p>

        <!-- Reset Button -->
        <button
            v-if="verificationStatus === 'verified' || verificationStatus === 'failed'"
            @click="resetVerification"
            class="text-xs text-primary-600 hover:text-primary-700 underline"
        >
            Modifier le numéro
        </button>
    </div>
</template>

<style scoped>
.text-primary-600 { color: #047857; }
.hover\:text-primary-700:hover { color: #065f46; }
.border-primary-500 { border-color: #059669; }
.bg-primary-50 { background-color: #d1fae5; }
</style>
