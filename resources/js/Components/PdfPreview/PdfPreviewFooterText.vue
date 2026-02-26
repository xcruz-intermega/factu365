<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();
</script>

<template>
    <div v-if="sampleData.document.footer_text" style="background: #f9fafb; padding: 8px; border-radius: 4px; font-size: 7pt; color: #6b7280; margin-bottom: 12px;">
        {{ sampleData.document.footer_text }}
    </div>
</template>
