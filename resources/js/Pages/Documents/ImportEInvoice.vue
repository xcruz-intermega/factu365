<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import type { SearchSelectOption } from '@/Components/SearchSelect.vue';
import { computed } from 'vue';

interface PreviewLine {
    concept: string;
    quantity: number;
    unitPrice: number;
    vatRate: number;
    lineSubtotal: number;
    vatAmount: number;
    lineTotal: number;
}

interface VatBreakdown {
    rate: number;
    base: number;
    vat: number;
}

interface Preview {
    invoiceNumber: string;
    issueDate: string;
    supplierNif: string;
    supplierName: string;
    lines: PreviewLine[];
    vatBreakdown: VatBreakdown[];
    totalBase: number;
    totalVat: number;
    totalIrpf: number;
    total: number;
    format: string;
    errors: string[];
    tempPath: string;
    matchedSupplierId: number | null;
}

interface Supplier {
    id: number;
    legal_name: string;
    trade_name: string | null;
    nif: string;
}

const props = defineProps<{
    suppliers: Supplier[];
    preview?: Preview;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const dragOver = ref(false);

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const supplierOptions = computed<SearchSelectOption[]>(() =>
    props.suppliers.map(s => ({
        value: s.id,
        label: s.trade_name || s.legal_name,
        sublabel: s.nif,
    }))
);

// Upload form
const handleUpload = (file: File) => {
    uploading.value = true;
    const formData = new FormData();
    formData.append('file', file);

    router.post(route('documents.import.preview', { type: 'purchase_invoice' }), formData as any, {
        forceFormData: true,
        onFinish: () => { uploading.value = false; },
    });
};

const onFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        handleUpload(input.files[0]);
    }
};

const onDrop = (e: DragEvent) => {
    dragOver.value = false;
    if (e.dataTransfer?.files?.length) {
        handleUpload(e.dataTransfer.files[0]);
    }
};

// Import form (after preview)
const importForm = useForm({
    temp_path: props.preview?.tempPath ?? '',
    supplier_client_id: props.preview?.matchedSupplierId ?? null,
    invoice_number: props.preview?.invoiceNumber ?? '',
    issue_date: props.preview?.issueDate ?? '',
    lines: (props.preview?.lines ?? []).map(l => ({
        concept: l.concept,
        quantity: l.quantity,
        unit_price: l.unitPrice,
        vat_rate: l.vatRate,
        irpf_rate: 0,
        unit: 'unidad',
    })),
});

const submitImport = () => {
    importForm.post(route('documents.import.store', { type: 'purchase_invoice' }));
};

const hasBlockingErrors = computed(() => (props.preview?.errors?.length ?? 0) > 0);
</script>

<template>
    <Head :title="$t('documents.import_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('documents.import_title') }}</h1>
        </template>

        <!-- Upload step (no preview yet) -->
        <div v-if="!preview" class="mx-auto max-w-xl">
            <div
                class="rounded-lg border-2 border-dashed p-12 text-center transition-colors"
                :class="dragOver ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-white'"
                @dragover.prevent="dragOver = true"
                @dragleave="dragOver = false"
                @drop.prevent="onDrop"
            >
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <p class="mt-4 text-sm font-medium text-gray-900">{{ $t('documents.import_drop_zone') }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $t('documents.import_formats') }}</p>
                <button
                    @click="fileInput?.click()"
                    :disabled="uploading"
                    class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                >
                    {{ uploading ? $t('common.loading') : $t('documents.import_select_file') }}
                </button>
                <input ref="fileInput" type="file" accept=".xml,.pdf" class="hidden" @change="onFileChange" />
            </div>
        </div>

        <!-- Preview step -->
        <div v-else class="space-y-6">
            <!-- Errors banner -->
            <div v-if="preview.errors.length > 0" class="rounded-md border border-red-200 bg-red-50 p-4">
                <h3 class="text-sm font-medium text-red-800">{{ $t('documents.import_validation_errors') }}</h3>
                <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                    <li v-for="(err, i) in preview.errors" :key="i">{{ err }}</li>
                </ul>
            </div>

            <!-- Format badge -->
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-800">
                    {{ preview.format === 'facturae' ? 'FacturaE (XML)' : 'Factur-X (PDF)' }}
                </span>
                <button
                    @click="router.get(route('documents.import', { type: 'purchase_invoice' }))"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    {{ $t('documents.import_upload_another') }}
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Supplier info -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('documents.import_supplier_info') }}</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium text-gray-500">{{ $t('documents.import_supplier_name') }}:</span> {{ preview.supplierName }}</p>
                        <p><span class="font-medium text-gray-500">NIF:</span> {{ preview.supplierNif || '--' }}</p>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-500">{{ $t('documents.import_link_supplier') }}</label>
                        <div class="mt-1">
                            <SearchSelect
                                v-model="importForm.supplier_client_id"
                                :options="supplierOptions"
                                :placeholder="$t('expenses.search_supplier')"
                            />
                        </div>
                    </div>
                </div>

                <!-- Invoice info -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('documents.import_invoice_info') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">{{ $t('documents.import_invoice_number') }}</label>
                            <input
                                v-model="importForm.invoice_number"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">{{ $t('documents.import_issue_date') }}</label>
                            <input
                                v-model="importForm.issue_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lines table -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $t('documents.import_lines') }} ({{ preview.lines.length }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('common.concept') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.quantity') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.unit_price') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.vat') }} %</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.tax_base') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.vat') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(line, i) in preview.lines" :key="i" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ line.concept }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700">{{ line.quantity }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(line.unitPrice) }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700">{{ line.vatRate }}%</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(line.lineSubtotal) }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700">{{ formatCurrency(line.vatAmount) }}</td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900">{{ formatCurrency(line.lineTotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VAT breakdown + totals -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div v-if="preview.vatBreakdown.length > 0" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('books.vat_breakdown') }}</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-1 text-left text-xs text-gray-500">{{ $t('books.vat_rate') }}</th>
                                <th class="py-1 text-right text-xs text-gray-500">{{ $t('books.vat_base') }}</th>
                                <th class="py-1 text-right text-xs text-gray-500">{{ $t('books.vat_amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vb in preview.vatBreakdown" :key="vb.rate">
                                <td class="py-1 text-gray-700">{{ vb.rate }}%</td>
                                <td class="py-1 text-right text-gray-700">{{ formatCurrency(vb.base) }}</td>
                                <td class="py-1 text-right text-gray-700">{{ formatCurrency(vb.vat) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('common.total') }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ $t('common.tax_base') }}</span>
                            <span class="font-medium">{{ formatCurrency(preview.totalBase) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ $t('common.vat') }}</span>
                            <span class="font-medium">{{ formatCurrency(preview.totalVat) }}</span>
                        </div>
                        <div v-if="preview.totalIrpf > 0" class="flex justify-between">
                            <span class="text-gray-500">{{ $t('common.irpf') }}</span>
                            <span class="font-medium text-red-600">-{{ formatCurrency(preview.totalIrpf) }}</span>
                        </div>
                        <hr class="border-gray-200" />
                        <div class="flex justify-between text-base">
                            <span class="font-semibold text-gray-900">{{ $t('common.total') }}</span>
                            <span class="text-lg font-bold text-indigo-700">{{ formatCurrency(preview.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <button
                    @click="router.get(route('documents.import', { type: 'purchase_invoice' }))"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                >
                    {{ $t('documents.import_upload_another') }}
                </button>
                <button
                    @click="submitImport"
                    :disabled="importForm.processing || hasBlockingErrors"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                >
                    {{ importForm.processing ? $t('common.loading') : $t('documents.import_button') }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>
