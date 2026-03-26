<script setup>
import { ref, watch } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import SelectInput from '@/Components/SelectInput.vue';
import axios from 'axios';

const props = defineProps({
  countries: Array,
});

const cities = ref([]);
const communes = ref([]);
const isLoadingCities = ref(false);
const isLoadingCommunes = ref(false);

const form = useForm({
  is_company: false,
  last_name: '',
  first_name: '',
  country_id: '',
  city_id: '',
  commune_id: '',
  phone: '',
  password: '',
  password_confirmation: '',
});

// Charger les villes avec loading
const fetchCities = async () => {
  if (!form.country_id) {
    cities.value = [];
    communes.value = [];
    form.city_id = '';
    form.commune_id = '';
    return;
  }

  isLoadingCities.value = true;
  communes.value = [];
  form.city_id = '';
  form.commune_id = '';

  try {
    const response = await axios.get(`countries/${form.country_id}/cities`);
    if (response.data.success) {
      cities.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur chargement villes:', error);
    cities.value = [];
  } finally {
    isLoadingCities.value = false;
  }
};

// Charger les communes avec loading
const fetchCommunes = async () => {
  if (!form.city_id) {
    communes.value = [];
    form.commune_id = '';
    return;
  }

  isLoadingCommunes.value = true;
  form.commune_id = '';

  try {
    const response = await axios.get(`/cities/${form.city_id}/communes`);
    if (response.data.success) {
      communes.value = response.data.data;
    }
  } catch (error) {
    console.error('Erreur chargement communes:', error);
    communes.value = [];
  } finally {
    isLoadingCommunes.value = false;
  }
};

// Observateurs pour la cascade
watch(() => form.country_id, fetchCities);
watch(() => form.city_id, fetchCommunes);

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <GuestLayout>

    <Head title="Inscription" />

    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Créer un compte</h1>
      <p class="text-gray-600">Rejoignez l'équipe des livreurs</p>
    </div>

    <form @submit.prevent="submit" class="space-y-5">

      <SelectInput v-model="form.is_company" label="Êtes-vous une entreprise ?" placeholder="Êtes-vous une entreprise"
        :error="form.errors.is_company" required>
        <option :value="false">Non, je suis un particulier</option>
        <option :value="true">Oui, je suis une entreprise</option>
      </SelectInput>


      <Input v-model="form.last_name" type="text" placeholder="Votre nom" label="Veillez Entrer Votre Nom" required />

      <Input v-model="form.first_name" type="text" placeholder="Prénoms" label="Votre Prenoms" required />



      <SelectInput v-model="form.country_id" @change="fetchCities" label="Pays de résidence"
        placeholder="Sélectionnez votre pays" :error="form.errors.country_id" required>
        <option v-for="country in countries" :key="country.id" :value="country.id">
          {{ country.name }}
        </option>
      </SelectInput>

        <SelectInput  v-model="form.city_id"
                      label="Ville de résidence"
                      placeholder="Sélectionnez votre ville"
                      :error="form.errors.city_id"
                      required
                     :disabled="!form.country_id || isLoadingCities"
                      >
          <option v-for="city in cities" :key="city.id" :value="city.id">
            {{ city.name }}
          </option>
        </SelectInput>


        <SelectInput  v-model="form.commune_id"
                      label="Commune de résidence"
                      placeholder="Sélectionnez votre commune"
                      :error="form.errors.commune_id"

                      :disabled="!form.city_id || isLoadingCommunes"
                      >
          <option v-for="commune in communes" :key="commune.id" :value="commune.id">
            {{ commune.name }}
          </option>
        </SelectInput>


      <Input v-model="form.phone" type="tel" placeholder="07 00 00 00 00" required label="Téléphone" />


      <label class="block font-medium text-gray-700 mb-1"></label>
      <Input v-model="form.password" label="Mot de passe " type="password" placeholder="••••••••" required />

      <Input v-model="form.password_confirmation" type="password" placeholder="••••••••" required
        label="Confirmation" />

      <Button type="submit" :loading="form.processing" fullWidth>
        {{ form.processing ? 'Inscription...' : "S'inscrire" }}
      </Button>

      <p class="text-center mt-6 text-sm text-gray-600">
        Avez-deja un compte ?
        <Link :href="route('login')" class="text-primary-600 hover:text-primary-700 font-medium">
          Se Connecter
        </Link>
      </p>

    </form>
  </GuestLayout>
</template>



