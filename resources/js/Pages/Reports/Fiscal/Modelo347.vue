<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportToolbar from '@/Components/ReportToolbar.vue';

interface OperationRow {
    nif: string;
    name: string;
    annual_total: number;
    q1: number;
    q2: number;
    q3: number;
    q4: number;
}

const props = defineProps<{
    company: { legal_name: string; nif: string } | null;
    sales: OperationRow[];
    purchases: OperationRow[];
    hasMissingNif: boolean;
    filters: {
        year: number;
    };
}>();

const year = ref(props.filters.year);

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const applyFilters = () => {
    router.get(route('reports.fiscal.modelo-347'), {
        year: year.value,
    }, { preserveState: true, replace: true });
};

const pdfUrl = () => route('reports.fiscal.modelo-347.pdf', { year: year.value });
const csvUrl = () => route('reports.fiscal.modelo-347.csv', { year: year.value });

const sectionTotal = (rows: OperationRow[]) => rows.reduce((s, r) => s + r.annual_total, 0);
</script>

<template>
    <Head :title="$t('reports.modelo_347')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('reports.modelo_347_full') }}</h1>
        </template>

        <div class="mb-6 flex flex-wrap items-end gap-4 print:hidden">
            <div>
                <label class="block text-xs font-medium text-gray-500">{{ $t('reports.fiscal_year') }}</label>
                <select v-model="year" @change="applyFilters" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
            </div>
            <ReportToolbar :pdfUrl="pdfUrl()" :csvUrl="csvUrl()" />
        </div>

        <!-- Company info -->
        <div v-if="company" class="mb-6 rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-600"><span class="font-medium">{{ $t('reports.declarant') }}</span> {{ company.legal_name }} ({{ company.nif }})</p>
            <p class="text-sm text-gray-600"><span class="font-medium">{{ $t('reports.year_label') }}</span> {{ year }}</p>
        </div>

        <p class="mb-4 text-xs text-gray-500">{{ $t('reports.threshold_notice') }}</p>

        <!-- Warning: missing NIF -->
        <div v-if="hasMissingNif" class="mb-4 rounded-md bg-amber-50 border border-amber-200 p-3">
            <p class="text-sm text-amber-700">{{ $t('reports.missing_nif_warning') }}</p>
        </div>

        <!-- Section A: Sales -->
        <div class="mb-6 rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('reports.section_sales') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_nif') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_client') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_annual_total') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q1') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q2') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q3') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q4') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="row in sales" :key="row.nif" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-2 text-sm font-medium text-gray-900">{{ row.nif }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700">{{ row.name }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm font-semibold text-gray-900">{{ formatCurrency(row.annual_total) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q1) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q2) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q3) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q4) }}</td>
                        </tr>
                        <tr v-if="sales.length === 0">
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-400">{{ $t('reports.no_operations_347') }}</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="sales.length > 0" class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900" colspan="2">{{ $t('reports.total_section') }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-900">{{ formatCurrency(sectionTotal(sales)) }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Section B: Purchases -->
        <div class="rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('reports.section_purchases') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_nif') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('books.col_supplier') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_annual_total') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q1') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q2') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q3') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_q4') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="row in purchases" :key="row.nif" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-2 text-sm font-medium text-gray-900">{{ row.nif }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700">{{ row.name }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm font-semibold text-gray-900">{{ formatCurrency(row.annual_total) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q1) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q2) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q3) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ formatCurrency(row.q4) }}</td>
                        </tr>
                        <tr v-if="purchases.length === 0">
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-400">{{ $t('reports.no_operations_347') }}</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="purchases.length > 0" class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900" colspan="2">{{ $t('reports.total_section') }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-900">{{ formatCurrency(sectionTotal(purchases)) }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <p class="mt-4 text-xs text-gray-400">{{ $t('reports.fiscal_disclaimer') }}</p>
    </AppLayout>
</template>
