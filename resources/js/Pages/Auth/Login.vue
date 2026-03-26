<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    phone: '',
    password: '',
    remember: false
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password')
    });
};
</script>

<template>
    <GuestLayout>
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-primary-500">Bienvenue</h2>
            <p class="text-gray-500 text-sm mt-1">La Super App de La Poste Côte d'Ivoire</p>
        </div>

        <form @submit.prevent="submit">
            <Input
                v-model="form.phone"
                label="Numéro de téléphone"
                type="tel"
                placeholder="07 00 00 00 00"
                :error="form.errors.phone"
                :required="true"
                class="mb-4"
            />

            <Input
                v-model="form.password"
                label="Mot de passe"
                type="password"
                placeholder="Votre mot de passe"
                :error="form.errors.password"
                required
                class="mb-4"
            />

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                    <span class="ml-2 text-sm text-gray-600">Rester connecté</span>
                </label>
                <Link :href="route('password.request')" class="text-sm text-primary-600 hover:text-primary-700">
                    Mot de passe oublié ?
                </Link>
            </div>

            <Button type="submit" :loading="form.processing" fullWidth>
                Se connecter
            </Button>

            <p class="text-center mt-6 text-sm text-gray-600">
                Pas encore de compte ?
                <Link :href="route('register')" class="text-primary-600 hover:text-primary-700 font-medium">
                    Créer un compte
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
