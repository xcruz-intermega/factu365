<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import DocumentForm from './Partials/DocumentForm.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import type { LineInput } from '@/composables/useTaxCalculator';

interface Props {
    document: any;
    documentType: string;
    documentTypeLabel: string;
    clients: any[];
    products: any[];
    series: any[];
    canFinalize: boolean;
    canEdit: boolean;
    canConvert: boolean;
    conversionTargets: string[];
    nextConversionType: string | null;
    paymentTemplates: any[];
}

const props = defineProps<Props>();

const doc = computed(() => props.document);

const form = useForm({
    document_type: props.document.document_type,
    invoice_type: props.document.invoice_type,
    title: props.document.title || '',
    client_id: props.document.client_id,
    series_id: props.document.series_id,
    issue_date: props.document.issue_date?.split('T')[0] ?? '',
    due_date: props.document.due_date?.split('T')[0] ?? '',
    operation_date: props.document.operation_date?.split('T')[0] ?? '',
    global_discount_percent: Number(props.document.global_discount_percent || 0),
    regime_key: props.document.regime_key || '01',
    notes: props.document.notes || '',
    footer_text: props.document.footer_text || '',
    corrected_document_id: props.document.corrected_document_id,
    rectificative_type: props.document.rectificative_type,
    due_dates: (props.document.due_dates || []).map((dd: any) => ({
        due_date: dd.due_date?.split('T')[0] ?? '',
        amount: Number(dd.amount),
        percentage: Number(dd.percentage),
    })),
    lines: (props.document.lines || []).map((l: any): LineInput => ({
        product_id: l.product_id,
        concept: l.concept,
        description: l.description || '',
        quantity: Number(l.quantity),
        unit_price: Number(l.unit_price),
        unit: l.unit || '',
        discount_percent: Number(l.discount_percent || 0),
        vat_rate: Number(l.vat_rate),
        exemption_code: l.exemption_code || '',
        irpf_rate: Number(l.irpf_rate || 0),
        surcharge_rate: Number(l.surcharge_rate || 0),
    })),
});

const submit = () => {
    form.put(route('documents.update', { type: props.documentType, document: doc.value.id }));
};

// Finalize
const finalizeDialog = ref(false);
const finalizing = ref(false);

const doFinalize = () => {
    finalizing.value = true;
    router.post(route('documents.finalize', { type: props.documentType, document: doc.value.id }), {}, {
        onFinish: () => {
            finalizing.value = false;
            finalizeDialog.value = false;
        },
    });
};

// Convert
const converting = ref(false);

const doConvert = (targetType?: string) => {
    converting.value = true;
    router.post(route('documents.convert', { type: props.documentType, document: doc.value.id }), {
        target_type: targetType || undefined,
    }, {
        onFinish: () => { converting.value = false; },
    });
};

const isNonFiscal = ['quote', 'delivery_note'].includes(props.documentType);
const isAccountable = ['invoice', 'rectificative', 'purchase_invoice'].includes(props.documentType);

const toggleAccounted = () => {
    router.post(route('documents.toggle-accounted', { type: props.documentType, document: doc.value.id }), {}, {
        preserveScroll: true,
    });
};

// Rectificative
const creatingRect = ref(false);

const doCreateRectificative = () => {
    creatingRect.value = true;
    router.post(route('documents.rectificative', { document: doc.value.id }), {}, {
        onFinish: () => { creatingRect.value = false; },
    });
};

// Status update
const updateStatus = (status: string) => {
    router.patch(route('documents.update-status', { type: props.documentType, document: doc.value.id }), {
        status,
    });
};

// PDF actions
const downloadPdf = () => {
    window.location.href = route('documents.download-pdf', { type: props.documentType, document: doc.value.id });
};

const previewPdf = () => {
    window.open(route('documents.preview-pdf', { type: props.documentType, document: doc.value.id }), '_blank');
};

// FacturaE export
const canExportFacturae = computed(() =>
    ['invoice', 'rectificative'].includes(props.documentType) && doc.value.status !== 'draft'
);

const downloadFacturae = () => {
    window.location.href = route('documents.download-facturae', { type: props.documentType, document: doc.value.id });
};

// Email dialog
const emailDialog = ref(false);
const emailForm = useForm({
    email: props.document.client?.email || '',
    cc: '',
    bcc: '',
    subject: '',
    message: '',
    attachments: 'pdf',
});
const sendingEmail = ref(false);

const openEmailDialog = () => {
    emailForm.email = doc.value.client?.email || '';
    emailForm.cc = '';
    emailForm.bcc = '';
    emailForm.subject = `${props.documentTypeLabel} ${doc.value.number}`;
    emailForm.message = trans('documents.email_default_message', { type: props.documentTypeLabel, number: doc.value.number });
    emailForm.attachments = 'pdf';
    emailDialog.value = true;
};

const doSendEmail = () => {
    sendingEmail.value = true;
    router.post(route('documents.send-email', { type: props.documentType, document: doc.value.id }), {
        email: emailForm.email,
        cc: emailForm.cc || undefined,
        bcc: emailForm.bcc || undefined,
        subject: emailForm.subject,
        message: emailForm.message,
        attachments: emailForm.attachments,
    }, {
        onFinish: () => {
            sendingEmail.value = false;
            emailDialog.value = false;
        },
    });
};

const statusColors: Record<string, 'gray' | 'green' | 'blue' | 'yellow' | 'red' | 'indigo' | 'purple'> = {
    draft: 'gray',
    finalized: 'blue',
    sent: 'indigo',
    paid: 'green',
    partial: 'yellow',
    overdue: 'red',
    cancelled: 'red',
    registered: 'blue',
    created: 'blue',
    accepted: 'green',
    rejected: 'red',
    converted: 'purple',
};

const statusLabels = computed<Record<string, string>>(() => ({
    draft: trans('common.status_draft'),
    finalized: trans('common.status_finalized'),
    sent: trans('common.status_sent'),
    paid: trans('common.status_paid'),
    partial: trans('common.status_partial'),
    overdue: trans('common.status_overdue'),
    cancelled: trans('common.status_cancelled'),
    registered: trans('common.status_registered'),
    created: trans('common.status_created'),
    accepted: trans('common.status_accepted'),
    rejected: trans('common.status_rejected'),
    converted: trans('common.status_converted'),
}));

const conversionLabels = computed<Record<string, string>>(() => ({
    invoice: trans('documents.to_invoice'),
    delivery_note: trans('documents.to_delivery_note'),
    quote: trans('documents.to_quote'),
}));

const formatCurrency = (val: number | string) => {
    const num = typeof val === 'string' ? parseFloat(val) : val;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(num);
};
</script>

<template>
    <Head :title="`${documentTypeLabel} ${doc.number || $t('common.status_draft')}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('documents.index', { type: documentType })"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-lg font-semibold text-gray-900">
                    {{ documentTypeLabel }}
                    <span v-if="doc.number" class="text-indigo-600">{{ doc.number }}</span>
                </h1>
                <Badge :color="statusColors[doc.status] || 'gray'">
                    {{ statusLabels[doc.status] || doc.status }}
                </Badge>
                <Badge v-if="isAccountable && doc.accounted" color="green">
                    {{ $t('documents.filter_accounted') }}
                </Badge>
                <Badge v-if="doc.recurring_invoice_id && doc.recurring_invoice" color="purple">
                    {{ $t('recurring.badge_from_recurring', { name: doc.recurring_invoice?.name || '' }) }}
                </Badge>
            </div>
        </template>

        <!-- Action bar -->
        <div v-if="!canEdit || isNonFiscal" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-gray-700">{{ $t('documents.actions_label') }}</span>

                <!-- Non-fiscal status changes (quotes) -->
                <template v-if="isNonFiscal">
                    <template v-if="doc.status === 'created'">
                        <button @click="updateStatus('sent')" class="rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                            {{ $t('documents.mark_sent') }}
                        </button>
                    </template>
                    <template v-if="documentType === 'quote' && ['created', 'sent'].includes(doc.status)">
                        <button @click="updateStatus('accepted')" class="rounded-md bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-100">
                            {{ $t('documents.accept') }}
                        </button>
                        <button @click="updateStatus('rejected')" class="rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100">
                            {{ $t('documents.reject') }}
                        </button>
                    </template>
                </template>

                <!-- Fiscal status changes -->
                <template v-if="!isNonFiscal">
                    <template v-if="doc.status === 'finalized'">
                        <button @click="updateStatus('sent')" class="rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                            {{ $t('documents.mark_sent_f') }}
                        </button>
                        <button @click="updateStatus('paid')" class="rounded-md bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-100">
                            {{ $t('documents.mark_paid') }}
                        </button>
                    </template>
                    <template v-if="doc.status === 'sent'">
                        <button @click="updateStatus('paid')" class="rounded-md bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-100">
                            {{ $t('documents.mark_paid') }}
                        </button>
                        <button @click="updateStatus('partial')" class="rounded-md bg-yellow-50 px-3 py-1.5 text-sm font-medium text-yellow-700 hover:bg-yellow-100">
                            {{ $t('documents.partial_payment') }}
                        </button>
                        <button @click="updateStatus('overdue')" class="rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100">
                            {{ $t('documents.mark_overdue') }}
                        </button>
                    </template>
                </template>

                <!-- Convert (with target type options) -->
                <template v-if="canConvert && conversionTargets.length > 0">
                    <button
                        v-for="target in conversionTargets"
                        :key="target"
                        @click="doConvert(target)"
                        :disabled="converting"
                        class="rounded-md bg-purple-50 px-3 py-1.5 text-sm font-medium text-purple-700 hover:bg-purple-100 disabled:opacity-50"
                    >
                        {{ $t('documents.convert_to', { target: conversionLabels[target] || target }) }}
                    </button>
                </template>

                <!-- Create rectificative -->
                <button
                    v-if="documentType === 'invoice' && doc.status !== 'draft'"
                    @click="doCreateRectificative"
                    :disabled="creatingRect"
                    class="rounded-md bg-orange-50 px-3 py-1.5 text-sm font-medium text-orange-700 hover:bg-orange-100 disabled:opacity-50"
                >
                    {{ $t('documents.create_rectificative') }}
                </button>

                <!-- Cancel -->
                <button
                    v-if="!['draft', 'cancelled', 'paid', 'converted', 'rejected'].includes(doc.status)"
                    @click="updateStatus('cancelled')"
                    class="rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100"
                >
                    {{ $t('documents.cancel_doc') }}
                </button>

                <!-- Toggle accounted -->
                <button
                    v-if="isAccountable"
                    @click="toggleAccounted"
                    class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium"
                    :class="doc.accounted
                        ? 'bg-green-50 text-green-700 hover:bg-green-100'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                >
                    <svg v-if="doc.accounted" class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ doc.accounted ? $t('documents.filter_accounted') : $t('documents.filter_not_accounted') }}
                </button>

                <span class="mx-1 text-gray-300">|</span>

                <!-- PDF actions -->
                <button @click="downloadPdf" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    {{ $t('common.download_pdf') }}
                </button>
                <button @click="previewPdf" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $t('common.preview') }}
                </button>
                <button v-if="canExportFacturae" @click="downloadFacturae" class="inline-flex items-center rounded-md bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-700 hover:bg-emerald-100">
                    <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                    </svg>
                    {{ $t('documents.download_facturae') }}
                </button>
                <button @click="openEmailDialog" class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-100">
                    <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    {{ $t('common.send_email') }}
                </button>
            </div>
        </div>

        <!-- Editable form (drafts + non-fiscal editable) -->
        <template v-if="canEdit">
            <DocumentForm
                :form="form"
                :document-type="documentType"
                :document-type-label="documentTypeLabel"
                :clients="clients"
                :products="products"
                :series="series"
                :payment-templates="paymentTemplates"
                :is-edit="true"
                @submit="submit"
            />

            <!-- Finalize button (only for fiscal types) -->
            <div v-if="canFinalize" class="mt-4 flex justify-end">
                <button
                    type="button"
                    @click="finalizeDialog = true"
                    class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                >
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $t('documents.finalize_document', { type: documentTypeLabel.toLowerCase() }) }}
                </button>
            </div>
        </template>

        <!-- Read-only summary -->
        <template v-else>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <span class="text-sm text-gray-500">{{ $t('documents.client') }}</span>
                        <p class="font-medium">{{ doc.client?.trade_name || doc.client?.legal_name || '—' }}</p>
                        <p v-if="doc.client?.nif" class="text-sm text-gray-500">{{ doc.client.nif }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ $t('documents.issue_date') }}</span>
                        <p class="font-medium">{{ doc.issue_date?.split('T')[0] }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ $t('common.total') }}</span>
                        <p class="text-xl font-bold text-indigo-700">{{ formatCurrency(doc.total) }}</p>
                    </div>
                </div>

                <!-- Lines summary -->
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_concept') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_quantity') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_price') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.vat') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="line in doc.lines" :key="line.id">
                                <td class="px-4 py-2 text-sm">{{ line.concept }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ Number(line.quantity) }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ formatCurrency(line.unit_price) }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ Number(line.vat_rate) }}%</td>
                                <td class="px-4 py-2 text-right text-sm font-medium">{{ formatCurrency(line.line_total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="mt-4 flex justify-end">
                    <div class="w-64 space-y-1 text-sm">
                        <div class="flex justify-between"><span>{{ $t('common.tax_base') }}</span><span>{{ formatCurrency(doc.tax_base) }}</span></div>
                        <div class="flex justify-between"><span>{{ $t('common.vat') }}</span><span>{{ formatCurrency(doc.total_vat) }}</span></div>
                        <div v-if="Number(doc.total_irpf) > 0" class="flex justify-between text-red-600"><span>{{ $t('common.irpf') }}</span><span>-{{ formatCurrency(doc.total_irpf) }}</span></div>
                        <div v-if="Number(doc.total_surcharge) > 0" class="flex justify-between"><span>{{ $t('common.surcharge') }}</span><span>{{ formatCurrency(doc.total_surcharge) }}</span></div>
                        <div class="flex justify-between border-t pt-1 text-lg font-bold"><span>{{ $t('common.total') }}</span><span class="text-indigo-700">{{ formatCurrency(doc.total) }}</span></div>
                    </div>
                </div>

                <!-- Due dates -->
                <div v-if="doc.due_dates && doc.due_dates.length > 0" class="mt-6">
                    <h4 class="mb-2 text-sm font-semibold text-gray-900">{{ $t('documents.due_dates') }}</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_date') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_percent') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_amount') }}</th>
                                <th class="px-4 py-2 text-center text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_status') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="dd in doc.due_dates" :key="dd.id">
                                <td class="px-4 py-2 text-sm">{{ dd.due_date?.split('T')[0] }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ Number(dd.percentage) }}%</td>
                                <td class="px-4 py-2 text-right text-sm font-medium">{{ formatCurrency(dd.amount) }}</td>
                                <td class="px-4 py-2 text-center">
                                    <Badge :color="dd.payment_status === 'paid' ? 'green' : 'yellow'">
                                        {{ dd.payment_status === 'paid' ? $t('documents.paid') : $t('common.status_pending') }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <button
                                        @click="router.post(route('documents.due-date.toggle-paid', { type: documentType, document: doc.id, dueDate: dd.id }))"
                                        class="text-sm"
                                        :class="dd.payment_status === 'paid' ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800'"
                                    >
                                        {{ dd.payment_status === 'paid' ? $t('documents.unmark') : $t('documents.mark_due_paid') }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- Finalize Confirmation -->
        <ConfirmDialog
            :show="finalizeDialog"
            :title="trans('documents.finalize_title')"
            :message="trans('documents.finalize_message')"
            :confirm-label="trans('common.finalize')"
            :processing="finalizing"
            @confirm="doFinalize"
            @cancel="finalizeDialog = false"
        />

        <!-- Send Email Dialog -->
        <ConfirmDialog
            :show="emailDialog"
            :title="trans('documents.email_title')"
            :confirm-label="trans('common.send')"
            :processing="sendingEmail"
            @confirm="doSendEmail"
            @cancel="emailDialog = false"
        >
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.email_to') }}</label>
                    <input
                        type="email"
                        v-model="emailForm.email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        :placeholder="$t('documents.email_to_placeholder')"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.email_cc') }}</label>
                    <input
                        type="text"
                        v-model="emailForm.cc"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        :placeholder="$t('documents.email_cc_placeholder')"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.email_bcc') }}</label>
                    <input
                        type="text"
                        v-model="emailForm.bcc"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        :placeholder="$t('documents.email_bcc_placeholder')"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.email_subject') }}</label>
                    <input
                        type="text"
                        v-model="emailForm.subject"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.email_message') }}</label>
                    <textarea
                        v-model="emailForm.message"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                </div>
                <div v-if="canExportFacturae">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.email_attachments') }}</label>
                    <div class="mt-1 flex items-center gap-4">
                        <label class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                            <input type="radio" v-model="emailForm.attachments" value="pdf" class="text-indigo-600 focus:ring-indigo-500" />
                            {{ $t('documents.email_attach_pdf') }}
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                            <input type="radio" v-model="emailForm.attachments" value="facturae" class="text-indigo-600 focus:ring-indigo-500" />
                            {{ $t('documents.email_attach_facturae') }}
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                            <input type="radio" v-model="emailForm.attachments" value="both" class="text-indigo-600 focus:ring-indigo-500" />
                            {{ $t('documents.email_attach_both') }}
                        </label>
                    </div>
                </div>
            </div>
        </ConfirmDialog>
    </AppLayout>
</template>
