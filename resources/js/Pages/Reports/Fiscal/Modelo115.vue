<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportToolbar from '@/Components/ReportToolbar.vue';

const props = defineProps<{
    company: { legal_name: string; nif: string } | null;
    data: {
        landlords: number;
        base: number;
        withheld: number;
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
    router.get(route('reports.fiscal.modelo-115'), {
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

const pdfUrl = () => route('reports.fiscal.modelo-115.pdf', { year: year.value, quarter: quarter.value });
const csvUrl = () => route('reports.fiscal.modelo-115.csv', { year: year.value, quarter: quarter.value });
</script>

<template>
    <Head :title="$t('reports.modelo_115')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('reports.modelo_115_full') }}</h1>
        </template>

        <div class="mb-6 flex flex-wrap items-end gap-4 print:hidden">
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
            <ReportToolbar :pdfUrl="pdfUrl()" :csvUrl="csvUrl()" />
        </div>

        <div v-if="company" class="mb-6 rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-600"><span class="font-medium">{{ $t('reports.declarant') }}</span> {{ company.legal_name }} ({{ company.nif }})</p>
            <p class="text-sm text-gray-600"><span class="font-medium">{{ $t('reports.period') }}</span> {{ year }} - {{ quarterLabel(quarter) }}</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">{{ $t('reports.section_rental') }}</h3>

            <div v-if="data.landlords > 0" class="space-y-4">
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-sm text-gray-600">{{ $t('reports.casilla_01_landlords') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ data.landlords }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-sm text-gray-600">{{ $t('reports.casilla_02_base') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(data.base) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-sm text-gray-600">{{ $t('reports.casilla_03_withheld') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(data.withheld) }}</span>
                </div>
                <div class="flex justify-between pt-2">
                    <span class="text-base font-semibold text-gray-900">{{ $t('reports.result_to_pay') }}</span>
                    <span class="text-lg font-bold text-red-700">{{ formatCurrency(data.to_pay) }}</span>
                </div>
            </div>
            <p v-else class="text-sm text-gray-400">{{ $t('reports.no_retentions') }}</p>
        </div>

        <p class="mt-4 text-xs text-gray-400">{{ $t('reports.fiscal_disclaimer') }}</p>
    </AppLayout>
</template>
