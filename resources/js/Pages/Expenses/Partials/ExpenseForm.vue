<script setup lang="ts">
import { computed } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';
import SearchSelect from '@/Components/SearchSelect.vue';
import type { SearchSelectOption } from '@/Components/SearchSelect.vue';

interface Category {
    id: number;
    name: string;
    code: string;
}

interface Supplier {
    id: number;
    legal_name: string;
    trade_name: string | null;
    nif: string;
}

const props = defineProps<{
    form: InertiaForm<any>;
    categories: Category[];
    suppliers: Supplier[];
    isEdit?: boolean;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const vatAmount = computed(() => {
    const subtotal = parseFloat(props.form.subtotal) || 0;
    const vatRate = parseFloat(props.form.vat_rate) || 0;
    return Math.round(subtotal * vatRate / 100 * 100) / 100;
});

const irpfAmount = computed(() => {
    const subtotal = parseFloat(props.form.subtotal) || 0;
    const irpfRate = parseFloat(props.form.irpf_rate) || 0;
    return Math.round(subtotal * irpfRate / 100 * 100) / 100;
});

const total = computed(() => {
    const subtotal = parseFloat(props.form.subtotal) || 0;
    return Math.round((subtotal + vatAmount.value - irpfAmount.value) * 100) / 100;
});

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

const handleFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        props.form.attachment = input.files[0];
    }
};
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-6" enctype="multipart/form-data">
        <!-- General data -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('expenses.section_data') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.date') }}</label>
                    <input
                        type="date"
                        v-model="form.expense_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.expense_date }"
                    />
                    <p v-if="form.errors.expense_date" class="mt-1 text-sm text-red-600">{{ form.errors.expense_date }}</p>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.category') }}</label>
                    <select
                        v-model="form.category_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">{{ $t('expenses.no_category') }}</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">
                            {{ c.code ? `[${c.code}] ` : '' }}{{ c.name }}
                        </option>
                    </select>
                </div>

                <!-- Supplier -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.supplier') }}</label>
                    <div class="mt-1">
                        <SearchSelect
                            v-model="form.supplier_client_id"
                            :options="supplierOptions"
                            :placeholder="$t('expenses.search_supplier')"
                        />
                    </div>
                </div>

                <!-- Supplier name (free text, when no supplier selected) -->
                <div v-if="!form.supplier_client_id">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.supplier_name') }}</label>
                    <input
                        type="text"
                        v-model="form.supplier_name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :placeholder="$t('expenses.supplier_name_placeholder')"
                    />
                </div>

                <!-- Invoice number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.invoice_number') }}</label>
                    <input
                        type="text"
                        v-model="form.invoice_number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :placeholder="$t('expenses.invoice_number_placeholder')"
                    />
                </div>

                <!-- Concept -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.concept') }}</label>
                    <input
                        type="text"
                        v-model="form.concept"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.concept }"
                        :placeholder="$t('expenses.concept_placeholder')"
                    />
                    <p v-if="form.errors.concept" class="mt-1 text-sm text-red-600">{{ form.errors.concept }}</p>
                </div>
            </div>
        </div>

        <!-- Amounts -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('expenses.section_amounts') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Subtotal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.tax_base') }}</label>
                    <input
                        type="number"
                        v-model="form.subtotal"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.subtotal }"
                    />
                    <p v-if="form.errors.subtotal" class="mt-1 text-sm text-red-600">{{ form.errors.subtotal }}</p>
                </div>

                <!-- VAT rate -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.vat_pct') }}</label>
                    <select
                        v-model="form.vat_rate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="21">{{ $t('common.vat_21') }}</option>
                        <option :value="10">{{ $t('common.vat_10') }}</option>
                        <option :value="4">{{ $t('common.vat_4') }}</option>
                        <option :value="0">{{ $t('common.vat_0_exempt') }}</option>
                    </select>
                </div>

                <!-- IRPF rate -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.irpf_pct') }}</label>
                    <input
                        type="number"
                        v-model="form.irpf_rate"
                        step="0.01"
                        min="0"
                        max="100"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <!-- Calculated total -->
                <div class="flex flex-col justify-end">
                    <div class="rounded-md bg-gray-50 p-3 text-right">
                        <p class="text-xs text-gray-500">{{ $t('expenses.vat_label') }}{{ formatCurrency(vatAmount) }}</p>
                        <p v-if="irpfAmount > 0" class="text-xs text-red-500">{{ $t('expenses.irpf_label') }}{{ formatCurrency(irpfAmount) }}</p>
                        <p class="mt-1 text-lg font-bold text-indigo-700">{{ formatCurrency(total) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment & Attachment -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('expenses.section_payment') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.status') }}</label>
                        <select
                            v-model="form.payment_status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="pending">{{ $t('expenses.status_pending') }}</option>
                            <option value="paid">{{ $t('expenses.status_paid') }}</option>
                        </select>
                    </div>
                    <div v-if="form.payment_status === 'paid'" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.payment_date') }}</label>
                            <input
                                type="date"
                                v-model="form.payment_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.payment_date }"
                            />
                            <p v-if="form.errors.payment_date" class="mt-1 text-sm text-red-600">{{ form.errors.payment_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.payment_method') }}</label>
                            <select
                                v-model="form.payment_method"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ $t('common.select_payment') }}</option>
                                <option value="transfer">{{ $t('common.payment_transfer') }}</option>
                                <option value="card">{{ $t('common.payment_card') }}</option>
                                <option value="cash">{{ $t('common.payment_cash') }}</option>
                                <option value="direct_debit">{{ $t('common.payment_direct_debit') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('expenses.section_attachment') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.attachment') }}</label>
                        <input
                            type="file"
                            @change="handleFileChange"
                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</p>
                        <p v-if="isEdit && form.attachment_path" class="mt-1 text-xs text-gray-500">
                            {{ $t('expenses.attachment_exists') }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('expenses.notes') }}</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :placeholder="$t('expenses.notes_placeholder')"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
            >
                {{ form.processing ? $t('common.saving') : (isEdit ? $t('common.save_changes') : $t('expenses.create_expense')) }}
            </button>
        </div>
    </form>
</template>
