<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportToolbar from '@/Components/ReportToolbar.vue';

interface Row {
    id: number;
    issue_date: string;
    number: string;
    series_name: string | null;
    client_name: string;
    client_nif: string;
    tax_base: number;
    total_vat: number;
    total_irpf: number;
    total: number;
    month_key: string;
    month_label: string;
}

const props = defineProps<{
    data: Row[];
    totals: { tax_base: number; total_vat: number; total_irpf: number; total: number };
    filters: { date_from: string; date_to: string };
}>();

const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);

const formatCurrency = (val: number | string) => {
    const num = typeof val === 'string' ? parseFloat(val) : val;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(num);
};

const applyFilters = () => {
    router.get(route('reports.books.ventas'), {
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, { preserveState: true, replace: true });
};

const pdfUrl = () => route('reports.books.ventas.pdf', { date_from: dateFrom.value, date_to: dateTo.value });
const csvUrl = () => route('reports.books.ventas.csv', { date_from: dateFrom.value, date_to: dateTo.value });

// Group rows by month for subtotals
const monthGroups = () => {
    const groups: { key: string; label: string; rows: Row[]; totals: { tax_base: number; total_vat: number; total_irpf: number; total: number } }[] = [];
    const map = new Map<string, Row[]>();
    props.data.forEach(r => {
        if (!map.has(r.month_key)) map.set(r.month_key, []);
        map.get(r.month_key)!.push(r);
    });
    map.forEach((rows, key) => {
        groups.push({
            key,
            label: rows[0].month_label,
            rows,
            totals: {
                tax_base: rows.reduce((s, r) => s + r.tax_base, 0),
                total_vat: rows.reduce((s, r) => s + r.total_vat, 0),
                total_irpf: rows.reduce((s, r) => s + r.total_irpf, 0),
                total: rows.reduce((s, r) => s + r.total, 0),
            },
        });
    });
    return groups;
};
</script>

<template>
    <Head :title="$t('books.libro_ventas')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('books.libro_ventas') }}</h1>
        </template>

        <!-- Filters + Toolbar -->
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

        <!-- Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_date') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_number') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_series') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_client') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_nif') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_base') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_vat') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_irpf') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('books.col_total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template v-for="group in monthGroups()" :key="group.key">
                        <tr v-for="(row, i) in group.rows" :key="row.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700">{{ row.issue_date }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm font-medium text-gray-900">{{ row.number }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-500">{{ row.series_name || '' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700">{{ row.client_name }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-500">{{ row.client_nif }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.tax_base) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.total_vat) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-red-600">{{ formatCurrency(row.total_irpf) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm font-semibold text-gray-900">{{ formatCurrency(row.total) }}</td>
                        </tr>
                        <!-- Monthly subtotal -->
                        <tr class="bg-blue-50">
                            <td colspan="5" class="px-4 py-2 text-sm font-medium text-blue-800">{{ $t('books.monthly_subtotal', { month: group.label }) }}</td>
                            <td class="px-4 py-2 text-right text-sm font-medium text-blue-800">{{ formatCurrency(group.totals.tax_base) }}</td>
                            <td class="px-4 py-2 text-right text-sm font-medium text-blue-800">{{ formatCurrency(group.totals.total_vat) }}</td>
                            <td class="px-4 py-2 text-right text-sm font-medium text-red-700">{{ formatCurrency(group.totals.total_irpf) }}</td>
                            <td class="px-4 py-2 text-right text-sm font-bold text-blue-900">{{ formatCurrency(group.totals.total) }}</td>
                        </tr>
                    </template>
                    <tr v-if="data.length === 0">
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-400">{{ $t('books.no_data') }}</td>
                    </tr>
                </tbody>
                <tfoot v-if="data.length > 0" class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900" colspan="5">{{ $t('books.total_period') }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.tax_base) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total_vat) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-red-700">{{ formatCurrency(totals.total_irpf) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </AppLayout>
</template>
