<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ service.name }}
                    </h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    {{ service.description }}
                </p>
            </div>

            <!-- Content -->
            <div class="p-6">
                <p class="text-gray-700 mb-4">Vous êtes :</p>
                <div class="space-y-3">
                    <button
                        @click="selectType('individual')"
                        class="w-full p-4 border-2 rounded-xl hover:border-primary-500 transition-all text-left group"
                        :class="{'border-primary-500 bg-primary-50': selectedType === 'individual'}"
                    >
                        <div class="flex items-center gap-3">
                            <UserIcon class="w-8 h-8 text-gray-400 group-hover:text-primary-500" />
                            <div>
                                <p class="font-semibold text-gray-900">Particulier</p>
                                <p class="text-sm text-gray-500">Je demande ce service à titre personnel</p>
                            </div>
                        </div>
                    </button>

                    <button
                        @click="selectType('business')"
                        class="w-full p-4 border-2 rounded-xl hover:border-primary-500 transition-all text-left group"
                        :class="{'border-primary-500 bg-primary-50': selectedType === 'business'}"
                    >
                        <div class="flex items-center gap-3">
                            <BuildingOfficeIcon class="w-8 h-8 text-gray-400 group-hover:text-primary-500" />
                            <div>
                                <p class="font-semibold text-gray-900">Entreprise</p>
                                <p class="text-sm text-gray-500">Je demande ce service pour mon entreprise</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 flex gap-3">
                <button
                    @click="$emit('close')"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Annuler
                </button>
                <button
                    @click="confirm"
                    :disabled="!selectedType"
                    class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Continuer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { UserIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    service: {
        type: Object,
        required: true
    }
});

const selectedType = ref(null);

const selectType = (type) => {
    selectedType.value = type;
};

const confirm = () => {
    if (selectedType.value) {
        emit('confirm', selectedType.value);
    }
};

const emit = defineEmits(['confirm', 'close']);
</script>
