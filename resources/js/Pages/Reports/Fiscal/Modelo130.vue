<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    company: { legal_name: string; nif: string } | null;
    data: {
        revenue: number;
        deductible_expenses: number;
        net_income: number;
        irpf_rate: number;
        irpf_payment: number;
        retentions: number;
        previous_payments: number;
        to_pay: number;
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
    router.get(route('reports.fiscal.modelo-130'), {
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
    <Head :title="$t('reports.modelo_130')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('reports.modelo_130_full') }}</h1>
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

        <!-- IRPF Calculation -->
        <div class="rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('reports.section_direct_estimation') }}</h3>
            </div>
            <div class="divide-y divide-gray-100 px-6">
                <!-- Row 1: Revenue -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $t('reports.row_01_income') }}</p>
                        <p class="text-xs text-gray-500">{{ $t('reports.row_01_subtitle') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(data.revenue) }}</p>
                </div>

                <!-- Row 2: Expenses -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $t('reports.row_02_expenses') }}</p>
                        <p class="text-xs text-gray-500">{{ $t('reports.row_02_subtitle') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(data.deductible_expenses) }}</p>
                </div>

                <!-- Row 3: Net income -->
                <div class="flex items-center justify-between bg-gray-50 py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $t('reports.row_03_net') }}</p>
                    </div>
                    <p :class="['text-sm font-bold', data.net_income >= 0 ? 'text-gray-900' : 'text-red-700']">
                        {{ formatCurrency(data.net_income) }}
                    </p>
                </div>

                <!-- Row 4: IRPF rate -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $t('reports.row_04_pct', { rate: String(data.irpf_rate) }) }}</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(data.irpf_payment) }}</p>
                </div>

                <!-- Row 5: Retentions -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $t('reports.row_05_withholdings') }}</p>
                        <p class="text-xs text-gray-500">{{ $t('reports.row_05_subtitle') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-green-700">- {{ formatCurrency(data.retentions) }}</p>
                </div>

                <!-- Row 6: Previous payments -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $t('reports.row_06_previous') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-green-700">- {{ formatCurrency(data.previous_payments) }}</p>
                </div>

                <!-- Row 7: Result -->
                <div class="flex items-center justify-between bg-indigo-50 py-4">
                    <div>
                        <p class="text-base font-semibold text-indigo-900">{{ $t('reports.row_07_total') }}</p>
                    </div>
                    <p :class="['text-lg font-bold', data.to_pay > 0 ? 'text-red-700' : 'text-green-700']">
                        {{ formatCurrency(data.to_pay) }}
                    </p>
                </div>
            </div>
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400">
                    {{ $t('reports.fiscal_disclaimer') }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
