<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-50 pt-6 sm:justify-center sm:pt-0">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-16 w-16 fill-current text-primary-500" />
            </Link>
        </div>

        <div class="mt-6 w-full overflow-hidden bg-white px-6 py-8 shadow-medium sm:max-w-md sm:rounded-xl border border-gray-200">
            <slot />
        </div>

        <!-- Badges de sécurité -->
        <div class="mt-6 flex gap-4">
            <img src="/images/anssi-badge.png" alt="ANSSI" class="h-10 opacity-75">
            <img src="/images/artci-badge.png" alt="ARTCI" class="h-10 opacity-75">
        </div>
    </div>
</template>
