<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface MonthData {
    label: string;
    month: string;
    revenue: number;
    expenses: number;
    result: number;
}

interface RecentDocument {
    id: number;
    document_type: string;
    number: string | null;
    issue_date: string;
    total: string;
    status: string;
    client?: {
        id: number;
        legal_name: string;
        trade_name: string | null;
        nif: string;
    };
}

interface RecentExpense {
    id: number;
    concept: string;
    expense_date: string;
    total: string;
    payment_status: string;
    category?: {
        id: number;
        name: string;
        code: string;
    };
}

const props = defineProps<{
    stats: {
        invoiced_this_month: number;
        pending_collection: number;
        expenses_this_month: number;
        result_this_month: number;
        overdue_count: number;
    };
    verifactu: {
        accepted: number;
        pending: number;
        rejected: number;
        error: number;
    };
    monthlyEvolution: MonthData[];
    recentDocuments: RecentDocument[];
    recentExpenses: RecentExpense[];
}>();

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const formatDate = (val: string) => {
    if (!val) return '';
    const d = new Date(val);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const statusLabels = computed<Record<string, string>>(() => ({
    draft: trans('common.status_draft'),
    finalized: trans('common.status_finalized'),
    sent: trans('common.status_sent'),
    paid: trans('common.status_paid'),
    partial: trans('common.status_partial'),
    overdue: trans('common.status_overdue'),
    cancelled: trans('common.status_cancelled'),
    pending: trans('common.status_pending'),
}));

// Chart data
const chartData = computed(() => ({
    labels: props.monthlyEvolution.map(m => m.label),
    datasets: [
        {
            label: trans('dashboard.income'),
            data: props.monthlyEvolution.map(m => m.revenue),
            backgroundColor: 'rgba(79, 70, 229, 0.7)',
            borderRadius: 4,
        },
        {
            label: trans('dashboard.chart_expenses'),
            data: props.monthlyEvolution.map(m => m.expenses),
            backgroundColor: 'rgba(239, 68, 68, 0.5)',
            borderRadius: 4,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
        },
        tooltip: {
            callbacks: {
                label: (ctx: any) => {
                    return `${ctx.dataset.label}: ${formatCurrency(ctx.parsed.y)}`;
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value: any) => formatCurrency(value),
            },
        },
    },
};

const kpiCards = computed(() => [
    {
        label: trans('dashboard.invoiced_this_month'),
        value: props.stats.invoiced_this_month,
        format: 'currency',
        color: 'text-indigo-700',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        bgColor: 'bg-indigo-50',
        iconColor: 'text-indigo-400',
    },
    {
        label: trans('dashboard.pending_collection'),
        value: props.stats.pending_collection,
        format: 'currency',
        color: 'text-amber-700',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        bgColor: 'bg-amber-50',
        iconColor: 'text-amber-400',
    },
    {
        label: trans('dashboard.expenses_this_month'),
        value: props.stats.expenses_this_month,
        format: 'currency',
        color: 'text-red-700',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        bgColor: 'bg-red-50',
        iconColor: 'text-red-400',
    },
    {
        label: trans('dashboard.month_result'),
        value: props.stats.result_this_month,
        format: 'currency',
        color: props.stats.result_this_month >= 0 ? 'text-green-700' : 'text-red-700',
        icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        bgColor: props.stats.result_this_month >= 0 ? 'bg-green-50' : 'bg-red-50',
        iconColor: props.stats.result_this_month >= 0 ? 'text-green-400' : 'text-red-400',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('dashboard.title') }}</h1>
        </template>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="kpi in kpiCards"
                :key="kpi.label"
                class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6"
            >
                <div class="flex items-center">
                    <div :class="[kpi.bgColor, 'rounded-md p-3']">
                        <svg :class="['h-6 w-6', kpi.iconColor]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="kpi.icon" />
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <dt class="truncate text-sm font-medium text-gray-500">{{ kpi.label }}</dt>
                        <dd :class="['mt-1 text-2xl font-semibold tracking-tight', kpi.color]">
                            {{ formatCurrency(kpi.value) }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue alert -->
        <div v-if="stats.overdue_count > 0" class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800">
                    {{ stats.overdue_count === 1 ? $t('dashboard.overdue_alert_single', { count: String(stats.overdue_count) }) : $t('dashboard.overdue_alert_plural', { count: String(stats.overdue_count) }) }}.
                    <Link :href="route('documents.index', { type: 'invoice', status: 'overdue' })" class="font-bold underline hover:text-red-900">{{ $t('dashboard.view_invoices') }}</Link>
                </p>
            </div>
        </div>

        <!-- Chart + VeriFactu -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Revenue vs Expenses chart -->
            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">{{ $t('dashboard.monthly_evolution') }}</h3>
                <div class="h-72">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- VeriFactu status -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">{{ $t('dashboard.verifactu') }}</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $t('dashboard.vf_accepted') }}</span>
                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-medium text-green-800">{{ verifactu.accepted }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $t('dashboard.vf_pending') }}</span>
                        <span class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-sm font-medium text-yellow-800">{{ verifactu.pending }}</span>
                    </div>
                    <div v-if="verifactu.rejected > 0" class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $t('dashboard.vf_rejected') }}</span>
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-medium text-red-800">{{ verifactu.rejected }}</span>
                    </div>
                    <div v-if="verifactu.error > 0" class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $t('dashboard.vf_error') }}</span>
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-medium text-red-800">{{ verifactu.error }}</span>
                    </div>
                </div>
                <div class="mt-4 border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400">{{ $t('dashboard.vf_total_records') }} {{ verifactu.accepted + verifactu.pending + verifactu.rejected + verifactu.error }}</p>
                </div>
            </div>
        </div>

        <!-- Recent activity -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent invoices -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $t('dashboard.latest_invoices') }}</h3>
                    <Link :href="route('documents.index', { type: 'invoice' })" class="text-sm text-indigo-600 hover:text-indigo-500">{{ $t('dashboard.view_all') }}</Link>
                </div>
                <div v-if="recentDocuments.length > 0" class="mt-4 divide-y divide-gray-100">
                    <div v-for="doc in recentDocuments" :key="doc.id" class="flex items-center justify-between py-3">
                        <div class="min-w-0 flex-1">
                            <Link :href="route('documents.edit', { type: doc.document_type, document: doc.id })" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                {{ doc.number || $t('common.status_draft') }}
                            </Link>
                            <p class="text-xs text-gray-500">
                                {{ doc.client?.trade_name || doc.client?.legal_name || $t('common.no_client') }}
                                &middot; {{ formatDate(doc.issue_date) }}
                            </p>
                        </div>
                        <div class="ml-4 text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(parseFloat(doc.total)) }}</p>
                            <p class="text-xs text-gray-400">{{ statusLabels[doc.status] || doc.status }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="mt-4 text-sm text-gray-400">{{ $t('dashboard.no_invoices_yet') }}</p>
            </div>

            <!-- Recent expenses -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $t('dashboard.latest_expenses') }}</h3>
                    <Link :href="route('expenses.index')" class="text-sm text-indigo-600 hover:text-indigo-500">{{ $t('dashboard.view_all_m') }}</Link>
                </div>
                <div v-if="recentExpenses.length > 0" class="mt-4 divide-y divide-gray-100">
                    <div v-for="exp in recentExpenses" :key="exp.id" class="flex items-center justify-between py-3">
                        <div class="min-w-0 flex-1">
                            <Link :href="route('expenses.edit', exp.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                {{ exp.concept }}
                            </Link>
                            <p class="text-xs text-gray-500">
                                <span v-if="exp.category">[{{ exp.category.code }}] {{ exp.category.name }}</span>
                                <span v-else>{{ $t('common.no_category') }}</span>
                                &middot; {{ formatDate(exp.expense_date) }}
                            </p>
                        </div>
                        <div class="ml-4 text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(parseFloat(exp.total)) }}</p>
                            <p :class="['text-xs', exp.payment_status === 'paid' ? 'text-green-600' : 'text-amber-600']">
                                {{ statusLabels[exp.payment_status] || exp.payment_status }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="mt-4 text-sm text-gray-400">{{ $t('dashboard.no_expenses_yet') }}</p>
            </div>
        </div>
    </AppLayout>
</template>
