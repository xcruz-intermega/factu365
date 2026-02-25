<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

defineProps<{
    current: string;
}>();

const tabs = computed(() => [
    { key: 'company', label: trans('settings.tab_company'), route: 'settings.company' },
    { key: 'series', label: trans('settings.tab_series'), route: 'settings.series' },
    { key: 'payment-templates', label: trans('settings.tab_payment_templates'), route: 'settings.payment-templates' },
    { key: 'certificates', label: trans('settings.tab_certificates'), route: 'settings.certificates' },
    { key: 'pdf-templates', label: trans('settings.tab_pdf_templates'), route: 'settings.pdf-templates' },
    { key: 'users', label: trans('settings.tab_users'), route: 'settings.users' },
    { key: 'backups', label: trans('settings.tab_backups'), route: 'settings.backups' },
]);
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
