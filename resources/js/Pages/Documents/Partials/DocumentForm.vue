<script setup lang="ts">
import { computed, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';
import type { InertiaForm } from '@inertiajs/vue3';
import LineEditor from './LineEditor.vue';
import TotalsSummary from './TotalsSummary.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import type { SearchSelectOption } from '@/Components/SearchSelect.vue';
import { calculateDocument, type LineInput, type DocumentTotals } from '@/composables/useTaxCalculator';
import { resolveClientDiscount, type ClientDiscountData } from '@/composables/useClientDiscountResolver';

interface DueDateInput {
    due_date: string;
    amount: number;
    percentage: number;
}

interface PaymentTemplateLine {
    days_from_issue: number;
    percentage: number;
}

interface PaymentTemplateData {
    id: number;
    name: string;
    lines: PaymentTemplateLine[];
}

interface Client {
    id: number;
    legal_name: string;
    trade_name: string | null;
    nif: string;
    type: string;
    payment_terms_days: number | null;
    payment_template_id: number | null;
    discounts?: ClientDiscountData[];
}

interface Product {
    id: number;
    name: string;
    reference: string;
    unit_price: number;
    vat_rate: number;
    exemption_code: string | null;
    irpf_applicable: boolean;
    unit: string | null;
    type: string;
    product_family_id: number | null;
    track_stock: boolean;
    stock_quantity: number;
    minimum_stock: number;
    allow_negative_stock: boolean;
    stock_mode: string;
    components?: Array<{
        id: number;
        component_product_id: number;
        quantity: string;
        component: {
            id: number;
            name: string;
            reference: string;
            unit_price: number;
            vat_rate: number;
            exemption_code: string | null;
            irpf_applicable: boolean;
            unit: string | null;
        };
    }>;
}

interface Series {
    id: number;
    prefix: string;
    fiscal_year: number;
    is_default: boolean;
}

const props = defineProps<{
    form: InertiaForm<any>;
    documentType: string;
    documentTypeLabel: string;
    clients: Client[];
    products: Product[];
    series: Series[];
    paymentTemplates?: PaymentTemplateData[];
    isEdit?: boolean;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const totals = computed<DocumentTotals>(() => {
    return calculateDocument(props.form.lines as LineInput[], props.form.global_discount_percent || 0);
});

const isInvoiceType = computed(() =>
    ['invoice', 'rectificative'].includes(props.documentType)
);

const isNonFiscal = computed(() =>
    ['quote', 'delivery_note'].includes(props.documentType)
);

const isQuote = computed(() => props.documentType === 'quote');

// Due dates management
const applyPaymentTemplate = (templateId: number | null) => {
    if (!templateId || !props.paymentTemplates) {
        props.form.due_dates = [];
        return;
    }
    const template = props.paymentTemplates.find(t => t.id === templateId);
    if (!template) return;

    const issueDate = props.form.issue_date ? new Date(props.form.issue_date) : new Date();
    const total = totals.value.total;

    let remaining = total;
    const dueDates: DueDateInput[] = template.lines.map((line, index) => {
        const date = new Date(issueDate);
        date.setDate(date.getDate() + line.days_from_issue);
        const isLast = index === template.lines.length - 1;
        const amount = isLast ? remaining : Math.round(total * line.percentage / 100 * 100) / 100;
        remaining -= amount;
        return {
            due_date: date.toISOString().split('T')[0],
            amount,
            percentage: line.percentage,
        };
    });

    props.form.due_dates = dueDates;
    if (dueDates.length > 0) {
        props.form.due_date = dueDates[0].due_date;
    }
};

const addDueDate = () => {
    if (!props.form.due_dates) props.form.due_dates = [];
    props.form.due_dates.push({
        due_date: props.form.issue_date || new Date().toISOString().split('T')[0],
        amount: 0,
        percentage: 0,
    });
};

const removeDueDate = (index: number) => {
    props.form.due_dates.splice(index, 1);
};

const invoiceTypes = computed(() => {
    if (props.documentType === 'rectificative') {
        return [
            { value: 'R1', label: trans('common.invoice_type_r1') },
            { value: 'R2', label: trans('common.invoice_type_r2') },
            { value: 'R3', label: trans('common.invoice_type_r3') },
            { value: 'R4', label: trans('common.invoice_type_r4') },
            { value: 'R5', label: trans('common.invoice_type_r5') },
        ];
    }
    return [
        { value: 'F1', label: trans('common.invoice_type_f1') },
        { value: 'F2', label: trans('common.invoice_type_f2') },
        { value: 'F3', label: trans('common.invoice_type_f3') },
    ];
});

const selectedClientDiscounts = computed<ClientDiscountData[]>(() => {
    if (!props.form.client_id) return [];
    const client = props.clients.find(c => c.id === props.form.client_id);
    return client?.discounts ?? [];
});

const updateLines = (lines: LineInput[]) => {
    props.form.lines = lines;
};

const clientOptions = computed<SearchSelectOption[]>(() =>
    props.clients.map(c => ({
        value: c.id,
        label: c.trade_name || c.legal_name,
        sublabel: c.nif,
    }))
);

const todayStr = new Date().toISOString().split('T')[0];
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-6">
        <!-- Header section -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('documents.general_data') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Client -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.client') }}</label>
                    <div class="mt-1">
                        <SearchSelect
                            v-model="form.client_id"
                            :options="clientOptions"
                            :placeholder="$t('documents.search_client')"
                            :has-error="!!form.errors.client_id"
                        />
                    </div>
                    <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}</p>
                </div>

                <!-- Title (only for quotes) -->
                <div v-if="isQuote" class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.quote_title') }}</label>
                    <input
                        type="text"
                        v-model="form.title"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :placeholder="$t('documents.quote_title_placeholder')"
                    />
                    <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                </div>

                <!-- Invoice type (only for invoices/rectificativas) -->
                <div v-if="isInvoiceType">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.invoice_type') }}</label>
                    <select
                        v-model="form.invoice_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="t in invoiceTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>

                <!-- Series (hidden for non-fiscal, they auto-assign) -->
                <div v-if="series.length > 1 && !isNonFiscal">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.series') }}</label>
                    <select
                        v-model="form.series_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">{{ $t('common.default') }}</option>
                        <option v-for="s in series" :key="s.id" :value="s.id">{{ s.prefix }} ({{ s.fiscal_year }})</option>
                    </select>
                </div>

                <!-- Issue date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.issue_date_label') }}</label>
                    <input
                        type="date"
                        v-model="form.issue_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.issue_date }"
                    />
                    <p v-if="form.errors.issue_date" class="mt-1 text-sm text-red-600">{{ form.errors.issue_date }}</p>
                </div>

                <!-- Due date (simple, hidden if using due_dates) -->
                <div v-if="!form.due_dates || form.due_dates.length === 0">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.due_date') }}</label>
                    <input
                        type="date"
                        v-model="form.due_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.due_date }"
                    />
                    <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
                </div>

                <!-- Operation date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.operation_date') }}</label>
                    <input
                        type="date"
                        v-model="form.operation_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <!-- Rectificative type (only for rectificatives) -->
                <div v-if="documentType === 'rectificative'">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('documents.rectification_method') }}</label>
                    <select
                        v-model="form.rectificative_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="substitution">{{ $t('documents.rectification_substitution') }}</option>
                        <option value="differences">{{ $t('documents.rectification_differences') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Lines section -->
        <LineEditor
            :lines="form.lines"
            :products="products"
            :errors="form.errors"
            :client-discounts="selectedClientDiscounts"
            @update:lines="updateLines"
        />

        <!-- Due dates section -->
        <div v-if="paymentTemplates && paymentTemplates.length > 0" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('documents.due_dates') }}</h3>
                <div class="flex items-center gap-3">
                    <select
                        @change="applyPaymentTemplate(Number(($event.target as HTMLSelectElement).value) || null)"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">{{ $t('documents.select_template') }}</option>
                        <option v-for="tpl in paymentTemplates" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                    </select>
                    <button type="button" @click="addDueDate" class="text-sm text-indigo-600 hover:text-indigo-800">{{ $t('documents.add_due_date') }}</button>
                </div>
            </div>
            <div v-if="form.due_dates && form.due_dates.length > 0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_date') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_percent') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('documents.col_amount') }}</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(dd, i) in form.due_dates" :key="i">
                            <td class="px-3 py-2">
                                <input type="date" v-model="dd.due_date" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" v-model.number="dd.percentage" min="0" max="100" step="0.01" class="block w-20 rounded-md border-gray-300 text-sm text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" v-model.number="dd.amount" min="0" step="0.01" class="block w-28 rounded-md border-gray-300 text-sm text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </td>
                            <td class="px-3 py-2">
                                <button type="button" @click="removeDueDate(i)" class="text-red-500 hover:text-red-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-sm text-gray-500">{{ $t('documents.no_due_dates') }}</p>
        </div>

        <!-- Bottom section: Notes + Totals side by side -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Notes -->
            <div class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('documents.notes_footer') }}</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $t('documents.internal_notes') }}</label>
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :placeholder="$t('documents.internal_notes_placeholder')"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $t('documents.document_footer') }}</label>
                            <textarea
                                v-model="form.footer_text"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :placeholder="$t('documents.footer_placeholder')"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <TotalsSummary
                :totals="totals"
                :global-discount-percent="form.global_discount_percent"
                @update:global-discount-percent="(v: number) => form.global_discount_percent = v"
            />
        </div>

        <!-- Validation error summary -->
        <div v-if="form.errors.lines" class="rounded-md bg-red-50 p-3">
            <p class="text-sm text-red-700">{{ form.errors.lines }}</p>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
            >
                {{ form.processing ? $t('common.saving') : (isEdit ? $t('common.save_changes') : (isNonFiscal ? $t('documents.create_document', { type: documentTypeLabel.toLowerCase() }) : $t('documents.create_draft'))) }}
            </button>
        </div>
    </form>
</template>
