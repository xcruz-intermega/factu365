<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportToolbar from '@/Components/ReportToolbar.vue';

interface VatBreakdownRow {
    vat_rate: number;
    base: number;
    vat: number;
}

interface Row {
    id: number;
    issue_date: string;
    number: string;
    series_name: string | null;
    client_name: string;
    client_nif: string;
    invoice_type: string;
    invoice_type_label: string;
    tax_base: number;
    total_vat: number;
    total_surcharge: number;
    total: number;
    vat_breakdown: VatBreakdownRow[];
    month_key: string;
    month_label: string;
}

const props = defineProps<{
    data: Row[];
    totals: { tax_base: number; total_vat: number; total_surcharge: number; total: number };
    filters: { date_from: string; date_to: string };
}>();

const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);

const formatCurrency = (val: number | string) => {
    const num = typeof val === 'string' ? parseFloat(val) : val;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(num);
};

const applyFilters = () => {
    router.get(route('reports.books.expedidas'), {
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, { preserveState: true, replace: true });
};

const pdfUrl = () => route('reports.books.expedidas.pdf', { date_from: dateFrom.value, date_to: dateTo.value });
const csvUrl = () => route('reports.books.expedidas.csv', { date_from: dateFrom.value, date_to: dateTo.value });

// Track expanded rows for VAT breakdown
const expandedRows = ref(new Set<number>());
const toggleExpand = (id: number) => {
    const next = new Set(expandedRows.value);
    if (next.has(id)) next.delete(id); else next.add(id);
    expandedRows.value = next;
};
</script>

<template>
    <Head :title="$t('books.libro_expedidas')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('books.libro_expedidas') }}</h1>
        </template>

        <div class="mb-6 flex flex-wrap items-end gap-4 print:hidden">
            <div>
                <label class="block text-xs font-medium text-gray-500">{{ $t('common.from') }}</label>
                <input type="date" v-model="dateFrom" @change="applyFilters" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">{{ $t('common.to_date') }}</label>
                <input type="date" v-model="dateTo" @change="applyFilters" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <ReportToolbar :pdfUrl="pdfUrl()" :csvUrl="csvUrl()" />
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_series') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_number') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_date') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_nif') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_name') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_invoice_type') }}</th>
                        <th class="px-3 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_base') }}</th>
                        <th class="px-3 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_vat') }}</th>
                        <th class="px-3 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_surcharge') }}</th>
                        <th class="px-3 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-for="row in data" :key="row.id">
                        <tr class="cursor-pointer hover:bg-gray-50" @click="toggleExpand(row.id)">
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ row.series_name || '' }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-900">{{ row.number }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-700">{{ row.issue_date }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ row.client_nif }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-700">{{ row.client_name }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-600">{{ row.invoice_type_label }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.tax_base) }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.total_vat) }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right text-sm text-gray-500">{{ formatCurrency(row.total_surcharge) }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right text-sm font-semibold text-gray-900">{{ formatCurrency(row.total) }}</td>
                        </tr>
                        <!-- VAT breakdown sub-row -->
                        <tr v-if="expandedRows.has(row.id) && row.vat_breakdown.length > 0" class="bg-indigo-50/50">
                            <td colspan="10" class="px-6 py-2">
                                <div class="text-xs font-medium text-gray-500 mb-1">{{ $t('books.vat_breakdown') }}</div>
                                <div class="flex gap-6">
                                    <div v-for="vb in row.vat_breakdown" :key="vb.vat_rate" class="text-xs text-gray-600">
                                        <span class="font-medium">{{ vb.vat_rate }}%:</span>
                                        {{ $t('books.vat_base') }} {{ formatCurrency(vb.base) }} &middot;
                                        {{ $t('books.vat_amount') }} {{ formatCurrency(vb.vat) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="data.length === 0">
                        <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-400">{{ $t('books.no_data') }}</td>
                    </tr>
                </tbody>
                <tfoot v-if="data.length > 0" class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="px-3 py-3 text-sm text-gray-900" colspan="6">{{ $t('books.total_period') }}</td>
                        <td class="px-3 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.tax_base) }}</td>
                        <td class="px-3 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total_vat) }}</td>
                        <td class="px-3 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total_surcharge) }}</td>
                        <td class="px-3 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </AppLayout>
</template>
