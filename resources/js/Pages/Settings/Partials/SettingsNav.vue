<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

defineProps<{
    current: string;
}>();

interface Tab {
    key: string;
    label: string;
    route: string;
}

interface Section {
    label: string;
    tabs: Tab[];
}

const sections = computed<Section[]>(() => [
    {
        label: trans('settings.section_billing'),
        tabs: [
            { key: 'series', label: trans('settings.tab_series'), route: 'settings.series' },
            { key: 'payment-templates', label: trans('settings.tab_payment_templates'), route: 'settings.payment-templates' },
            { key: 'pdf-templates', label: trans('settings.tab_pdf_templates'), route: 'settings.pdf-templates' },
        ],
    },
    {
        label: trans('settings.section_company'),
        tabs: [
            { key: 'company', label: trans('settings.tab_company'), route: 'settings.company' },
            { key: 'certificates', label: trans('settings.tab_certificates'), route: 'settings.certificates' },
            { key: 'users', label: trans('settings.tab_users'), route: 'settings.users' },
            { key: 'backups', label: trans('settings.tab_backups'), route: 'settings.backups' },
            { key: 'audit-logs', label: trans('settings.tab_audit_logs'), route: 'settings.audit-logs' },
        ],
    },
]);
</script>

<template>
    <div class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 border-b border-gray-200 pb-3">
        <template v-for="(section, i) in sections" :key="section.label">
            <span v-if="i > 0" class="mx-1 h-4 w-px bg-gray-300" />
            <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ section.label }}</span>
            <Link
                v-for="tab in section.tabs"
                :key="tab.key"
                :href="route(tab.route)"
                class="rounded-md px-3 py-1.5 text-sm font-medium"
                :class="current === tab.key ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
            >
                {{ tab.label }}
            </Link>
        </template>
    </div>
</template>
