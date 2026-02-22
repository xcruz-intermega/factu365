<script setup lang="ts">
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { calculateLine, getSurchargeRate, type LineInput, type CalculatedLine } from '@/composables/useTaxCalculator';
import { resolveClientDiscount, type ClientDiscountData } from '@/composables/useClientDiscountResolver';
import SearchSelect from '@/Components/SearchSelect.vue';
import type { SearchSelectOption } from '@/Components/SearchSelect.vue';

interface ProductComponent {
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
    components?: ProductComponent[];
}

const props = defineProps<{
    lines: LineInput[];
    products: Product[];
    errors: Record<string, string>;
    clientDiscounts?: ClientDiscountData[];
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
        unit: 'unidad',
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

    // Resolve client discount for this product
    let discountPercent = 0;
    if (props.clientDiscounts && props.clientDiscounts.length > 0) {
        const lineAmount = Number(product.unit_price) * (props.lines[index]?.quantity || 1);
        const resolved = resolveClientDiscount(
            props.clientDiscounts,
            { id: product.id, type: product.type, product_family_id: product.product_family_id ?? null },
            lineAmount,
        );
        if (resolved !== null) discountPercent = resolved;
    }

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
        discount_percent: discountPercent,
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

const productOptions = computed<SearchSelectOption[]>(() =>
    props.products.map(p => ({
        value: p.id,
        label: p.name,
        sublabel: p.reference || undefined,
    }))
);

const isComposite = (line: LineInput): boolean => {
    if (!line.product_id) return false;
    const product = props.products.find(p => p.id === line.product_id);
    return !!(product?.components && product.components.length > 0);
};

const componentCount = (line: LineInput): number => {
    if (!line.product_id) return 0;
    const product = props.products.find(p => p.id === line.product_id);
    return product?.components?.length ?? 0;
};

const expandComponents = (index: number) => {
    const line = props.lines[index];
    if (!line.product_id) return;

    const product = props.products.find(p => p.id === line.product_id);
    if (!product?.components || product.components.length === 0) return;

    const parentQty = line.quantity;
    const newLines = [...props.lines];

    // Build expanded lines from components
    const expandedLines: LineInput[] = product.components.map(comp => ({
        product_id: comp.component.id,
        concept: comp.component.name,
        description: '',
        quantity: Number((parentQty * Number(comp.quantity)).toFixed(4)),
        unit_price: Number(comp.component.unit_price),
        unit: comp.component.unit || '',
        discount_percent: line.discount_percent,
        vat_rate: Number(comp.component.vat_rate),
        exemption_code: comp.component.exemption_code || '',
        irpf_rate: comp.component.irpf_applicable ? 15 : 0,
        surcharge_rate: 0,
    }));

    // Replace the composite line with expanded lines
    newLines.splice(index, 1, ...expandedLines);
    emit('update:lines', newLines);
};

const hasClientDiscount = (line: LineInput): boolean => {
    if (!line.product_id || !props.clientDiscounts || props.clientDiscounts.length === 0) return false;
    if (line.discount_percent === 0) return false;
    const product = props.products.find(p => p.id === line.product_id);
    if (!product) return false;
    const resolved = resolveClientDiscount(
        props.clientDiscounts,
        { id: product.id, type: product.type, product_family_id: product.product_family_id ?? null },
        Number(line.unit_price) * line.quantity,
    );
    return resolved !== null && resolved === line.discount_percent;
};

const lineError = (index: number, field: string): string | undefined => {
    return props.errors[`lines.${index}.${field}`];
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">{{ $t('documents.document_lines') }}</h3>
            <button
                type="button"
                @click="addLine"
                class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
            >
                <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                {{ $t('documents.add_line') }}
            </button>
        </div>

        <!-- Lines -->
        <div v-if="lines.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
            <p class="text-sm text-gray-500">{{ $t('documents.no_lines') }}</p>
        </div>

        <div v-for="(line, index) in lines" :key="index" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <!-- Line number & composite badge -->
                <div class="flex flex-col items-center gap-1 pt-1">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">
                        {{ index + 1 }}
                    </span>
                    <span v-if="isComposite(line)" class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-800">
                        {{ $t('documents.components_count', { count: String(componentCount(line)) }) }}
                    </span>
                </div>

                <!-- Main fields -->
                <div class="flex-1 space-y-3">
                    <!-- Row 1: Product selector + Concept -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.product') }}</label>
                            <div class="mt-0.5">
                                <SearchSelect
                                    :model-value="line.product_id ?? null"
                                    @update:model-value="applyProduct(index, $event as number | null)"
                                    :options="productOptions"
                                    :placeholder="$t('documents.search_product')"
                                />
                            </div>
                        </div>
                        <div class="sm:col-span-8">
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.concept_required') }}</label>
                            <input
                                type="text"
                                :value="line.concept"
                                @input="updateLine(index, 'concept', ($event.target as HTMLInputElement).value)"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': lineError(index, 'concept') }"
                                :placeholder="$t('documents.concept_placeholder')"
                            />
                            <p v-if="lineError(index, 'concept')" class="mt-1 text-xs text-red-600">{{ lineError(index, 'concept') }}</p>
                        </div>
                    </div>

                    <!-- Expand components button for composite products -->
                    <div v-if="isComposite(line)" class="flex items-center">
                        <button
                            type="button"
                            @click="expandComponents(index)"
                            class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 hover:bg-amber-100"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            {{ $t('documents.expand_components', { count: String(componentCount(line)) }) }}
                        </button>
                        <span class="ml-2 text-xs text-gray-400">{{ $t('documents.expand_components_help') }}</span>
                    </div>

                    <!-- Row 2: Quantity, Unit Price, Unit, Discount -->
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.quantity_required') }}</label>
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
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.unit_price_required') }}</label>
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
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.unit') }}</label>
                            <input
                                type="text"
                                :value="line.unit"
                                @input="updateLine(index, 'unit', ($event.target as HTMLInputElement).value)"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :placeholder="$t('documents.unit_short')"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-1 text-xs font-medium text-gray-600">
                                {{ $t('documents.discount_pct') }}
                                <span v-if="hasClientDiscount(line)" class="inline-flex items-center rounded-full bg-green-100 px-1.5 py-0.5 text-[9px] font-medium text-green-700" :title="$t('documents.client_discount')">
                                    {{ $t('documents.client_discount') }}
                                </span>
                            </label>
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
                                <span class="text-xs text-gray-500">{{ $t('common.subtotal') }}</span>
                                <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(calculatedLines[index]?.line_total ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: VAT, IRPF, Surcharge -->
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.vat_pct') }}</label>
                            <select
                                :value="line.vat_rate"
                                @change="onVatRateChange(index, Number(($event.target as HTMLSelectElement).value))"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option :value="21">{{ $t('common.vat_21') }}</option>
                                <option :value="10">{{ $t('common.vat_10') }}</option>
                                <option :value="4">{{ $t('common.vat_4') }}</option>
                                <option :value="0">{{ $t('common.vat_0_exempt') }}</option>
                            </select>
                        </div>
                        <div v-if="line.vat_rate === 0" class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.exemption_required') }}</label>
                            <select
                                :value="line.exemption_code"
                                @change="updateLine(index, 'exemption_code', ($event.target as HTMLSelectElement).value)"
                                class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': lineError(index, 'exemption_code') }"
                            >
                                <option value="">{{ $t('common.exemption_select') }}</option>
                                <option value="E1">{{ $t('common.exemption_e1') }}</option>
                                <option value="E2">{{ $t('common.exemption_e2') }}</option>
                                <option value="E3">{{ $t('common.exemption_e3') }}</option>
                                <option value="E4">{{ $t('common.exemption_e4') }}</option>
                                <option value="E5">{{ $t('common.exemption_e5') }}</option>
                                <option value="E6">{{ $t('common.exemption_e6') }}</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.irpf_pct') }}</label>
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
                            <label class="block text-xs font-medium text-gray-600">{{ $t('documents.surcharge_label') }}</label>
                            <button
                                type="button"
                                @click="toggleSurcharge(index)"
                                class="mt-0.5 flex h-[38px] w-full items-center justify-center rounded-md border text-sm"
                                :class="line.surcharge_rate > 0
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold'
                                    : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'"
                            >
                                {{ line.surcharge_rate > 0 ? `${line.surcharge_rate}%` : $t('common.no') }}
                            </button>
                        </div>
                        <div class="sm:col-span-3 flex items-end">
                            <div class="text-right w-full space-y-0.5">
                                <p class="text-xs text-gray-500">
                                    {{ $t('common.vat') }}: {{ formatCurrency(calculatedLines[index]?.vat_amount ?? 0) }}
                                </p>
                                <p v-if="line.irpf_rate > 0" class="text-xs text-red-500">
                                    {{ $t('common.irpf') }}: -{{ formatCurrency(calculatedLines[index]?.irpf_amount ?? 0) }}
                                </p>
                                <p v-if="line.surcharge_rate > 0" class="text-xs text-gray-500">
                                    {{ $t('common.surcharge') }}: {{ formatCurrency(calculatedLines[index]?.surcharge_amount ?? 0) }}
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
                    :title="$t('documents.remove_line')"
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
                {{ $t('documents.add_another_line') }}
            </button>
        </div>
    </div>
</template>
