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
    recurringInvoices: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
    };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const columns = computed<Column[]>(() => [
    { key: 'name', label: trans('recurring.col_name') },
    { key: 'client', label: trans('recurring.col_client') },
    { key: 'frequency', label: trans('recurring.col_frequency') },
    { key: 'next_issue_date', label: trans('recurring.col_next_date') },
    { key: 'status', label: trans('recurring.col_status') },
    { key: 'generated', label: trans('recurring.col_generated'), class: 'text-center' },
]);

const statusColors: Record<string, 'green' | 'yellow' | 'gray'> = {
    active: 'green',
    paused: 'yellow',
    finished: 'gray',
};

const statusLabels = computed<Record<string, string>>(() => ({
    active: trans('recurring.status_active'),
    paused: trans('recurring.status_paused'),
    finished: trans('recurring.status_finished'),
}));

const unitLabels = computed<Record<string, string>>(() => ({
    day: trans('recurring.unit_day'),
    week: trans('recurring.unit_week'),
    month: trans('recurring.unit_month'),
    year: trans('recurring.unit_year'),
}));

const formatFrequency = (row: any) => {
    return trans('recurring.frequency_every', {
        value: row.interval_value,
        unit: unitLabels.value[row.interval_unit] || row.interval_unit,
    });
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-ES');
};

const applyFilters = () => {
    router.get(route('recurring-invoices.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleSearch = (val: string) => {
    search.value = val;
    applyFilters();
};

// Delete
const deleteDialog = ref(false);
const deleteTarget = ref<any>(null);
const deleting = ref(false);

const confirmDelete = (row: any) => {
    deleteTarget.value = row;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('recurring-invoices.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};

// Toggle status
const toggleStatus = (row: any) => {
    router.post(route('recurring-invoices.toggle-status', row.id), {}, {
        preserveScroll: true,
    });
};

// Generate now
const generateDialog = ref(false);
const generateTarget = ref<any>(null);
const generating = ref(false);

const confirmGenerate = (row: any) => {
    generateTarget.value = row;
    generateDialog.value = true;
};

const executeGenerate = () => {
    if (!generateTarget.value) return;
    generating.value = true;
    router.post(route('recurring-invoices.generate-now', generateTarget.value.id), {}, {
        onFinish: () => {
            generating.value = false;
            generateDialog.value = false;
            generateTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('recurring.title_index')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('recurring.title_index') }}</h1>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-4">
                <div class="w-full max-w-sm">
                    <SearchInput
                        :model-value="search"
                        @update:model-value="handleSearch"
                        :placeholder="$t('common.search')"
                    />
                </div>
                <select
                    :value="statusFilter"
                    @change="statusFilter = ($event.target as HTMLSelectElement).value; applyFilters()"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">{{ $t('common.all') }}</option>
                    <option value="active">{{ $t('recurring.status_active') }}</option>
                    <option value="paused">{{ $t('recurring.status_paused') }}</option>
                    <option value="finished">{{ $t('recurring.status_finished') }}</option>
                </select>
            </div>
            <Link
                :href="route('recurring-invoices.create')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                {{ $t('recurring.btn_create') }}
            </Link>
        </div>

        <!-- Empty state -->
        <div v-if="recurringInvoices.data.length === 0 && !filters.search && !filters.status" class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ $t('recurring.empty_title') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('recurring.empty_description') }}</p>
            <div class="mt-6">
                <Link
                    :href="route('recurring-invoices.create')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    {{ $t('recurring.btn_create') }}
                </Link>
            </div>
        </div>

        <!-- Table -->
        <DataTable
            v-else
            :columns="columns"
            :rows="recurringInvoices.data"
            :pagination="recurringInvoices"
            :empty-message="$t('recurring.empty_title')"
        >
            <template #cell-name="{ row }">
                <Link :href="route('recurring-invoices.show', row.id)" class="font-medium text-indigo-600 hover:text-indigo-900">
                    {{ row.name }}
                </Link>
            </template>

            <template #cell-client="{ row }">
                <span v-if="row.client">{{ row.client.trade_name || row.client.legal_name }}</span>
                <span v-else class="text-gray-400">—</span>
            </template>

            <template #cell-frequency="{ row }">
                {{ formatFrequency(row) }}
            </template>

            <template #cell-next_issue_date="{ row }">
                <span v-if="row.status !== 'finished'">{{ formatDate(row.next_issue_date) }}</span>
                <span v-else class="text-gray-400">—</span>
            </template>

            <template #cell-status="{ row }">
                <Badge :color="statusColors[row.status] || 'gray'">
                    {{ statusLabels[row.status] || row.status }}
                </Badge>
            </template>

            <template #cell-generated="{ row }">
                <span class="font-mono text-sm">
                    {{ row.occurrences_count }}{{ row.max_occurrences ? ` / ${row.max_occurrences}` : '' }}
                </span>
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('recurring-invoices.show', row.id)"
                        class="text-gray-500 hover:text-gray-700"
                        :title="$t('common.view')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </Link>
                    <Link
                        :href="route('recurring-invoices.edit', row.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                        :title="$t('common.edit')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487" /></svg>
                    </Link>
                    <button
                        v-if="row.status !== 'finished'"
                        @click="toggleStatus(row)"
                        class="text-yellow-600 hover:text-yellow-800"
                        :title="row.status === 'active' ? $t('recurring.btn_pause') : $t('recurring.btn_resume')"
                    >
                        <svg v-if="row.status === 'active'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" /></svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" /></svg>
                    </button>
                    <button
                        v-if="row.status === 'active'"
                        @click="confirmGenerate(row)"
                        class="text-green-600 hover:text-green-800"
                        :title="$t('recurring.btn_generate_now')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    </button>
                    <button
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
            :title="trans('recurring.confirm_delete_title')"
            :message="trans('recurring.confirm_delete_message', { name: deleteTarget?.name || '' })"
            :confirm-label="trans('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />

        <!-- Generate Confirmation -->
        <ConfirmDialog
            :show="generateDialog"
            :title="trans('recurring.confirm_generate_title')"
            :message="trans('recurring.confirm_generate_message', { name: generateTarget?.name || '' })"
            :confirm-label="trans('recurring.btn_generate_now')"
            :processing="generating"
            @confirm="executeGenerate"
            @cancel="generateDialog = false"
        />
    </AppLayout>
</template>
