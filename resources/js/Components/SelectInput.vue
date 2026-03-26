<template>
    <div>
        <label v-if="label" :for="id" class="block font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            :class="[
                'w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 transition-all duration-200 appearance-none bg-white',
                error
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
                    : 'border-gray-300 focus:border-primary-500 focus:ring-primary-200',
                disabled && 'bg-gray-100 cursor-not-allowed'
            ]"
            @change="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur')"
        >
            <option v-if="placeholder" value="" disabled selected>
                {{ placeholder }}
            </option>

            <slot>
                <option
                    v-for="option in options"
                    :key="option.value ?? option"
                    :value="option.value ?? option"
                >
                    {{ option.label ?? option }}
                </option>
            </slot>
        </select>

        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
        <p v-if="hint" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
import { useId } from 'vue'

const props = defineProps({
    modelValue: [String, Number, Boolean],
    options: {
        type: Array,
        default: () => []
    },
    label: String,
    placeholder: String,
    disabled: Boolean,
    error: String,
    hint: String,
    required: Boolean,
    id: {
        type: String,
        default: () => `select-${useId()}`
    }
})

defineEmits(['update:modelValue', 'blur'])
</script>

<style scoped>
/* Optionnel : Ajoute une petite flèche personnalisée si vous voulez masquer celle par défaut
   car 'appearance-none' la retire sur certains navigateurs */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>
