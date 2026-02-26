<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();
</script>

<template>
    <div
        :style="{
            backgroundColor: options.background_color || global.accent_color || '#4f46e5',
            color: options.text_color || '#ffffff',
            padding: '8px 12px',
            fontSize: '11pt',
            fontWeight: 'bold',
            textTransform: 'uppercase',
            marginBottom: '16px',
        }"
    >
        {{ sampleData.typeLabel }}
        <span style="float: right; font-size: 10pt;">{{ sampleData.document.number }}</span>
    </div>
</template>
