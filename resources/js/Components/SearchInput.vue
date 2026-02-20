<script setup lang="ts">
import { ref, watch } from 'vue';

const props = withDefaults(defineProps<{
    modelValue?: string;
    placeholder?: string;
    debounce?: number;
}>(), {
    modelValue: '',
    placeholder: 'Buscar...',
    debounce: 300,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const localValue = ref(props.modelValue);
let timeout: ReturnType<typeof setTimeout>;

watch(() => props.modelValue, (val) => {
    localValue.value = val;
});

const onInput = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        emit('update:modelValue', localValue.value);
    }, props.debounce);
};

const clear = () => {
    localValue.value = '';
    emit('update:modelValue', '');
};
</script>

<template>
    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
        </div>
        <input
            type="text"
            v-model="localValue"
            @input="onInput"
            :placeholder="placeholder"
            class="block w-full rounded-md border-0 py-2 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
        />
        <button
            v-if="localValue"
            @click="clear"
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3"
        >
            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>
