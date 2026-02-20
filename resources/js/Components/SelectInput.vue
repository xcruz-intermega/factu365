<script setup lang="ts">
import { ref, onMounted } from 'vue';

defineProps<{
    modelValue?: string | number;
    options: Array<{ value: string | number; label: string }>;
}>();

defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const input = ref<HTMLSelectElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <select
        ref="input"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        :value="modelValue"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
        <option v-for="opt in options" :key="opt.value" :value="opt.value">
            {{ opt.label }}
        </option>
    </select>
</template>
