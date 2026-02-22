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

interface Category {
    id: number;
    name: string;
    code: string;
}

const props = defineProps<{
    expenses: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: any[];
    };
    categories: Category[];
    filters: {
        search?: string;
        category?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
        sort?: string;
        dir?: string;
    };
}>();

const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');
const statusFilter = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sortBy = ref(props.filters.sort || '');
const sortDir = ref<'asc' | 'desc'>((props.filters.dir as 'asc' | 'desc') || 'desc');

const columns = computed<Column[]>(() => [
    { key: 'expense_date', label: trans('expenses.col_date'), sortable: true },
    { key: 'concept', label: trans('expenses.col_concept') },
    { key: 'supplier', label: trans('expenses.col_supplier') },
    { key: 'category', label: trans('expenses.col_category') },
    { key: 'total', label: trans('expenses.col_total'), sortable: true, class: 'text-right' },
    { key: 'payment_status', label: trans('expenses.col_status'), sortable: true },
]);

const statusColors: Record<string, 'gray' | 'green' | 'yellow'> = {
    pending: 'yellow',
    paid: 'green',
};

const statusLabels = computed<Record<string, string>>(() => ({
    pending: trans('expenses.status_pending'),
    paid: trans('expenses.status_paid'),
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
    router.get(route('expenses.index'), {
        search: search.value || undefined,
        category: categoryFilter.value || undefined,
        status: statusFilter.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
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

const handleCategoryFilter = (e: Event) => {
    categoryFilter.value = (e.target as HTMLSelectElement).value;
    applyFilters();
};

const handleStatusFilter = (e: Event) => {
    statusFilter.value = (e.target as HTMLSelectElement).value;
    applyFilters();
};

const handleDateFrom = (e: Event) => {
    dateFrom.value = (e.target as HTMLInputElement).value;
    applyFilters();
};

const handleDateTo = (e: Event) => {
    dateTo.value = (e.target as HTMLInputElement).value;
    applyFilters();
};

// Delete confirmation
const deleteDialog = ref(false);
const deleteTarget = ref<any>(null);
const deleting = ref(false);

const confirmDelete = (expense: any) => {
    deleteTarget.value = expense;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('expenses.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};

// Mark as paid
const markPaidDialog = ref(false);
const markPaidTarget = ref<any>(null);
const markPaidDate = ref(new Date().toISOString().split('T')[0]);
const markPaidMethod = ref('');
const markingPaid = ref(false);

const openMarkPaid = (expense: any) => {
    markPaidTarget.value = expense;
    markPaidDate.value = new Date().toISOString().split('T')[0];
    markPaidMethod.value = '';
    markPaidDialog.value = true;
};

const executeMarkPaid = () => {
    if (!markPaidTarget.value) return;
    markingPaid.value = true;
    router.post(route('expenses.mark-paid', markPaidTarget.value.id), {
        payment_date: markPaidDate.value,
        payment_method: markPaidMethod.value || null,
    }, {
        onFinish: () => {
            markingPaid.value = false;
            markPaidDialog.value = false;
            markPaidTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('expenses.title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('expenses.title') }}</h1>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-wrap gap-3">
                <div class="w-full max-w-xs">
                    <SearchInput
                        :model-value="search"
                        @update:model-value="handleSearch"
                        :placeholder="$t('expenses.search_placeholder')"
                    />
                </div>
                <select
                    :value="categoryFilter"
                    @change="handleCategoryFilter"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">{{ $t('expenses.all_categories') }}</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">
                        {{ c.code ? `[${c.code}] ` : '' }}{{ c.name }}
                    </option>
                </select>
                <select
                    :value="statusFilter"
                    @change="handleStatusFilter"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">{{ $t('expenses.all_statuses') }}</option>
                    <option value="pending">{{ $t('expenses.status_pending') }}</option>
                    <option value="paid">{{ $t('expenses.status_paid') }}</option>
                </select>
                <input
                    type="date"
                    :value="dateFrom"
                    @change="handleDateFrom"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :title="$t('common.from')"
                />
                <input
                    type="date"
                    :value="dateTo"
                    @change="handleDateTo"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :title="$t('common.to_date')"
                />
            </div>
            <Link
                :href="route('expenses.create')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                {{ $t('expenses.new_expense') }}
            </Link>
        </div>

        <!-- Table -->
        <DataTable
            :columns="columns"
            :rows="expenses.data"
            :pagination="expenses"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            @sort="handleSort"
            :empty-message="$t('expenses.no_expenses')"
        >
            <template #cell-expense_date="{ value }">
                {{ formatDate(value) }}
            </template>

            <template #cell-concept="{ row }">
                <Link
                    :href="route('expenses.edit', row.id)"
                    class="font-medium text-indigo-600 hover:text-indigo-900"
                >
                    {{ row.concept }}
                </Link>
                <p v-if="row.invoice_number" class="text-xs text-gray-400">{{ row.invoice_number }}</p>
            </template>

            <template #cell-supplier="{ row }">
                <span v-if="row.supplier">
                    {{ row.supplier.trade_name || row.supplier.legal_name }}
                </span>
                <span v-else-if="row.supplier_name" class="text-gray-600">
                    {{ row.supplier_name }}
                </span>
                <span v-else class="text-gray-400 italic">--</span>
            </template>

            <template #cell-category="{ row }">
                <span v-if="row.category" class="text-sm">
                    <span v-if="row.category.code" class="text-gray-400">[{{ row.category.code }}]</span>
                    {{ row.category.name }}
                </span>
                <span v-else class="text-gray-400 italic">--</span>
            </template>

            <template #cell-total="{ value }">
                <span class="font-medium">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-payment_status="{ value }">
                <Badge :color="statusColors[value] || 'gray'">
                    {{ statusLabels[value] || value }}
                </Badge>
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('expenses.edit', row.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                    >
                        {{ $t('common.edit') }}
                    </Link>
                    <button
                        v-if="row.payment_status === 'pending'"
                        @click="openMarkPaid(row)"
                        class="text-green-600 hover:text-green-900"
                    >
                        {{ $t('expenses.pay') }}
                    </button>
                    <button
                        @click="confirmDelete(row)"
                        class="text-red-600 hover:text-red-900"
                    >
                        {{ $t('common.delete') }}
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Delete Confirmation -->
        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('expenses.delete_title')"
            :message="$t('expenses.delete_message', { concept: deleteTarget?.concept })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />

        <!-- Mark as Paid Dialog -->
        <ConfirmDialog
            :show="markPaidDialog"
            :title="$t('expenses.mark_as_paid_title')"
            :confirm-label="$t('expenses.mark_as_paid_confirm')"
            :processing="markingPaid"
            @confirm="executeMarkPaid"
            @cancel="markPaidDialog = false"
        >
            <div class="space-y-3">
                <p class="text-sm text-gray-600">{{ $t('expenses.mark_as_paid_message', { concept: markPaidTarget?.concept }) }}</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.payment_date_label') }}</label>
                    <input
                        type="date"
                        v-model="markPaidDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.payment_method_label') }}</label>
                    <select
                        v-model="markPaidMethod"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ $t('common.select_payment') }}</option>
                        <option value="transfer">{{ $t('common.payment_transfer') }}</option>
                        <option value="card">{{ $t('common.payment_card') }}</option>
                        <option value="cash">{{ $t('common.payment_cash') }}</option>
                        <option value="direct_debit">{{ $t('common.payment_direct_debit') }}</option>
                    </select>
                </div>
            </div>
        </ConfirmDialog>
    </AppLayout>
</template>
