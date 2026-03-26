<!-- resources/js/Components/AdministrationCard.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    administration: {
        type: Object,
        required: true
    }
});

const isAdminAvailable = computed(() => {
    return props.administration.status === 'active';
});
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm border-2 border-gray-100 p-6 hover:shadow-lg transition-all duration-300 cursor-pointer group h-full flex flex-col"
        :class="{
            'opacity-75': !isAdminAvailable,
            'hover:border-primary-300': isAdminAvailable,
            'hover:-translate-y-1': isAdminAvailable
        }"
    >
        <template v-if="isAdminAvailable">
            <Link
                :href="route('administration-show', administration.key)"
                class="flex flex-col items-center text-center flex-1"
            >
                <!-- Logo Container -->
                <div class="h-20 w-20 mb-4 rounded-xl bg-gray-50 flex items-center justify-center group-hover:bg-primary-50 transition-colors duration-300 border border-gray-200 group-hover:border-primary-200">
                    <img
                        v-if="administration.logo"
                        :src="administration.logo_url"
                        :alt="administration.name"
                        class="h-14 w-14 object-contain"
                        @error="e => {
                            e.target.style.display = 'none';
                            e.target.nextElementSibling.style.display = 'flex';
                        }"
                    />
                    <div
                        class="h-14 w-14 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center text-white font-bold text-2xl shadow-md"
                        style="display: none;"
                    >
                        {{ administration.name.charAt(0) }}
                    </div>
                </div>

                <!-- Title -->
                <h3 class="font-bold text-gray-900 mb-2 text-lg group-hover:text-primary-600 transition-colors">
                    {{ administration.name }}
                </h3>

                <!-- Subtitle -->
                <p class="text-sm text-gray-500 mb-3">
                    {{ administration.description }}
                </p>

                <!-- Badge Disponible -->
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Disponible
                </span>
            </Link>
        </template>

        <template v-else>
            <!-- Version Non Disponible -->
            <div class="flex flex-col items-center text-center flex-1">
                <!-- Logo Container -->
                <div class="h-20 w-20 mb-4 rounded-xl bg-gray-50 flex items-center justify-center border border-gray-200">
                    <img
                        v-if="administration.logo"
                        :src="administration.logo_url"
                        :alt="administration.name"
                        class="h-14 w-14 object-contain grayscale opacity-60"
                        @error="e => {
                            e.target.style.display = 'none';
                            e.target.nextElementSibling.style.display = 'flex';
                        }"
                    />
                    <div
                        class="h-14 w-14 bg-gradient-to-br from-gray-300 to-gray-400 rounded-lg flex items-center justify-center text-white font-bold text-2xl"
                        style="display: none;"
                    >
                        {{ administration.name.charAt(0) }}
                    </div>
                </div>

                <!-- Title -->
                <h3 class="font-bold text-gray-900 mb-2 text-lg">
                    {{ administration.name }}
                </h3>

                <!-- Subtitle -->
                <p class="text-sm text-gray-500 mb-3">
                    {{ administration.description }}
                </p>

                <!-- Badge Bientôt disponible -->
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    Bientôt disponible
                </span>
            </div>
        </template>
    </div>
</template>

<style scoped>
.text-primary-600 {
    color: #047857;
}
.bg-primary-50 {
    background-color: #d1fae5;
}
.border-primary-200 {
    border-color: #6ee7b7;
}
.border-primary-300 {
    border-color: #34d399;
}
.from-primary-400 {
    --tw-gradient-from: #34d399;
}
.to-primary-600 {
    --tw-gradient-to: #047857;
}
.hover\:border-primary-300:hover {
    border-color: #34d399;
}
.hover\:bg-primary-50:hover {
    background-color: #d1fae5;
}
.hover\:text-primary-600:hover {
    color: #047857;
}
.hover\:-translate-y-1:hover {
    transform: translateY(-0.25rem);
}
</style>
