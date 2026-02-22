<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

interface VatRow {
    vat_rate: string;
    base: number;
    vat: number;
}

const props = defineProps<{
    company: { legal_name: string; nif: string } | null;
    vatIssued: VatRow[];
    vatReceived: VatRow[];
    summary: {
        total_vat_issued: number;
        total_vat_received: number;
        difference: number;
    };
    filters: {
        year: number;
        quarter: number;
    };
}>();

const year = ref(props.filters.year);
const quarter = ref(props.filters.quarter);

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const applyFilters = () => {
    router.get(route('reports.fiscal.modelo-303'), {
        year: year.value,
        quarter: quarter.value,
    }, { preserveState: true, replace: true });
};

const quarterLabel = (q: number) => {
    const months: Record<number, string> = {
        1: trans('reports.q1_months'),
        2: trans('reports.q2_months'),
        3: trans('reports.q3_months'),
        4: trans('reports.q4_months'),
    };
    return `${q}T - ${months[q]}`;
};
</script>

<template>
    <Head :title="$t('reports.modelo_303')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('reports.modelo_303_full') }}</h1>
        </template>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500">{{ $t('reports.fiscal_year') }}</label>
                <select v-model="year" @change="applyFilters" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">{{ $t('reports.quarter') }}</label>
                <select v-model="quarter" @change="applyFilters" class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option v-for="q in [1,2,3,4]" :key="q" :value="q">{{ quarterLabel(q) }}</option>
                </select>
            </div>
        </div>

        <!-- Company info -->
        <div v-if="company" class="mb-6 rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-600"><span class="font-medium">{{ $t('reports.declarant') }}</span> {{ company.legal_name }} ({{ company.nif }})</p>
            <p class="text-sm text-gray-600"><span class="font-medium">{{ $t('reports.period') }}</span> {{ year }} - {{ quarterLabel(quarter) }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- IVA Devengado (Repercutido) -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $t('reports.vat_charged') }}</h3>
                    <p class="text-xs text-gray-500">{{ $t('reports.issued_invoices') }}</p>
                </div>
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('reports.vat_type') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_base') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="row in vatIssued" :key="row.vat_rate">
                            <td class="px-4 py-2 text-sm text-gray-700">{{ row.vat_rate }}%</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.base) }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.vat) }}</td>
                        </tr>
                        <tr v-if="vatIssued.length === 0">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-400">{{ $t('reports.no_operations') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-indigo-50">
                        <tr>
                            <td class="px-4 py-2 text-sm font-semibold text-indigo-900" colspan="2">{{ $t('reports.total_vat_charged') }}</td>
                            <td class="px-4 py-2 text-right text-sm font-bold text-indigo-900">{{ formatCurrency(summary.total_vat_issued) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- IVA Deducible (Soportado) -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $t('reports.vat_deductible') }}</h3>
                    <p class="text-xs text-gray-500">{{ $t('reports.received_invoices') }}</p>
                </div>
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('reports.vat_type') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_base') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('reports.col_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="row in vatReceived" :key="row.vat_rate">
                            <td class="px-4 py-2 text-sm text-gray-700">{{ row.vat_rate }}%</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.base) }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(row.vat) }}</td>
                        </tr>
                        <tr v-if="vatReceived.length === 0">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-400">{{ $t('reports.no_operations') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-green-50">
                        <tr>
                            <td class="px-4 py-2 text-sm font-semibold text-green-900" colspan="2">{{ $t('reports.total_vat_deductible') }}</td>
                            <td class="px-4 py-2 text-right text-sm font-bold text-green-900">{{ formatCurrency(summary.total_vat_received) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Result -->
        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">{{ $t('reports.settlement_result') }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ $t('reports.total_vat_charged') }}</span>
                    <span class="font-medium">{{ formatCurrency(summary.total_vat_issued) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ $t('reports.total_vat_deductible') }}</span>
                    <span class="font-medium text-green-700">- {{ formatCurrency(summary.total_vat_received) }}</span>
                </div>
                <hr class="border-gray-200" />
                <div class="flex justify-between text-base">
                    <span class="font-semibold text-gray-900">{{ $t('reports.difference') }}</span>
                    <span :class="['font-bold text-lg', summary.difference >= 0 ? 'text-red-700' : 'text-green-700']">
                        {{ formatCurrency(summary.difference) }}
                    </span>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-400">
                {{ $t('reports.fiscal_disclaimer') }}
            </p>
        </div>
    </AppLayout>
</template>
