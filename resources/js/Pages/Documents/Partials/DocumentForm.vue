<script setup lang="ts">
import { computed, watch } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';
import LineEditor from './LineEditor.vue';
import TotalsSummary from './TotalsSummary.vue';
import { calculateDocument, type LineInput, type DocumentTotals } from '@/composables/useTaxCalculator';

interface Client {
    id: number;
    legal_name: string;
    trade_name: string | null;
    nif: string;
    type: string;
    payment_terms_days: number | null;
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

const invoiceTypes = computed(() => {
    if (props.documentType === 'rectificative') {
        return [
            { value: 'R1', label: 'R1 - Rectificativa Art. 80.1, 80.2 y 80.6' },
            { value: 'R2', label: 'R2 - Rectificativa Art. 80.3' },
            { value: 'R3', label: 'R3 - Rectificativa Art. 80.4' },
            { value: 'R4', label: 'R4 - Rectificativa otros' },
            { value: 'R5', label: 'R5 - Rectificativa facturas simplificadas' },
        ];
    }
    return [
        { value: 'F1', label: 'F1 - Factura completa' },
        { value: 'F2', label: 'F2 - Factura simplificada' },
        { value: 'F3', label: 'F3 - Factura emitida en sustitución' },
    ];
});

const updateLines = (lines: LineInput[]) => {
    props.form.lines = lines;
};

const todayStr = new Date().toISOString().split('T')[0];
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-6">
        <!-- Header section -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Datos generales</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Client -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Cliente</label>
                    <select
                        v-model="form.client_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.client_id }"
                    >
                        <option :value="null">-- Seleccionar cliente --</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">
                            {{ c.trade_name || c.legal_name }} ({{ c.nif }})
                        </option>
                    </select>
                    <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}</p>
                </div>

                <!-- Invoice type (only for invoices/rectificativas) -->
                <div v-if="isInvoiceType">
                    <label class="block text-sm font-medium text-gray-700">Tipo factura</label>
                    <select
                        v-model="form.invoice_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="t in invoiceTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>

                <!-- Series -->
                <div v-if="series.length > 1">
                    <label class="block text-sm font-medium text-gray-700">Serie</label>
                    <select
                        v-model="form.series_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">Por defecto</option>
                        <option v-for="s in series" :key="s.id" :value="s.id">{{ s.prefix }} ({{ s.fiscal_year }})</option>
                    </select>
                </div>

                <!-- Issue date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha emisión *</label>
                    <input
                        type="date"
                        v-model="form.issue_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.issue_date }"
                    />
                    <p v-if="form.errors.issue_date" class="mt-1 text-sm text-red-600">{{ form.errors.issue_date }}</p>
                </div>

                <!-- Due date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha vencimiento</label>
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
                    <label class="block text-sm font-medium text-gray-700">Fecha operación</label>
                    <input
                        type="date"
                        v-model="form.operation_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <!-- Rectificative type (only for rectificatives) -->
                <div v-if="documentType === 'rectificative'">
                    <label class="block text-sm font-medium text-gray-700">Método rectificación</label>
                    <select
                        v-model="form.rectificative_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="substitution">Por sustitución</option>
                        <option value="differences">Por diferencias</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Lines section -->
        <LineEditor
            :lines="form.lines"
            :products="products"
            :errors="form.errors"
            @update:lines="updateLines"
        />

        <!-- Bottom section: Notes + Totals side by side -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Notes -->
            <div class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Notas y pie</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas internas</label>
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Notas internas (no se muestran al cliente)"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pie de documento</label>
                            <textarea
                                v-model="form.footer_text"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Texto visible en el pie del documento"
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
                {{ form.processing ? 'Guardando...' : (isEdit ? 'Guardar cambios' : 'Crear borrador') }}
            </button>
        </div>
    </form>
</template>
