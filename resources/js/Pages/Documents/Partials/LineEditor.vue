<script setup lang="ts">
import { computed } from 'vue';
import { calculateLine, getSurchargeRate, type LineInput, type CalculatedLine } from '@/composables/useTaxCalculator';

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

const props = defineProps<{
    lines: LineInput[];
    products: Product[];
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    'update:lines': [lines: LineInput[]];
}>();

const calculatedLines = computed<CalculatedLine[]>(() => {
    return props.lines.map(line => calculateLine(line));
});

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const addLine = () => {
    emit('update:lines', [...props.lines, {
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
    }]);
};

const removeLine = (index: number) => {
    const newLines = [...props.lines];
    newLines.splice(index, 1);
    emit('update:lines', newLines);
};

const updateLine = (index: number, field: string, value: any) => {
    const newLines = [...props.lines];
    newLines[index] = { ...newLines[index], [field]: value };
    emit('update:lines', newLines);
};

const applyProduct = (index: number, productId: number | null) => {
    if (!productId) {
        updateLine(index, 'product_id', null);
        return;
    }

    const product = props.products.find(p => p.id === productId);
    if (!product) return;

    const newLines = [...props.lines];
    newLines[index] = {
        ...newLines[index],
        product_id: product.id,
        concept: product.name,
        unit_price: Number(product.unit_price),
        vat_rate: Number(product.vat_rate),
        exemption_code: product.exemption_code || '',
        irpf_rate: product.irpf_applicable ? 15 : 0,
        surcharge_rate: 0,
        unit: product.unit || '',
    };
    emit('update:lines', newLines);
};

const toggleSurcharge = (index: number) => {
    const line = props.lines[index];
    if (line.surcharge_rate > 0) {
        updateLine(index, 'surcharge_rate', 0);
    } else {
        updateLine(index, 'surcharge_rate', getSurchargeRate(line.vat_rate));
    }
};

const onVatRateChange = (index: number, value: number) => {
    const newLines = [...props.lines];
    const hasSurcharge = newLines[index].surcharge_rate > 0;
    newLines[index] = {
        ...newLines[index],
        vat_rate: value,
        surcharge_rate: hasSurcharge ? getSurchargeRate(value) : 0,
    };
    emit('update:lines', newLines);
};

const lineError = (index: number, field: string): string | undefined => {
    return props.errors[`lines.${index}.${field}`];
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Líneas del documento</h3>
            <button
                type="button"
                @click="addLine"
                class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
            >
                <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Añadir línea
            </button>
        </div>

        <!-- Lines -->
        <div v-if="lines.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
            <p class="text-sm text-gray-500">No hay líneas. Haz clic en "Añadir línea" para empezar.</p>
        </div>

        <div v-for="(line, index) in lines" :key="index" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <!-- Line number & remove -->
                <div class="flex items-center gap-2 pt-1">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">
                        {{ index + 1 }}
                    </span>
                </div>

                <!-- Main fields -->
                <div class="flex-1 space-y-3">
                    <!-- Row 1: Product selector + Concept -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-medium text-gray-600">Producto</label>
                            <select
                                :value="line.product_id ?? ''"
                                @change="applyProduct(index, ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">-- Seleccionar --</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">
                                    {{ p.reference ? `[${p.reference}] ` : '' }}{{ p.name }}
                                </option>
                            </select>
                        </div>
                        <div class="sm:col-span-8">
                            <label class="block text-xs font-medium text-gray-600">Concepto *</label>
                            <input
                                type="text"
                                :value="line.concept"
                                @input="updateLine(index, 'concept', ($event.target as HTMLInputElement).value)"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': lineError(index, 'concept') }"
                                placeholder="Concepto de la línea"
                            />
                            <p v-if="lineError(index, 'concept')" class="mt-1 text-xs text-red-600">{{ lineError(index, 'concept') }}</p>
                        </div>
                    </div>

                    <!-- Row 2: Quantity, Unit Price, Unit, Discount -->
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Cantidad *</label>
                            <input
                                type="number"
                                :value="line.quantity"
                                @input="updateLine(index, 'quantity', Number(($event.target as HTMLInputElement).value))"
                                step="0.0001"
                                min="0.0001"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': lineError(index, 'quantity') }"
                            />
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-600">Precio unitario *</label>
                            <input
                                type="number"
                                :value="line.unit_price"
                                @input="updateLine(index, 'unit_price', Number(($event.target as HTMLInputElement).value))"
                                step="0.01"
                                min="0"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': lineError(index, 'unit_price') }"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Unidad</label>
                            <input
                                type="text"
                                :value="line.unit"
                                @input="updateLine(index, 'unit', ($event.target as HTMLInputElement).value)"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="ud."
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Dto. %</label>
                            <input
                                type="number"
                                :value="line.discount_percent"
                                @input="updateLine(index, 'discount_percent', Number(($event.target as HTMLInputElement).value))"
                                step="0.01"
                                min="0"
                                max="100"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="sm:col-span-3 flex items-end">
                            <div class="text-right w-full">
                                <span class="text-xs text-gray-500">Subtotal</span>
                                <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(calculatedLines[index]?.line_total ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: VAT, IRPF, Surcharge -->
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">IVA %</label>
                            <select
                                :value="line.vat_rate"
                                @change="onVatRateChange(index, Number(($event.target as HTMLSelectElement).value))"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option :value="21">21%</option>
                                <option :value="10">10%</option>
                                <option :value="4">4%</option>
                                <option :value="0">0% (Exento)</option>
                            </select>
                        </div>
                        <div v-if="line.vat_rate === 0" class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-600">Exención *</label>
                            <select
                                :value="line.exemption_code"
                                @change="updateLine(index, 'exemption_code', ($event.target as HTMLSelectElement).value)"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': lineError(index, 'exemption_code') }"
                            >
                                <option value="">Seleccionar</option>
                                <option value="E1">E1 - Exenta Art. 20</option>
                                <option value="E2">E2 - Exenta Art. 21</option>
                                <option value="E3">E3 - Exenta Art. 22</option>
                                <option value="E4">E4 - Exenta Art. 23 y 24</option>
                                <option value="E5">E5 - Exenta Art. 25</option>
                                <option value="E6">E6 - Exenta otros</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">IRPF %</label>
                            <input
                                type="number"
                                :value="line.irpf_rate"
                                @input="updateLine(index, 'irpf_rate', Number(($event.target as HTMLInputElement).value))"
                                step="0.01"
                                min="0"
                                max="100"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">R.E.</label>
                            <button
                                type="button"
                                @click="toggleSurcharge(index)"
                                class="mt-0.5 flex h-[38px] w-full items-center justify-center rounded-md border text-sm"
                                :class="line.surcharge_rate > 0
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold'
                                    : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'"
                            >
                                {{ line.surcharge_rate > 0 ? `${line.surcharge_rate}%` : 'No' }}
                            </button>
                        </div>
                        <div class="sm:col-span-3 flex items-end">
                            <div class="text-right w-full space-y-0.5">
                                <p class="text-xs text-gray-500">
                                    IVA: {{ formatCurrency(calculatedLines[index]?.vat_amount ?? 0) }}
                                </p>
                                <p v-if="line.irpf_rate > 0" class="text-xs text-red-500">
                                    IRPF: -{{ formatCurrency(calculatedLines[index]?.irpf_amount ?? 0) }}
                                </p>
                                <p v-if="line.surcharge_rate > 0" class="text-xs text-gray-500">
                                    R.E.: {{ formatCurrency(calculatedLines[index]?.surcharge_amount ?? 0) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remove button -->
                <button
                    type="button"
                    @click="removeLine(index)"
                    class="mt-1 rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                    title="Eliminar línea"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Add line button at bottom too if we have lines -->
        <div v-if="lines.length > 0" class="flex justify-center">
            <button
                type="button"
                @click="addLine"
                class="inline-flex items-center rounded-md border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-500 hover:border-indigo-400 hover:text-indigo-600"
            >
                <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Añadir otra línea
            </button>
        </div>
    </div>
</template>
