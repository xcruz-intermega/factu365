<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    color?: 'gray' | 'green' | 'blue' | 'yellow' | 'red' | 'indigo' | 'purple';
}>(), {
    color: 'gray',
});

const colorClasses = computed(() => {
    const map: Record<string, string> = {
        gray: 'bg-gray-100 text-gray-700',
        green: 'bg-green-100 text-green-700',
        blue: 'bg-blue-100 text-blue-700',
        yellow: 'bg-yellow-100 text-yellow-700',
        red: 'bg-red-100 text-red-700',
        indigo: 'bg-indigo-100 text-indigo-700',
        purple: 'bg-purple-100 text-purple-700',
    };
    return map[props.color] || map.gray;
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
        :class="colorClasses"
    >
        <slot />
    </span>
</template>
