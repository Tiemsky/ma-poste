<script setup>
import { ref } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Votre agence postale désormais dans votre téléphone'
    },
    subtitle: {
        type: String,
        default: 'Super App MaPoste'
    }
});
</script>

<template>
    <div class="relative bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700 overflow-hidden">
        <!-- Image de fond avec overlay -->
        <div class="absolute inset-0">
            <img
                src="/images/hero-bg.jpg"
                alt="Background"
                class="w-full h-full object-cover opacity-20"
            />
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/20"></div>
        </div>

        <!-- Contenu -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                    {{ title }}
                </h1>
                <p class="text-xl md:text-2xl text-primary-100 mb-8">
                    {{ subtitle }}
                </p>
            </div>
        </div>

        <!-- Forme décorative en bas -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
            </svg>
        </div>
    </div>
</template>

<style scoped>
.from-primary-900 {
    --tw-gradient-from: #064e3b;
}
.via-primary-800 {
    --tw-gradient-stops: var(--tw-gradient-from), #065f46, var(--tw-gradient-to, rgb(6 78 59 / 0));
}
.to-primary-700 {
    --tw-gradient-to: #047857;
}
</style>
