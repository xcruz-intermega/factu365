<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();
</script>

<template>
    <div style="position: absolute; bottom: 16px; left: 30px; right: 30px; font-size: 6pt; color: #d1d5db; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 4px;">
        <template v-if="!options.content || options.content === 'company'">
            {{ sampleData.company.legal_name }} &middot; NIF: {{ sampleData.company.nif }}
            <template v-if="sampleData.company.website"> &middot; {{ sampleData.company.website }}</template>
        </template>
        <template v-else>
            {{ options.content }}
        </template>
    </div>
</template>
