<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();
</script>

<template>
    <div style="font-size: 6pt; color: #9ca3af; margin-top: 8px; line-height: 1.4;">
        Factura generada por sistema informático conforme al Reglamento VERI*FACTU (RD 1007/2023).
        Software: {{ sampleData.company.software_name }} v{{ sampleData.company.software_version }}.
    </div>
</template>
