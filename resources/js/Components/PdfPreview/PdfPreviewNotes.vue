<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();
</script>

<template>
    <div v-if="sampleData.document.notes" style="background: #f9fafb; padding: 8px; border-radius: 4px; font-size: 7pt; color: #6b7280; margin-bottom: 12px;">
        <div style="font-weight: bold; color: #374151; margin-bottom: 2px;">Observaciones</div>
        {{ sampleData.document.notes }}
    </div>
</template>
