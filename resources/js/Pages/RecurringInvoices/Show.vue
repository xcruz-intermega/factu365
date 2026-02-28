<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps<{
    recurringInvoice: any;
}>();

const ri = computed(() => props.recurringInvoice);

const statusColors: Record<string, 'green' | 'yellow' | 'gray'> = {
    active: 'green',
    paused: 'yellow',
    finished: 'gray',
};

const statusLabels = computed<Record<string, string>>(() => ({
    active: trans('recurring.status_active'),
    paused: trans('recurring.status_paused'),
    finished: trans('recurring.status_finished'),
}));

const unitLabels = computed<Record<string, string>>(() => ({
    day: trans('recurring.unit_day'),
    week: trans('recurring.unit_week'),
    month: trans('recurring.unit_month'),
    year: trans('recurring.unit_year'),
}));

const docStatusLabels = computed<Record<string, string>>(() => ({
    draft: trans('common.status_draft'),
    finalized: trans('common.status_finalized'),
    sent: trans('common.status_sent'),
    paid: trans('common.status_paid'),
    partial: trans('common.status_partial'),
    overdue: trans('common.status_overdue'),
    cancelled: trans('common.status_cancelled'),
}));

const docStatusColors: Record<string, 'gray' | 'green' | 'blue' | 'yellow' | 'red' | 'indigo'> = {
    draft: 'gray',
    finalized: 'blue',
    sent: 'indigo',
    paid: 'green',
    partial: 'yellow',
    overdue: 'red',
    cancelled: 'red',
};

const formatCurrency = (val: number | string) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(Number(val));
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-ES');
};

const formatFrequency = computed(() => {
    return trans('recurring.frequency_every', {
        value: ri.value.interval_value,
        unit: unitLabels.value[ri.value.interval_unit] || ri.value.interval_unit,
    });
});

// Toggle status
const toggleStatus = () => {
    router.post(route('recurring-invoices.toggle-status', ri.value.id), {}, {
        preserveScroll: true,
    });
};

// Generate now
const generateDialog = ref(false);
const generating = ref(false);

const executeGenerate = () => {
    generating.value = true;
    router.post(route('recurring-invoices.generate-now', ri.value.id), {}, {
        onFinish: () => {
            generating.value = false;
            generateDialog.value = false;
        },
    });
};

// Delete
const deleteDialog = ref(false);
const deleting = ref(false);

const executeDelete = () => {
    deleting.value = true;
    router.delete(route('recurring-invoices.destroy', ri.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`${$t('recurring.title_show')} - ${ri.name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('recurring-invoices.index')"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-lg font-semibold text-gray-900">{{ ri.name }}</h1>
                <Badge :color="statusColors[ri.status] || 'gray'">
                    {{ statusLabels[ri.status] || ri.status }}
                </Badge>
            </div>
            <div class="ml-4 flex items-center gap-2">
                <Link
                    :href="route('recurring-invoices.edit', ri.id)"
                    class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50"
                >
                    {{ $t('recurring.btn_edit') }}
                </Link>
                <button
                    v-if="ri.status !== 'finished'"
                    @click="toggleStatus"
                    class="rounded-md bg-white px-3 py-1.5 text-sm font-medium shadow-sm ring-1 ring-gray-300 hover:bg-gray-50"
                    :class="ri.status === 'active' ? 'text-yellow-700' : 'text-green-700'"
                >
                    {{ ri.status === 'active' ? $t('recurring.btn_pause') : $t('recurring.btn_resume') }}
                </button>
                <button
                    v-if="ri.status === 'active'"
                    @click="generateDialog = true"
                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-500"
                >
                    {{ $t('recurring.btn_generate_now') }}
                </button>
                <button
                    @click="deleteDialog = true"
                    class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-red-700 shadow-sm ring-1 ring-gray-300 hover:bg-red-50"
                >
                    {{ $t('recurring.btn_delete') }}
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Configuration -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ $t('recurring.show_config') }}</h2>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_client') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ri.client?.trade_name || ri.client?.legal_name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.col_frequency') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatFrequency }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_next_issue_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <span v-if="ri.status !== 'finished'">{{ formatDate(ri.next_issue_date) }}</span>
                            <span v-else class="text-gray-400">—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_start_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(ri.start_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_end_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ri.end_date ? formatDate(ri.end_date) : $t('recurring.show_unlimited') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_occurrences_count') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ ri.occurrences_count }}{{ ri.max_occurrences ? ` / ${ri.max_occurrences}` : '' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_series') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ri.series?.prefix || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_payment_template') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ri.payment_template?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_auto_finalize') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <Badge :color="ri.auto_finalize ? 'green' : 'gray'">
                                {{ ri.auto_finalize ? $t('common.yes') : $t('common.no') }}
                            </Badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_auto_send_email') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <Badge :color="ri.auto_send_email ? 'green' : 'gray'">
                                {{ ri.auto_send_email ? $t('common.yes') : $t('common.no') }}
                            </Badge>
                        </dd>
                    </div>
                    <div v-if="ri.created_by_user">
                        <dt class="text-sm font-medium text-gray-500">{{ $t('recurring.label_created_by') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ri.created_by_user?.name || '—' }}</dd>
                    </div>
                </dl>

                <!-- Lines preview -->
                <div v-if="ri.lines?.length" class="mt-6">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700">{{ $t('recurring.section_lines') }}</h3>
                    <div class="overflow-x-auto rounded-md border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">{{ $t('documents.col_concept') }}</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500">{{ $t('documents.col_qty') }}</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500">{{ $t('documents.col_price') }}</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500">{{ $t('documents.col_vat') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="line in ri.lines" :key="line.id">
                                    <td class="px-3 py-2">
                                        {{ line.concept }}
                                        <span v-if="line.product" class="text-xs text-gray-400"> ({{ line.product.reference }})</span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono">{{ Number(line.quantity) }}</td>
                                    <td class="px-3 py-2 text-right font-mono">{{ formatCurrency(line.unit_price) }}</td>
                                    <td class="px-3 py-2 text-right">{{ Number(line.vat_rate) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Generated invoices history -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ $t('recurring.show_history') }}</h2>

                <div v-if="!ri.generated_documents?.length" class="py-8 text-center text-sm text-gray-500">
                    {{ $t('recurring.show_no_documents') }}
                </div>

                <div v-else class="overflow-x-auto rounded-md border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">{{ $t('recurring.show_document_number') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">{{ $t('recurring.show_document_date') }}</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-500">{{ $t('recurring.show_document_total') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">{{ $t('recurring.show_document_status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="doc in ri.generated_documents" :key="doc.id">
                                <td class="px-4 py-2">
                                    <Link
                                        :href="route('documents.edit', { type: 'invoice', document: doc.id })"
                                        class="font-medium text-indigo-600 hover:text-indigo-900"
                                    >
                                        {{ doc.number || `#${doc.id}` }}
                                    </Link>
                                </td>
                                <td class="px-4 py-2">{{ formatDate(doc.issue_date) }}</td>
                                <td class="px-4 py-2 text-right font-mono">{{ formatCurrency(doc.total) }}</td>
                                <td class="px-4 py-2">
                                    <Badge :color="docStatusColors[doc.status] || 'gray'">
                                        {{ docStatusLabels[doc.status] || doc.status }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Generate Confirmation -->
        <ConfirmDialog
            :show="generateDialog"
            :title="trans('recurring.confirm_generate_title')"
            :message="trans('recurring.confirm_generate_message', { name: ri.name || '' })"
            :confirm-label="trans('recurring.btn_generate_now')"
            :processing="generating"
            @confirm="executeGenerate"
            @cancel="generateDialog = false"
        />

        <!-- Delete Confirmation -->
        <ConfirmDialog
            :show="deleteDialog"
            :title="trans('recurring.confirm_delete_title')"
            :message="trans('recurring.confirm_delete_message', { name: ri.name || '' })"
            :confirm-label="trans('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
