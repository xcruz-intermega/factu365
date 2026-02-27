<script setup lang="ts">
import type { DocumentTotals } from '@/composables/useTaxCalculator';

defineProps<{
    totals: DocumentTotals;
    globalDiscountPercent: number;
}>();

const emit = defineEmits<{
    'update:globalDiscountPercent': [value: number];
}>();

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('documents.summary') }}</h3>

        <div class="space-y-2 text-sm">
            <!-- Subtotal -->
            <div class="flex justify-between">
                <span class="text-gray-600">{{ $t('common.subtotal') }}</span>
                <span class="font-medium">{{ formatCurrency(totals.subtotal) }}</span>
            </div>

            <!-- Line discounts -->
            <div v-if="totals.total_discount > 0" class="flex justify-between text-orange-600">
                <span>{{ $t('documents.line_discounts') }}</span>
                <span>-{{ formatCurrency(totals.total_discount - totals.global_discount_amount) }}</span>
            </div>

            <!-- Global discount -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-gray-600">{{ $t('documents.global_discount') }}</span>
                    <input
                        type="number"
                        :value="globalDiscountPercent"
                        @input="emit('update:globalDiscountPercent', Number(($event.target as HTMLInputElement).value))"
                        step="0.1"
                        min="0"
                        max="100"
                        class="w-16 rounded border-gray-300 px-1.5 py-0.5 text-right text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <span class="text-xs text-gray-400">%</span>
                </div>
                <span v-if="totals.global_discount_amount > 0" class="text-orange-600">
                    -{{ formatCurrency(totals.global_discount_amount) }}
                </span>
            </div>

            <div class="border-t border-gray-200 pt-2">
                <!-- Tax base -->
                <div class="flex justify-between font-medium">
                    <span class="text-gray-700">{{ $t('common.tax_base') }}</span>
                    <span>{{ formatCurrency(totals.tax_base) }}</span>
                </div>
            </div>

            <!-- VAT breakdown -->
            <div v-for="entry in totals.vat_breakdown" :key="entry.rate" class="flex justify-between text-gray-600">
                <span>{{ $t('common.vat') }} {{ entry.rate }}%</span>
                <span>{{ formatCurrency(entry.vat) }}</span>
            </div>

            <!-- Surcharge breakdown -->
            <template v-for="entry in totals.vat_breakdown" :key="'sur-' + entry.rate">
                <div v-if="entry.surcharge > 0" class="flex justify-between text-gray-600">
                    <span>{{ $t('common.surcharge') }} {{ entry.surcharge_rate }}%</span>
                    <span>{{ formatCurrency(entry.surcharge) }}</span>
                </div>
            </template>

            <!-- IRPF -->
            <div v-if="totals.total_irpf > 0" class="flex justify-between text-red-600">
                <span>{{ $t('common.irpf') }}</span>
                <span>-{{ formatCurrency(totals.total_irpf) }}</span>
            </div>

            <!-- Total -->
            <div class="border-t border-gray-200 pt-2">
                <div class="flex justify-between text-lg font-bold">
                    <span class="text-gray-900">{{ $t('common.total') }}</span>
                    <span class="text-indigo-700">{{ formatCurrency(totals.total) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
