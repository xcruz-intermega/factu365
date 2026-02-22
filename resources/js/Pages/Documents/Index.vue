<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import type { Column } from '@/Components/DataTable.vue';

const props = defineProps<{
    documents: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: any[];
    };
    documentType: string;
    documentTypeLabel: string;
    filters: {
        search?: string;
        status?: string;
        sort?: string;
        dir?: string;
    };
    statuses: Array<{ value: string; label: string }>;
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const sortBy = ref(props.filters.sort || '');
const sortDir = ref<'asc' | 'desc'>((props.filters.dir as 'asc' | 'desc') || 'desc');

const columns = computed<Column[]>(() => [
    { key: 'number', label: trans('documents.col_number'), sortable: true },
    { key: 'client', label: trans('documents.col_client') },
    { key: 'issue_date', label: trans('documents.col_date'), sortable: true },
    { key: 'total', label: trans('documents.col_total'), sortable: true, class: 'text-right' },
    { key: 'status', label: trans('documents.col_status'), sortable: true },
]);

const statusColors: Record<string, 'gray' | 'green' | 'blue' | 'yellow' | 'red' | 'indigo' | 'purple'> = {
    draft: 'gray',
    finalized: 'blue',
    sent: 'indigo',
    paid: 'green',
    partial: 'yellow',
    overdue: 'red',
    cancelled: 'red',
    registered: 'blue',
    created: 'blue',
    accepted: 'green',
    rejected: 'red',
    converted: 'purple',
};

const statusLabels = computed<Record<string, string>>(() => ({
    draft: trans('common.status_draft'),
    finalized: trans('common.status_finalized'),
    sent: trans('common.status_sent'),
    paid: trans('common.status_paid'),
    partial: trans('common.status_partial'),
    overdue: trans('common.status_overdue'),
    cancelled: trans('common.status_cancelled'),
    registered: trans('common.status_registered'),
    created: trans('common.status_created'),
    accepted: trans('common.status_accepted'),
    rejected: trans('common.status_rejected'),
    converted: trans('common.status_converted'),
}));

const formatCurrency = (val: number | string) => {
    const num = typeof val === 'string' ? parseFloat(val) : val;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(num);
};

const formatDate = (val: string) => {
    if (!val) return '';
    const d = new Date(val);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const applyFilters = () => {
    router.get(route('documents.index', { type: props.documentType }), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        sort: sortBy.value || undefined,
        dir: sortDir.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleSort = (key: string, dir: 'asc' | 'desc') => {
    sortBy.value = key;
    sortDir.value = dir;
    applyFilters();
};

const handleSearch = (val: string) => {
    search.value = val;
    applyFilters();
};

const handleStatusFilter = (e: Event) => {
    statusFilter.value = (e.target as HTMLSelectElement).value;
    applyFilters();
};

// Delete confirmation
const deleteDialog = ref(false);
const deleteTarget = ref<any>(null);
const deleting = ref(false);

const confirmDelete = (doc: any) => {
    deleteTarget.value = doc;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('documents.destroy', { type: props.documentType, document: deleteTarget.value.id }), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('documents.title_' + documentType)" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('documents.title_' + documentType) }}</h1>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-4">
                <div class="w-full max-w-sm">
                    <SearchInput
                        :model-value="search"
                        @update:model-value="handleSearch"
                        :placeholder="$t('documents.search_placeholder')"
                    />
                </div>
                <select
                    :value="statusFilter"
                    @change="handleStatusFilter"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">{{ $t('documents.all_statuses') }}</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>
            <Link
                :href="route('documents.create', { type: documentType })"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                {{ $t('documents.new_' + documentType) }}
            </Link>
        </div>

        <!-- Table -->
        <DataTable
            :columns="columns"
            :rows="documents.data"
            :pagination="documents"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            @sort="handleSort"
        >
            <template #cell-number="{ row }">
                <Link
                    :href="route('documents.edit', { type: documentType, document: row.id })"
                    class="font-medium text-indigo-600 hover:text-indigo-900"
                >
                    {{ row.number || $t('common.status_draft') }}
                </Link>
            </template>

            <template #cell-client="{ row }">
                <span v-if="row.client">
                    {{ row.client.trade_name || row.client.legal_name }}
                    <span class="text-gray-400 text-xs ml-1">{{ row.client.nif }}</span>
                </span>
                <span v-else class="text-gray-400 italic">{{ $t('common.no_client') }}</span>
            </template>

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

            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('documents.edit', { type: documentType, document: row.id })"
                        class="text-indigo-600 hover:text-indigo-900"
                        :title="$t('common.edit')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487" /></svg>
                    </Link>
                    <button
                        v-if="['draft', 'created'].includes(row.status)"
                        @click="confirmDelete(row)"
                        class="text-red-600 hover:text-red-900"
                        :title="$t('common.delete')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Delete Confirmation -->
        <ConfirmDialog
            :show="deleteDialog"
            :title="trans('documents.delete_title')"
            :message="trans('documents.delete_message')"
            :confirm-label="trans('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
