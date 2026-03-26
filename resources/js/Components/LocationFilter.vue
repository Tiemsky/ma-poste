<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const emit = defineEmits(['filters-updated']);

// Données
const countries = ref([]);
const districts = ref([]);
const communes = ref([]);

// Sélections
const selectedCountry = ref(null);
const selectedDistrict = ref(null);
const selectedCommune = ref(null);

// Recherche
const countrySearch = ref('');
const districtSearch = ref('');
const communeSearch = ref('');

// Dropdowns
const showCountryDropdown = ref(false);
const showDistrictDropdown = ref(false);
const showCommuneDropdown = ref(false);

// Filtrer les résultats
const filteredCountries = computed(() => {
    if (!countrySearch.value) return countries.value;
    return countries.value.filter(c =>
        c.name.toLowerCase().includes(countrySearch.value.toLowerCase()) ||
        c.code.toLowerCase().includes(countrySearch.value.toLowerCase())
    );
});

const filteredDistricts = computed(() => {
    if (!districtSearch.value) return districts.value;
    return districts.value.filter(d =>
        d.name.toLowerCase().includes(districtSearch.value.toLowerCase())
    );
});

const filteredCommunes = computed(() => {
    if (!communeSearch.value) return communes.value;
    return communes.value.filter(c =>
        c.name.toLowerCase().includes(communeSearch.value.toLowerCase()) ||
        (c.postal_code && c.postal_code.includes(communeSearch.value))
    );
});

// Charger les pays au montage
onMounted(async () => {
    try {
        const response = await fetch('/api/countries');
        const data = await response.json();
        countries.value = data.data || data;
    } catch (error) {
        console.error('Erreur chargement pays:', error);
        // Données de fallback
        countries.value = [
            { id: 1, name: 'Côte d\'Ivoire', code: 'CI' },
            { id: 2, name: 'France', code: 'FR' },
            { id: 3, name: 'Sénégal', code: 'SN' }
        ];
    }
});

// Quand un pays est sélectionné, charger les districts
watch(selectedCountry, async (country) => {
    if (country) {
        try {
            const response = await fetch(`/api/countries/${country.id}/districts`);
            const data = await response.json();
            districts.value = data.data || data;
        } catch (error) {
            console.error('Erreur chargement districts:', error);
            // Données de fallback
            districts.value = [
                { id: 1, country_id: 1, name: 'District Abidjan' },
                { id: 2, country_id: 1, name: 'Bas-Sassandra' },
                { id: 3, country_id: 1, name: 'Comoé' }
            ];
        }
    } else {
        districts.value = [];
    }
    selectedDistrict.value = null;
    communes.value = [];
    selectedCommune.value = null;
});

// Quand un district est sélectionné, charger les communes
watch(selectedDistrict, async (district) => {
    if (district) {
        try {
            const response = await fetch(`/api/districts/${district.id}/communes`);
            const data = await response.json();
            communes.value = data.data || data;
        } catch (error) {
            console.error('Erreur chargement communes:', error);
            // Données de fallback
            communes.value = [
                { id: 1, district_id: 1, name: 'Marcory', postal_code: '01 BP 123' },
                { id: 2, district_id: 1, name: 'Cocody', postal_code: '01 BP 456' },
                { id: 3, district_id: 1, name: 'Plateau', postal_code: '01 BP 789' },
                { id: 4, district_id: 1, name: 'Yopougon', postal_code: '01 BP 321' }
            ];
        }
    } else {
        communes.value = [];
    }
    selectedCommune.value = null;
});

// Fermer les dropdowns quand on clique ailleurs
onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.country-dropdown')) showCountryDropdown.value = false;
        if (!e.target.closest('.district-dropdown')) showDistrictDropdown.value = false;
        if (!e.target.closest('.commune-dropdown')) showCommuneDropdown.value = false;
    });
});

// Mettre à jour les filtres
const updateFilters = () => {
    emit('filters-updated', {
        country: selectedCountry.value,
        district: selectedDistrict.value,
        commune: selectedCommune.value
    });
};

const clearFilters = () => {
    selectedCountry.value = null;
    selectedDistrict.value = null;
    selectedCommune.value = null;
    countrySearch.value = '';
    districtSearch.value = '';
    communeSearch.value = '';
    districts.value = [];
    communes.value = [];
    updateFilters();
};
</script>

<template>
    <div class="bg-white shadow-lg rounded-2xl -mt-8 mx-4 md:mx-8 relative z-20">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Sélection Pays -->
                <div class="relative country-dropdown">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pays <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="countrySearch"
                            @focus="showCountryDropdown = true"
                            @input="showCountryDropdown = true"
                            type="text"
                            placeholder="Rechercher un pays..."
                            class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            :class="{ 'border-primary-500': selectedCountry }"
                        />
                        <svg
                            v-if="selectedCountry"
                            @click="selectedCountry = null; countrySearch = ''"
                            class="absolute right-10 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 hover:text-gray-600 cursor-pointer"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <svg
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Dropdown Pays -->
                    <div
                        v-show="showCountryDropdown && filteredCountries.length > 0"
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                    >
                        <div
                            v-for="country in filteredCountries"
                            :key="country.id"
                            @click="selectedCountry = country; countrySearch = country.name; showCountryDropdown = false"
                            class="px-4 py-2 hover:bg-primary-50 cursor-pointer transition-colors"
                            :class="{ 'bg-primary-100': selectedCountry?.id === country.id }"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-900">{{ country.name }}</span>
                                <span class="text-sm text-gray-500">{{ country.code }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sélection District -->
                <div class="relative district-dropdown">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        District / Région <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="districtSearch"
                            @focus="showDistrictDropdown = true"
                            @input="showDistrictDropdown = true"
                            type="text"
                            placeholder="Rechercher un district..."
                            :disabled="!selectedCountry"
                            class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all disabled:bg-gray-100 disabled:cursor-not-allowed"
                            :class="{ 'border-primary-500': selectedDistrict }"
                        />
                        <svg
                            v-if="selectedDistrict"
                            @click="selectedDistrict = null; districtSearch = ''"
                            class="absolute right-10 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 hover:text-gray-600 cursor-pointer"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <svg
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Dropdown District -->
                    <div
                        v-show="showDistrictDropdown && filteredDistricts.length > 0"
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                    >
                        <div
                            v-for="district in filteredDistricts"
                            :key="district.id"
                            @click="selectedDistrict = district; districtSearch = district.name; showDistrictDropdown = false"
                            class="px-4 py-2 hover:bg-primary-50 cursor-pointer transition-colors"
                            :class="{ 'bg-primary-100': selectedDistrict?.id === district.id }"
                        >
                            <span class="font-medium text-gray-900">{{ district.name }}</span>
                        </div>
                    </div>
                    <div
                        v-show="showDistrictDropdown && filteredDistricts.length === 0 && selectedCountry"
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-gray-500"
                    >
                        Aucun district trouvé
                    </div>
                </div>

                <!-- Sélection Commune -->
                <div class="relative commune-dropdown">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Commune <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="communeSearch"
                            @focus="showCommuneDropdown = true"
                            @input="showCommuneDropdown = true"
                            type="text"
                            placeholder="Rechercher une commune..."
                            :disabled="!selectedDistrict"
                            class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all disabled:bg-gray-100 disabled:cursor-not-allowed"
                            :class="{ 'border-primary-500': selectedCommune }"
                        />
                        <svg
                            v-if="selectedCommune"
                            @click="selectedCommune = null; communeSearch = ''"
                            class="absolute right-10 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 hover:text-gray-600 cursor-pointer"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <svg
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Dropdown Commune -->
                    <div
                        v-show="showCommuneDropdown && filteredCommunes.length > 0"
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                    >
                        <div
                            v-for="commune in filteredCommunes"
                            :key="commune.id"
                            @click="selectedCommune = commune; communeSearch = `${commune.name} ${commune.postal_code ? '(' + commune.postal_code + ')' : ''}`; showCommuneDropdown = false"
                            class="px-4 py-2 hover:bg-primary-50 cursor-pointer transition-colors"
                            :class="{ 'bg-primary-100': selectedCommune?.id === commune.id }"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-900">{{ commune.name }}</span>
                                <span v-if="commune.postal_code" class="text-sm text-gray-500">{{ commune.postal_code }}</span>
                            </div>
                        </div>
                    </div>
                    <div
                        v-show="showCommuneDropdown && filteredCommunes.length === 0 && selectedDistrict"
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-gray-500"
                    >
                        Aucune commune trouvée
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-4 flex flex-wrap gap-3 justify-end">
                <button
                    @click="clearFilters"
                    class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors"
                >
                    Réinitialiser
                </button>
                <button
                    @click="updateFilters"
                    :disabled="!selectedCountry || !selectedDistrict || !selectedCommune"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
                >
                    Mettre à jour
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-primary-500 {
    color: #059669;
}
.bg-primary-50 {
    background-color: #d1fae5;
}
.bg-primary-100 {
    background-color: #a7f3d0;
}
.bg-primary-600 {
    background-color: #059669;
}
.hover\:bg-primary-700:hover {
    background-color: #047857;
}
.border-primary-500 {
    border-color: #059669;
}
.focus\:ring-primary-500:focus {
    --tw-ring-color: #059669;
}
</style>
