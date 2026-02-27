<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportToolbar from '@/Components/ReportToolbar.vue';

interface ExpenseItem {
    id: number;
    due_date: string | null;
    concept: string;
    amount: number;
    is_overdue: boolean;
    days_overdue: number;
    supplier_name: string;
}

interface PurchaseItem {
    id: number;
    due_date: string;
    amount: number;
    is_overdue: boolean;
    days_overdue: number;
    document_number: string;
    document_id: number;
    supplier_name: string;
}

const props = defineProps<{
    expenseItems: ExpenseItem[];
    expenseTotalPending: number;
    expenseTotalOverdue: number;
    purchaseItems: PurchaseItem[];
    purchaseTotalPending: number;
    purchaseTotalOverdue: number;
    totalPending: number;
    totalOverdue: number;
    filters: {
        date_from: string | null;
        date_to: string | null;
    };
}>();

const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = () => {
    router.get(route('treasury.payments'), {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('treasury.payments'), {}, { preserveState: true, replace: true });
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const formatDate = (val: string | null) => {
    if (!val) return '-';
    const d = new Date(val);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const exportParams = computed(() => {
    const params: Record<string, string> = {};
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    return params;
});

const pdfUrl = computed(() => route('treasury.payments.pdf', exportParams.value));
const csvUrl = computed(() => route('treasury.payments.csv', exportParams.value));
</script>

<template>
    <Head :title="$t('treasury.payments_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('treasury.payments_title') }}</h1>
        </template>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap items-end gap-4 print:hidden">
            <div>
                <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.date_from') }}</label>
                <input type="date" v-model="dateFrom" @change="applyFilters" class="mt-0.5 block rounded-md border-gray-300 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.date_to') }}</label>
                <input type="date" v-model="dateTo" @change="applyFilters" class="mt-0.5 block rounded-md border-gray-300 text-sm" />
            </div>
            <button v-if="dateFrom || dateTo" @click="clearFilters" class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">
                {{ $t('common.clear') }}
            </button>
            <div class="ml-auto">
                <ReportToolbar :pdf-url="pdfUrl" :csv-url="csvUrl" />
            </div>
        </div>

        <!-- Section 1: Pending expenses -->
        <div class="rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('treasury.pending_expenses') }}</h3>
            </div>
            <div v-if="expenseItems.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.due_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.concept') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.supplier') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('treasury.amount') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('treasury.days_overdue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in expenseItems" :key="'exp-' + item.id" :class="item.is_overdue ? 'bg-red-50' : ''">
                            <td class="whitespace-nowrap px-4 py-3 text-sm" :class="item.is_overdue ? 'font-medium text-red-700' : 'text-gray-700'">
                                {{ formatDate(item.due_date) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a :href="route('expenses.edit', item.id)" class="font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ item.concept }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ item.supplier_name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.amount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm" :class="item.is_overdue ? 'font-medium text-red-700' : 'text-gray-400'">
                                {{ item.days_overdue > 0 ? item.days_overdue : '-' }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $t('treasury.total_pending') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">{{ formatCurrency(expenseTotalPending) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div v-else class="py-8 text-center text-sm text-gray-500">
                {{ $t('treasury.no_pending_payments') }}
            </div>
        </div>

        <!-- Section 2: Pending purchase invoices -->
        <div class="mt-6 rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('treasury.pending_purchase_invoices') }}</h3>
            </div>
            <div v-if="purchaseItems.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.due_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.document_number') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.supplier') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('treasury.amount') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('treasury.days_overdue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in purchaseItems" :key="'pur-' + item.id" :class="item.is_overdue ? 'bg-red-50' : ''">
                            <td class="whitespace-nowrap px-4 py-3 text-sm" :class="item.is_overdue ? 'font-medium text-red-700' : 'text-gray-700'">
                                {{ formatDate(item.due_date) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a :href="route('documents.edit', { type: 'purchase_invoice', document: item.document_id })" class="font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ item.document_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ item.supplier_name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.amount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm" :class="item.is_overdue ? 'font-medium text-red-700' : 'text-gray-400'">
                                {{ item.days_overdue > 0 ? item.days_overdue : '-' }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $t('treasury.total_pending') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">{{ formatCurrency(purchaseTotalPending) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div v-else class="py-8 text-center text-sm text-gray-500">
                {{ $t('treasury.no_pending_payments') }}
            </div>
        </div>

        <!-- Grand totals -->
        <div v-if="expenseItems.length > 0 || purchaseItems.length > 0" class="mt-6 rounded-lg bg-white p-4 shadow">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-gray-900">{{ $t('treasury.total_general') }}</span>
                <span class="text-lg font-bold text-gray-900">{{ formatCurrency(totalPending) }}</span>
            </div>
            <div v-if="totalOverdue > 0" class="mt-1 flex items-center justify-between">
                <span class="text-sm font-semibold text-red-700">{{ $t('treasury.total_overdue') }}</span>
                <span class="text-lg font-bold text-red-700">{{ formatCurrency(totalOverdue) }}</span>
            </div>
        </div>
    </AppLayout>
</template>
