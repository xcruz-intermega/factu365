<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Badge from '@/Components/Badge.vue';
import { useFormatters } from '@/composables/useFormatters';

const { formatCurrency, formatDate } = useFormatters();

const props = defineProps<{
    documents: any;
    filters: {
        date_from: string;
        date_to: string;
        status: string;
        client_id: string;
    };
    statuses: string[];
    clients: Array<{ id: number; legal_name: string; trade_name: string | null }>;
    totalCount: number;
}>();

const form = ref({
    date_from: props.filters.date_from,
    date_to: props.filters.date_to,
    status: props.filters.status,
    client_id: props.filters.client_id,
});

const columns = computed(() => [
    { key: 'number', label: trans('documents.col_number') },
    { key: 'client_name', label: trans('documents.col_client') },
    { key: 'issue_date', label: trans('documents.col_date') },
    { key: 'total', label: trans('documents.col_total'), class: 'text-right' },
    { key: 'status', label: trans('documents.col_status') },
]);

const statusColors: Record<string, 'gray' | 'green' | 'blue' | 'yellow' | 'red' | 'indigo' | 'purple'> = {
    finalized: 'blue',
    sent: 'indigo',
    paid: 'green',
    partial: 'yellow',
    overdue: 'red',
    cancelled: 'red',
};

const statusLabels = computed<Record<string, string>>(() => ({
    finalized: trans('common.status_finalized'),
    sent: trans('common.status_sent'),
    paid: trans('common.status_paid'),
    partial: trans('common.status_partial'),
    overdue: trans('common.status_overdue'),
    cancelled: trans('common.status_cancelled'),
}));

const rows = computed(() =>
    props.documents.data.map((doc: any) => ({
        ...doc,
        client_name: doc.client?.trade_name || doc.client?.legal_name || '-',
    }))
);

const activeFilters = computed(() => {
    const params: Record<string, string> = {};
    if (form.value.date_from) params.date_from = form.value.date_from;
    if (form.value.date_to) params.date_to = form.value.date_to;
    if (form.value.status) params.status = form.value.status;
    if (form.value.client_id) params.client_id = form.value.client_id;
    return params;
});

const downloadUrl = computed(() =>
    route('documents.export-facturae.download', activeFilters.value)
);

function applyFilters() {
    router.get(route('documents.export-facturae'), activeFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('documents.export_facturae_title') }}
            </h1>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <!-- Subtitle -->
            <p class="text-sm text-gray-500">
                {{ $t('documents.export_facturae_subtitle') }}
            </p>

            <!-- Filters -->
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('documents.export_date_from') }}</label>
                        <input
                            v-model="form.date_from"
                            type="date"
                            class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('documents.export_date_to') }}</label>
                        <input
                            v-model="form.date_to"
                            type="date"
                            class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('documents.col_status') }}</label>
                        <select
                            v-model="form.status"
                            class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">{{ $t('documents.export_all_statuses') }}</option>
                            <option v-for="s in statuses" :key="s" :value="s">
                                {{ statusLabels[s] || s }}
                            </option>
                        </select>
                    </div>
                    <div class="min-w-0 flex-1">
                        <label class="block text-xs font-medium text-gray-700">{{ $t('documents.col_client') }}</label>
                        <select
                            v-model="form.client_id"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">{{ $t('documents.export_all_clients') }}</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">
                                {{ c.trade_name || c.legal_name }}
                            </option>
                        </select>
                    </div>
                    <button
                        @click="applyFilters"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                        {{ $t('documents.export_filter') }}
                    </button>
                </div>
            </div>

            <!-- Results bar -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    {{ $t('documents.export_count', { count: String(totalCount) }) }}
                </p>
                <a
                    v-if="totalCount > 0"
                    :href="downloadUrl"
                    class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5L12 21l-9-4.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9 4.5 9-4.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5l9 4.5 9-4.5L12 3 3 7.5z" />
                    </svg>
                    {{ $t('documents.export_download_zip', { count: String(totalCount) }) }}
                </a>
            </div>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :rows="rows"
                :pagination="documents"
                :empty-message="$t('documents.export_no_results')"
            >
                <template #cell-issue_date="{ value }">
                    {{ formatDate(value) }}
                </template>

                <template #cell-total="{ value }">
                    <span class="font-medium">{{ formatCurrency(value) }}</span>
                </template>

                <template #cell-status="{ value }">
                    <Badge :color="statusColors[value] || 'gray'">
                        {{ statusLabels[value] || value }}
                    </Badge>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
