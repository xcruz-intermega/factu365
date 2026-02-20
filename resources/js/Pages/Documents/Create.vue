<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DocumentForm from './Partials/DocumentForm.vue';
import type { LineInput } from '@/composables/useTaxCalculator';

interface Props {
    documentType: string;
    documentTypeLabel: string;
    direction: string;
    clients: any[];
    products: any[];
    series: any[];
    parentDocument?: any;
}

const props = defineProps<Props>();

// Pre-populate lines from parent document if converting
const initialLines: LineInput[] = props.parentDocument?.lines?.map((l: any) => ({
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

const defaultInvoiceType = props.documentType === 'rectificative' ? 'R1'
    : ['invoice'].includes(props.documentType) ? 'F1' : null;

const form = useForm({
    document_type: props.documentType,
    invoice_type: defaultInvoiceType,
    client_id: props.parentDocument?.client_id ?? null,
    series_id: null as number | null,
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    operation_date: '',
    global_discount_percent: Number(props.parentDocument?.global_discount_percent ?? 0),
    regime_key: '01',
    notes: props.parentDocument?.notes ?? '',
    footer_text: props.parentDocument?.footer_text ?? '',
    corrected_document_id: null as number | null,
    rectificative_type: props.documentType === 'rectificative' ? 'substitution' : null,
    lines: initialLines,
});

const submit = () => {
    form.post(route('documents.store', { type: props.documentType }));
};
</script>

<template>
    <Head :title="`Nuevo ${documentTypeLabel}`" />

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
                <h1 class="text-lg font-semibold text-gray-900">Nuevo {{ documentTypeLabel }}</h1>
            </div>
        </template>

        <DocumentForm
            :form="form"
            :document-type="documentType"
            :document-type-label="documentTypeLabel"
            :clients="clients"
            :products="products"
            :series="series"
            @submit="submit"
        />
    </AppLayout>
</template>
