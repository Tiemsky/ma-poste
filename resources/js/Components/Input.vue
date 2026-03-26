<template>
    <div>
        <label v-if="label" :for="id" class="block font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :class="[
                'w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 transition-all duration-200',
                error
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
                    : 'border-gray-300 focus:border-primary-500 focus:ring-primary-200',
                disabled && 'bg-gray-100 cursor-not-allowed'
            ]"
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur')"
        />
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
        <p v-if="hint" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
import { useId } from 'vue'

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: 'text'
    },
    placeholder: String,
    disabled: Boolean,
    error: String,
    hint: String,
    required: Boolean,
    id: {
        type: String,
        default: () => `input-${useId()}`
    }
})

defineEmits(['update:modelValue', 'blur'])
</script>
