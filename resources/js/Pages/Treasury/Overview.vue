<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
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

interface Account {
    id: number;
    name: string;
    iban: string | null;
    current_balance: number;
}

interface CashFlowMonth {
    label: string;
    month: string;
    collections: number;
    payments: number;
}

interface Entry {
    id: number;
    entry_date: string;
    concept: string;
    amount: number;
    entry_type: string;
    bank_account_name: string;
    bank_account_id: number;
    notes: string | null;
    is_manual: boolean;
}

const props = defineProps<{
    accounts: Account[];
    totalBalance: number;
    collectionsThisMonth: number;
    paymentsThisMonth: number;
    netFlow: number;
    cashFlow: CashFlowMonth[];
    recentEntries: Entry[];
}>();

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const formatDate = (val: string) => {
    if (!val) return '';
    const d = new Date(val);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

// KPI cards
const kpiCards = computed(() => [
    {
        label: trans('treasury.total_balance'),
        value: props.totalBalance,
        color: props.totalBalance >= 0 ? 'text-indigo-700' : 'text-red-700',
        bgColor: props.totalBalance >= 0 ? 'bg-indigo-50' : 'bg-red-50',
        iconColor: props.totalBalance >= 0 ? 'text-indigo-400' : 'text-red-400',
        icon: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    },
    {
        label: trans('treasury.collections_this_month'),
        value: props.collectionsThisMonth,
        color: 'text-green-700',
        bgColor: 'bg-green-50',
        iconColor: 'text-green-400',
        icon: 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
    },
    {
        label: trans('treasury.payments_this_month'),
        value: props.paymentsThisMonth,
        color: 'text-red-700',
        bgColor: 'bg-red-50',
        iconColor: 'text-red-400',
        icon: 'M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181',
    },
    {
        label: trans('treasury.net_flow'),
        value: props.netFlow,
        color: props.netFlow >= 0 ? 'text-green-700' : 'text-red-700',
        bgColor: props.netFlow >= 0 ? 'bg-green-50' : 'bg-red-50',
        iconColor: props.netFlow >= 0 ? 'text-green-400' : 'text-red-400',
        icon: 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
    },
]);

// Chart
const chartData = computed(() => ({
    labels: props.cashFlow.map(m => m.label),
    datasets: [
        {
            label: trans('treasury.chart_collections'),
            data: props.cashFlow.map(m => m.collections),
            backgroundColor: 'rgba(34, 197, 94, 0.7)',
            borderRadius: 4,
        },
        {
            label: trans('treasury.chart_payments'),
            data: props.cashFlow.map(m => m.payments),
            backgroundColor: 'rgba(239, 68, 68, 0.5)',
            borderRadius: 4,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' as const },
        tooltip: {
            callbacks: {
                label: (ctx: any) => `${ctx.dataset.label}: ${formatCurrency(ctx.parsed.y)}`,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { callback: (value: any) => formatCurrency(value) },
        },
    },
};

// Entry type labels
const entryTypeLabel = (type: string): string => {
    const map: Record<string, string> = {
        collection: trans('treasury.type_collection'),
        payment: trans('treasury.type_payment'),
        manual: trans('treasury.type_manual'),
    };
    return map[type] || type;
};

const entryTypeBadgeClass = (type: string): string => {
    const map: Record<string, string> = {
        collection: 'bg-green-100 text-green-800',
        payment: 'bg-red-100 text-red-800',
        manual: 'bg-gray-100 text-gray-700',
    };
    return map[type] || 'bg-gray-100 text-gray-700';
};

// New entry modal
const showEntryModal = ref(false);
const editingEntry = ref<Entry | null>(null);

const entryForm = useForm({
    entry_date: new Date().toISOString().slice(0, 10),
    concept: '',
    amount: 0,
    bank_account_id: null as number | null,
    notes: '',
});

const openNewEntry = () => {
    editingEntry.value = null;
    entryForm.reset();
    entryForm.entry_date = new Date().toISOString().slice(0, 10);
    if (props.accounts.length > 0) {
        entryForm.bank_account_id = props.accounts[0].id;
    }
    showEntryModal.value = true;
};

const openEditEntry = (entry: Entry) => {
    editingEntry.value = entry;
    entryForm.entry_date = entry.entry_date;
    entryForm.concept = entry.concept;
    entryForm.amount = entry.amount;
    entryForm.bank_account_id = entry.bank_account_id;
    entryForm.notes = entry.notes || '';
    showEntryModal.value = true;
};

const submitEntry = () => {
    if (editingEntry.value) {
        entryForm.put(route('treasury.entries.update', editingEntry.value.id), {
            onSuccess: () => { showEntryModal.value = false; },
        });
    } else {
        entryForm.post(route('treasury.entries.store'), {
            onSuccess: () => { showEntryModal.value = false; },
        });
    }
};

// Delete entry
const deleteDialog = ref(false);
const deleteTarget = ref<Entry | null>(null);
const deleting = ref(false);

const confirmDeleteEntry = (entry: Entry) => {
    deleteTarget.value = entry;
    deleteDialog.value = true;
};

const executeDeleteEntry = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('treasury.entries.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('treasury.overview_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('treasury.overview_title') }}</h1>
        </template>

        <!-- No accounts CTA -->
        <div v-if="accounts.length === 0" class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ $t('treasury.no_accounts') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('treasury.configure_accounts') }}</p>
            <Link :href="route('settings.bank-accounts')" class="mt-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                {{ $t('treasury.configure_accounts') }}
            </Link>
        </div>

        <template v-else>
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="kpi in kpiCards" :key="kpi.label" class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
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

            <!-- Chart + Account cards -->
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Cash flow chart -->
                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">{{ $t('treasury.cash_flow_12m') }}</h3>
                    <div class="h-72">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Account cards -->
                <div class="space-y-4">
                    <div v-for="account in accounts" :key="account.id" class="rounded-lg bg-white p-4 shadow">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900">{{ account.name }}</h4>
                        </div>
                        <p v-if="account.iban" class="mt-1 text-xs text-gray-400">{{ account.iban.slice(0, 8) }}...{{ account.iban.slice(-4) }}</p>
                        <p class="mt-2 text-lg font-bold" :class="account.current_balance >= 0 ? 'text-green-700' : 'text-red-700'">
                            {{ formatCurrency(account.current_balance) }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $t('treasury.account_balance') }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent entries -->
            <div class="mt-6 rounded-lg bg-white p-6 shadow">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $t('treasury.recent_entries') }}</h3>
                    <button
                        @click="openNewEntry"
                        class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
                    >
                        <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                        </svg>
                        {{ $t('treasury.new_entry') }}
                    </button>
                </div>

                <div v-if="recentEntries.length > 0" class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.entry_date') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.concept') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.entry_type') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('treasury.amount') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.account') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="entry in recentEntries" :key="entry.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-700">{{ formatDate(entry.entry_date) }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ entry.concept }}</td>
                                <td class="px-3 py-2">
                                    <span :class="[entryTypeBadgeClass(entry.entry_type), 'inline-flex rounded-full px-2 py-0.5 text-xs font-medium']">
                                        {{ entryTypeLabel(entry.entry_type) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-right text-sm font-medium" :class="entry.amount >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ entry.amount >= 0 ? '+' : '' }}{{ formatCurrency(entry.amount) }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ entry.bank_account_name }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-right text-sm">
                                    <template v-if="entry.is_manual">
                                        <button @click="openEditEntry(entry)" class="text-indigo-600 hover:text-indigo-900 mr-2" :title="$t('common.edit')">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                        </button>
                                        <button @click="confirmDeleteEntry(entry)" class="text-red-600 hover:text-red-900" :title="$t('common.delete')">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="mt-4 text-sm text-gray-400">{{ $t('treasury.no_entries') }}</p>
            </div>
        </template>

        <!-- Entry Modal -->
        <Modal :show="showEntryModal" max-width="lg" @close="showEntryModal = false">
            <div class="relative z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-900">{{ editingEntry ? $t('treasury.edit_entry') : $t('treasury.new_entry') }}</h3>
                <form @submit.prevent="submitEntry" class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.entry_date') }}</label>
                            <input type="date" v-model="entryForm.entry_date" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                            <p v-if="entryForm.errors.entry_date" class="mt-1 text-xs text-red-600">{{ entryForm.errors.entry_date }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.amount') }}</label>
                            <input type="number" v-model.number="entryForm.amount" step="0.01" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                            <p v-if="entryForm.errors.amount" class="mt-1 text-xs text-red-600">{{ entryForm.errors.amount }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.concept') }}</label>
                        <input type="text" v-model="entryForm.concept" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                        <p v-if="entryForm.errors.concept" class="mt-1 text-xs text-red-600">{{ entryForm.errors.concept }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.account') }}</label>
                        <select v-model="entryForm.bank_account_id" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm">
                            <option :value="null" disabled>{{ $t('treasury.select_account') }}</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                        </select>
                        <p v-if="entryForm.errors.bank_account_id" class="mt-1 text-xs text-red-600">{{ entryForm.errors.bank_account_id }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.entry_notes') }}</label>
                        <textarea v-model="entryForm.notes" rows="2" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showEntryModal = false" class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">{{ $t('common.cancel') }}</button>
                        <button type="submit" :disabled="entryForm.processing" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50">
                            {{ editingEntry ? $t('common.save') : $t('common.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('treasury.delete_entry_title')"
            :message="trans('treasury.delete_entry_message', { concept: deleteTarget?.concept || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDeleteEntry"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
