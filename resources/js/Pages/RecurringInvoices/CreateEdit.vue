<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import LineEditor from '@/Pages/Documents/Partials/LineEditor.vue';
import TotalsSummary from '@/Pages/Documents/Partials/TotalsSummary.vue';
import { calculateDocument } from '@/composables/useTaxCalculator';
import type { LineInput, DocumentTotals } from '@/composables/useTaxCalculator';
import type { SearchSelectOption } from '@/Components/SearchSelect.vue';

interface Props {
    recurringInvoice: any | null;
    clients: any[];
    products: any[];
    series: any[];
    paymentTemplates: any[];
}

const props = defineProps<Props>();

const isEditing = computed(() => !!props.recurringInvoice);

const initialLines: LineInput[] = props.recurringInvoice?.lines?.map((l: any) => ({
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
})) ?? [{
    product_id: null,
    concept: '',
    description: '',
    quantity: 1,
    unit_price: 0,
    unit: '',
    discount_percent: 0,
    vat_rate: 21,
    exemption_code: '',
    irpf_rate: 0,
    surcharge_rate: 0,
}];

const form = useForm({
    name: props.recurringInvoice?.name || '',
    client_id: props.recurringInvoice?.client_id || null as number | null,
    series_id: props.recurringInvoice?.series_id || null as number | null,
    payment_template_id: props.recurringInvoice?.payment_template_id || null as number | null,
    invoice_type: props.recurringInvoice?.invoice_type || 'F1',
    regime_key: props.recurringInvoice?.regime_key || '01',
    global_discount_percent: Number(props.recurringInvoice?.global_discount_percent || 0),
    notes: props.recurringInvoice?.notes || '',
    footer_text: props.recurringInvoice?.footer_text || '',
    interval_value: props.recurringInvoice?.interval_value || 1,
    interval_unit: props.recurringInvoice?.interval_unit || 'month',
    start_date: props.recurringInvoice?.start_date?.split('T')[0] ?? new Date().toISOString().split('T')[0],
    end_date: props.recurringInvoice?.end_date?.split('T')[0] ?? '',
    max_occurrences: props.recurringInvoice?.max_occurrences || null as number | null,
    auto_finalize: props.recurringInvoice?.auto_finalize ?? false,
    auto_send_email: props.recurringInvoice?.auto_send_email ?? false,
    email_recipients: props.recurringInvoice?.email_recipients || '',
    lines: initialLines,
});

const totals = computed<DocumentTotals>(() => {
    return calculateDocument(form.lines as LineInput[], form.global_discount_percent || 0);
});

const clientOptions = computed<SearchSelectOption[]>(() =>
    props.clients.map(c => ({
        value: c.id,
        label: c.trade_name || c.legal_name,
        sublabel: c.nif || '',
    }))
);

const seriesOptions = computed<SearchSelectOption[]>(() =>
    props.series.map(s => ({
        value: s.id,
        label: s.prefix,
    }))
);

const paymentTemplateOptions = computed<SearchSelectOption[]>(() =>
    props.paymentTemplates.map(t => ({
        value: t.id,
        label: t.name,
    }))
);

const unitOptions = computed(() => [
    { value: 'day', label: trans('recurring.unit_day') },
    { value: 'week', label: trans('recurring.unit_week') },
    { value: 'month', label: trans('recurring.unit_month') },
    { value: 'year', label: trans('recurring.unit_year') },
]);

const submit = () => {
    if (isEditing.value) {
        form.put(route('recurring-invoices.update', props.recurringInvoice.id));
    } else {
        form.post(route('recurring-invoices.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? $t('recurring.title_edit') : $t('recurring.title_create')" />

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
                <h1 class="text-lg font-semibold text-gray-900">
                    {{ isEditing ? $t('recurring.title_edit') : $t('recurring.title_create') }}
                </h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-8">
            <!-- Section: Info -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ $t('recurring.section_info') }}</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Name -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_name') }} *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            :placeholder="$t('recurring.label_name_placeholder')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Client -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_client') }} *</label>
                        <SearchSelect
                            v-model="form.client_id"
                            :options="clientOptions"
                            :has-error="!!form.errors.client_id"
                            class="mt-1"
                        />
                        <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}</p>
                    </div>

                    <!-- Series -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_series') }}</label>
                        <SearchSelect
                            v-model="form.series_id"
                            :options="seriesOptions"
                            class="mt-1"
                        />
                    </div>

                    <!-- Payment Template -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_payment_template') }}</label>
                        <SearchSelect
                            v-model="form.payment_template_id"
                            :options="paymentTemplateOptions"
                            class="mt-1"
                        />
                    </div>
                </div>
            </div>

            <!-- Section: Recurrence -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ $t('recurring.section_recurrence') }}</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Start date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_start_date') }} *</label>
                        <input
                            v-model="form.start_date"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.start_date }"
                        />
                        <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                    </div>

                    <!-- Interval value + unit -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_interval_value') }} *</label>
                        <div class="mt-1 flex gap-2">
                            <input
                                v-model.number="form.interval_value"
                                type="number"
                                min="1"
                                max="365"
                                class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                :class="{ 'border-red-500': form.errors.interval_value }"
                            />
                            <select
                                v-model="form.interval_unit"
                                class="block flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option v-for="u in unitOptions" :key="u.value" :value="u.value">{{ u.label }}</option>
                            </select>
                        </div>
                        <p v-if="form.errors.interval_value" class="mt-1 text-sm text-red-600">{{ form.errors.interval_value }}</p>
                    </div>

                    <!-- End date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_end_date') }}</label>
                        <input
                            v-model="form.end_date"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>

                    <!-- Max occurrences -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_max_occurrences') }}</label>
                        <input
                            v-model.number="form.max_occurrences"
                            type="number"
                            min="1"
                            :placeholder="$t('recurring.show_unlimited')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Section: Behavior -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ $t('recurring.section_behavior') }}</h2>
                <div class="space-y-4">
                    <!-- Auto-finalize -->
                    <div class="flex items-start gap-3">
                        <input
                            v-model="form.auto_finalize"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <div>
                            <label class="text-sm font-medium text-gray-700">{{ $t('recurring.label_auto_finalize') }}</label>
                            <p class="text-sm text-gray-500">{{ $t('recurring.label_auto_finalize_help') }}</p>
                        </div>
                    </div>

                    <!-- Auto-send email -->
                    <div class="flex items-start gap-3">
                        <input
                            v-model="form.auto_send_email"
                            type="checkbox"
                            :disabled="!form.auto_finalize"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50"
                        />
                        <div>
                            <label class="text-sm font-medium text-gray-700" :class="{ 'opacity-50': !form.auto_finalize }">{{ $t('recurring.label_auto_send_email') }}</label>
                            <p class="text-sm text-gray-500" :class="{ 'opacity-50': !form.auto_finalize }">{{ $t('recurring.label_auto_send_email_help') }}</p>
                        </div>
                    </div>

                    <!-- Email recipients -->
                    <div v-if="form.auto_send_email && form.auto_finalize" class="ml-7">
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_email_recipients') }}</label>
                        <input
                            v-model="form.email_recipients"
                            type="text"
                            class="mt-1 block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="email1@example.com, email2@example.com"
                        />
                        <p class="mt-1 text-xs text-gray-500">{{ $t('recurring.label_email_recipients_help') }}</p>
                    </div>
                </div>
            </div>

            <!-- Section: Lines -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ $t('recurring.section_lines') }}</h2>
                <LineEditor
                    :lines="form.lines"
                    :products="products"
                    :errors="form.errors"
                    @update:lines="form.lines = $event"
                />
                <div class="mt-6">
                    <TotalsSummary
                        :totals="totals"
                        :global-discount-percent="form.global_discount_percent"
                        @update:global-discount-percent="form.global_discount_percent = $event"
                    />
                </div>
            </div>

            <!-- Section: Additional -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ $t('recurring.section_additional') }}</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Invoice type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_invoice_type') }}</label>
                        <select
                            v-model="form.invoice_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="F1">F1 - {{ $t('documents.invoice_type_F1') }}</option>
                            <option value="F2">F2 - {{ $t('documents.invoice_type_F2') }}</option>
                            <option value="F3">F3 - {{ $t('documents.invoice_type_F3') }}</option>
                        </select>
                    </div>

                    <!-- Regime key -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_regime_key') }}</label>
                        <select
                            v-model="form.regime_key"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="01">01 - General</option>
                            <option value="02">02 - Exportación</option>
                            <option value="03">03 - Bienes usados</option>
                            <option value="04">04 - Oro inversión</option>
                            <option value="05">05 - Agencias viaje</option>
                            <option value="06">06 - Grupos IVA</option>
                            <option value="07">07 - RECC</option>
                            <option value="08">08 - IPSI/IGIC</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_notes') }}</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>

                    <!-- Footer text -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('recurring.label_footer_text') }}</label>
                        <textarea
                            v-model="form.footer_text"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-4">
                <Link
                    :href="route('recurring-invoices.index')"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-900"
                >
                    {{ $t('common.cancel') }}
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                >
                    {{ isEditing ? $t('recurring.btn_update') : $t('recurring.btn_save') }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>
