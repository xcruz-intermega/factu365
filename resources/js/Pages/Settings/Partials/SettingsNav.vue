<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    current: string;
}>();

const tabs = [
    { key: 'company', label: 'Empresa', route: 'settings.company' },
    { key: 'series', label: 'Series', route: 'settings.series' },
    { key: 'product-families', label: 'Familias', route: 'settings.product-families' },
    { key: 'payment-templates', label: 'Vencimientos', route: 'settings.payment-templates' },
    { key: 'certificates', label: 'Certificados', route: 'settings.certificates' },
    { key: 'pdf-templates', label: 'Plantillas PDF', route: 'settings.pdf-templates' },
    { key: 'users', label: 'Usuarios', route: 'settings.users' },
];
</script>

<template>
    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        <Link
            v-for="tab in tabs"
            :key="tab.key"
            :href="route(tab.route)"
            class="rounded-md px-3 py-1.5 text-sm font-medium"
            :class="current === tab.key ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
        >
            {{ tab.label }}
        </Link>
    </div>
</template>
