<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';
import ReportToolbar from '@/Components/ReportToolbar.vue';

interface AuditLogEntry {
    id: number;
    subject_type: string;
    subject_id: number;
    action: string;
    old_values: Record<string, any> | null;
    new_values: Record<string, any> | null;
    user_name: string | null;
    user_email: string | null;
    ip_address: string | null;
    summary: string | null;
    created_at: string;
    model_label: string;
    action_label: string;
    short_model: string;
}

interface PaginatedData {
    data: AuditLogEntry[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Option {
    value: string | number;
    label: string;
}

const props = defineProps<{
    logs: PaginatedData;
    filters: {
        search: string;
        action: string;
        model: string;
        user_id: string;
        from: string;
        to: string;
    };
    actionOptions: Option[];
    modelOptions: Option[];
    userOptions: Option[];
}>();

const search = ref(props.filters.search);
const action = ref(props.filters.action);
const model = ref(props.filters.model);
const userId = ref(props.filters.user_id);
const from = ref(props.filters.from);
const to = ref(props.filters.to);
const expandedId = ref<number | null>(null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(route('settings.audit-logs'), {
        search: search.value || undefined,
        action: action.value || undefined,
        model: model.value || undefined,
        user_id: userId.value || undefined,
        from: from.value || undefined,
        to: to.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    action.value = '';
    model.value = '';
    userId.value = '';
    from.value = '';
    to.value = '';
    applyFilters();
};

watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([action, model, userId, from, to], applyFilters);

const toggleExpand = (id: number) => {
    expandedId.value = expandedId.value === id ? null : id;
};

const diffFields = (log: AuditLogEntry): string[] => {
    const fields = new Set<string>();
    if (log.old_values) Object.keys(log.old_values).forEach(k => fields.add(k));
    if (log.new_values) Object.keys(log.new_values).forEach(k => fields.add(k));
    return Array.from(fields).sort();
};

const formatValue = (val: any): string => {
    if (val === null || val === undefined) return trans('audit.null_value');
    if (val === '[REDACTED]') return trans('audit.redacted');
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
};

const actionBadgeClass = (actionKey: string): string => {
    const map: Record<string, string> = {
        created: 'bg-green-100 text-green-800',
        updated: 'bg-blue-100 text-blue-800',
        deleted: 'bg-red-100 text-red-800',
        finalized: 'bg-indigo-100 text-indigo-800',
        sent_to_aeat: 'bg-purple-100 text-purple-800',
        marked_paid: 'bg-emerald-100 text-emerald-800',
        status_changed: 'bg-yellow-100 text-yellow-800',
        converted: 'bg-cyan-100 text-cyan-800',
        restored: 'bg-orange-100 text-orange-800',
    };
    return map[actionKey] || 'bg-gray-100 text-gray-800';
};

const csvUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (action.value) params.set('action', action.value);
    if (model.value) params.set('model', model.value);
    if (userId.value) params.set('user_id', userId.value);
    if (from.value) params.set('from', from.value);
    if (to.value) params.set('to', to.value);
    const qs = params.toString();
    return route('settings.audit-logs.export-csv') + (qs ? '?' + qs : '');
});

const hasActiveFilters = computed(() => {
    return search.value || action.value || model.value || userId.value || from.value || to.value;
});
</script>

<template>
    <Head :title="$t('audit.title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('audit.title') }}</h1>
            <div class="ml-4 flex items-center gap-2">
                <ReportToolbar :csvUrl="csvUrl" :showPrint="true" />
            </div>
        </template>

        <SettingsNav current="audit-logs" />

        <p class="mb-4 text-sm text-gray-500">{{ $t('audit.description') }}</p>

        <!-- Filters -->
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 print:hidden">
            <input
                v-model="search"
                type="text"
                :placeholder="$t('audit.filter_search')"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <select
                v-model="action"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">{{ $t('audit.filter_all_actions') }}</option>
                <option v-for="opt in actionOptions" :key="opt.value" :value="opt.value">
                    {{ $t(opt.label) }}
                </option>
            </select>
            <select
                v-model="model"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">{{ $t('audit.filter_all_models') }}</option>
                <option v-for="opt in modelOptions" :key="opt.value" :value="opt.value">
                    {{ $t(opt.label) }}
                </option>
            </select>
            <select
                v-model="userId"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">{{ $t('audit.filter_all_users') }}</option>
                <option v-for="opt in userOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </select>
            <input
                v-model="from"
                type="date"
                :title="$t('audit.filter_from')"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <div class="flex gap-2">
                <input
                    v-model="to"
                    type="date"
                    :title="$t('audit.filter_to')"
                    class="min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="shrink-0 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs text-gray-600 hover:bg-gray-50"
                    :title="$t('audit.filter_clear')"
                >
                    &times;
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ $t('audit.col_date') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ $t('audit.col_user') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ $t('audit.col_action') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ $t('audit.col_model') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ $t('audit.col_entity_id') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ $t('audit.col_summary') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-if="logs.data.length === 0">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                {{ $t('audit.no_logs') }}
                            </td>
                        </tr>
                    </template>
                    <template v-for="log in logs.data" :key="log.id">
                        <!-- Main row -->
                        <tr
                            class="cursor-pointer hover:bg-gray-50"
                            @click="toggleExpand(log.id)"
                        >
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ log.created_at }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ log.user_name || $t('audit.system') }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="actionBadgeClass(log.action)"
                                >
                                    {{ $t(log.action_label) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $t(log.model_label) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ log.subject_id }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-gray-600">{{ log.summary }}</td>
                        </tr>
                        <!-- Expanded detail row -->
                        <tr v-if="expandedId === log.id">
                            <td colspan="6" class="bg-gray-50 px-4 py-4">
                                <div class="mb-2 flex items-center gap-4 text-xs text-gray-500">
                                    <span v-if="log.ip_address">{{ $t('audit.col_ip') }}: {{ log.ip_address }}</span>
                                    <span v-if="log.user_email">{{ log.user_email }}</span>
                                </div>
                                <table v-if="log.old_values || log.new_values" class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">{{ $t('audit.col_field') }}</th>
                                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">{{ $t('audit.col_old_value') }}</th>
                                            <th class="px-2 py-1.5 text-left font-medium text-gray-500">{{ $t('audit.col_new_value') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="field in diffFields(log)" :key="field" class="border-b border-gray-100">
                                            <td class="px-2 py-1.5 font-mono text-gray-700">{{ field }}</td>
                                            <td class="px-2 py-1.5">
                                                <span
                                                    v-if="log.old_values && field in log.old_values"
                                                    class="rounded bg-red-50 px-1 text-red-700"
                                                >
                                                    {{ formatValue(log.old_values[field]) }}
                                                </span>
                                                <span v-else class="text-gray-300">—</span>
                                            </td>
                                            <td class="px-2 py-1.5">
                                                <span
                                                    v-if="log.new_values && field in log.new_values"
                                                    class="rounded bg-green-50 px-1 text-green-700"
                                                >
                                                    {{ formatValue(log.new_values[field]) }}
                                                </span>
                                                <span v-else class="text-gray-300">—</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p v-else class="text-xs text-gray-400">{{ $t('audit.no_logs') }}</p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav v-if="logs.last_page > 1" class="mt-4 flex items-center justify-between print:hidden">
            <p class="text-sm text-gray-500">
                {{ logs.total }} {{ logs.total === 1 ? 'registro' : 'registros' }}
            </p>
            <div class="flex gap-1">
                <template v-for="link in logs.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.get(link.url)"
                        class="rounded-md border px-3 py-1.5 text-sm"
                        :class="link.active ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="rounded-md border border-gray-200 px-3 py-1.5 text-sm text-gray-300"
                        v-html="link.label"
                    />
                </template>
            </div>
        </nav>
    </AppLayout>
</template>
