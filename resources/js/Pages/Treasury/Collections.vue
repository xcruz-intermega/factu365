<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

interface CollectionItem {
    id: number;
    due_date: string;
    amount: number;
    is_overdue: boolean;
    days_overdue: number;
    document_number: string;
    document_id: number;
    document_type: string;
    client_name: string;
    client_nif: string;
}

const props = defineProps<{
    items: CollectionItem[];
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
    router.get(route('treasury.collections'), {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('treasury.collections'), {}, { preserveState: true, replace: true });
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const formatDate = (val: string) => {
    if (!val) return '';
    const d = new Date(val);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const printPage = () => window.print();
</script>

<template>
    <Head :title="$t('treasury.collections_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('treasury.collections_title') }}</h1>
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
                <button @click="printPage" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                    </svg>
                    {{ $t('common.print') }}
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg bg-white shadow">
            <div v-if="items.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.due_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.document_number') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.client') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('treasury.nif') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('treasury.amount') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('treasury.days_overdue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in items" :key="item.id" :class="item.is_overdue ? 'bg-red-50' : ''">
                            <td class="whitespace-nowrap px-4 py-3 text-sm" :class="item.is_overdue ? 'font-medium text-red-700' : 'text-gray-700'">
                                {{ formatDate(item.due_date) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a :href="route('documents.edit', { type: item.document_type, document: item.document_id })" class="font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ item.document_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ item.client_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ item.client_nif }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.amount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm" :class="item.is_overdue ? 'font-medium text-red-700' : 'text-gray-400'">
                                {{ item.days_overdue > 0 ? item.days_overdue : '-' }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $t('treasury.total_pending') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">{{ formatCurrency(totalPending) }}</td>
                            <td></td>
                        </tr>
                        <tr v-if="totalOverdue > 0">
                            <td colspan="4" class="px-4 py-3 text-sm font-semibold text-red-700">{{ $t('treasury.total_overdue') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-red-700">{{ formatCurrency(totalOverdue) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div v-else class="py-12 text-center text-sm text-gray-500">
                {{ $t('treasury.no_pending_collections') }}
            </div>
        </div>
    </AppLayout>
</template>
