<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface ClientRow {
    client_id: number | null;
    invoice_count: number;
    total_base: string;
    total_vat: string;
    total_irpf: string;
    total_amount: string;
    client?: {
        id: number;
        legal_name: string;
        trade_name: string | null;
        nif: string;
    };
}

const props = defineProps<{
    data: ClientRow[];
    totals: {
        invoice_count: number;
        total_base: number;
        total_vat: number;
        total_irpf: number;
        total_amount: number;
    };
    filters: {
        date_from: string;
        date_to: string;
    };
}>();

const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);

const formatCurrency = (val: number | string) => {
    const num = typeof val === 'string' ? parseFloat(val) : val;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(num);
};

const applyFilters = () => {
    router.get(route('reports.sales.by-client'), {
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, { preserveState: true, replace: true });
};

const exportCsv = () => {
    window.location.href = route('reports.sales.export-csv', {
        date_from: dateFrom.value,
        date_to: dateTo.value,
    });
};
</script>

<template>
    <Head title="Ventas por cliente" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Ventas por cliente</h1>
        </template>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500">Desde</label>
                <input type="date" v-model="dateFrom" @change="applyFilters" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Hasta</label>
                <input type="date" v-model="dateTo" @change="applyFilters" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <button @click="exportCsv" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Exportar CSV
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">NIF</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Facturas</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Base imponible</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">IVA</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">IRPF</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="(row, i) in data" :key="row.client_id ?? i" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                            {{ row.client?.trade_name || row.client?.legal_name || 'Sin cliente' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ row.client?.nif || '--' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ row.invoice_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ formatCurrency(row.total_base) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ formatCurrency(row.total_vat) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-red-600">{{ formatCurrency(row.total_irpf) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ formatCurrency(row.total_amount) }}</td>
                    </tr>
                    <tr v-if="data.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">Sin datos para el periodo seleccionado.</td>
                    </tr>
                </tbody>
                <tfoot v-if="data.length > 0" class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900" colspan="2">Total</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ totals.invoice_count }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total_base) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total_vat) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-red-700">{{ formatCurrency(totals.total_irpf) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900">{{ formatCurrency(totals.total_amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </AppLayout>
</template>
