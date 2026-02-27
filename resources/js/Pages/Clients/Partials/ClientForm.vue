<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

interface DiscountData {
    id: number;
    client_id: number;
    discount_type: 'general' | 'agreement' | 'type' | 'family';
    discount_percent: number;
    product_type: string | null;
    product_family_id: number | null;
    min_amount: number | null;
    valid_from: string | null;
    valid_to: string | null;
    notes: string | null;
    product_family?: { id: number; name: string } | null;
}

interface FamilyOption {
    id: number;
    name: string;
    parent_id: number | null;
}

const props = defineProps<{
    form: InertiaForm<{
        type: string;
        legal_name: string;
        trade_name: string;
        nif: string;
        address_street: string;
        address_city: string;
        address_postal_code: string;
        address_province: string;
        address_country: string;
        phone: string;
        email: string;
        website: string;
        contact_person: string;
        payment_terms_days: number;
        payment_template_id: number | null;
        payment_method: string;
        iban: string;
        notes: string;
    }>;
    submitLabel: string;
    paymentTemplates?: Array<{ id: number; name: string }>;
    clientId?: number;
    discounts?: DiscountData[];
    productFamilies?: FamilyOption[];
}>();

const emit = defineEmits<{
    submit: [];
}>();

// Discount management
const showNewDiscount = ref(false);
const editingDiscountId = ref<number | null>(null);
const savingDiscount = ref(false);

const newDiscount = reactive({
    discount_type: 'general' as 'general' | 'agreement' | 'type' | 'family',
    discount_percent: 5,
    product_type: null as string | null,
    product_family_id: null as number | null,
    min_amount: null as number | null,
    valid_from: null as string | null,
    valid_to: null as string | null,
    notes: null as string | null,
});

const editDiscount = reactive({
    discount_type: 'general' as 'general' | 'agreement' | 'type' | 'family',
    discount_percent: 0,
    product_type: null as string | null,
    product_family_id: null as number | null,
    min_amount: null as number | null,
    valid_from: null as string | null,
    valid_to: null as string | null,
    notes: null as string | null,
});

const discountTypeLabels = computed<Record<string, string>>(() => ({
    general: trans('clients.discount_type_short_general'),
    agreement: trans('clients.discount_type_short_agreement'),
    type: trans('clients.discount_type_short_by_type'),
    family: trans('clients.discount_type_short_by_family'),
}));

const resetNewDiscount = () => {
    newDiscount.discount_type = 'general';
    newDiscount.discount_percent = 5;
    newDiscount.product_type = null;
    newDiscount.product_family_id = null;
    newDiscount.min_amount = null;
    newDiscount.valid_from = null;
    newDiscount.valid_to = null;
    newDiscount.notes = null;
    showNewDiscount.value = false;
};

const saveNewDiscount = () => {
    if (!props.clientId) return;
    savingDiscount.value = true;
    router.post(
        route('clients.discounts.store', props.clientId),
        { ...newDiscount },
        {
            preserveScroll: true,
            onSuccess: () => resetNewDiscount(),
            onFinish: () => { savingDiscount.value = false; },
        }
    );
};

const startEditDiscount = (d: DiscountData) => {
    editingDiscountId.value = d.id;
    editDiscount.discount_type = d.discount_type;
    editDiscount.discount_percent = Number(d.discount_percent);
    editDiscount.product_type = d.product_type;
    editDiscount.product_family_id = d.product_family_id;
    editDiscount.min_amount = d.min_amount ? Number(d.min_amount) : null;
    editDiscount.valid_from = d.valid_from;
    editDiscount.valid_to = d.valid_to;
    editDiscount.notes = d.notes;
};

const cancelEditDiscount = () => {
    editingDiscountId.value = null;
};

const saveEditDiscount = () => {
    if (!props.clientId || !editingDiscountId.value) return;
    savingDiscount.value = true;
    router.put(
        route('clients.discounts.update', [props.clientId, editingDiscountId.value]),
        { ...editDiscount },
        {
            preserveScroll: true,
            onSuccess: () => { editingDiscountId.value = null; },
            onFinish: () => { savingDiscount.value = false; },
        }
    );
};

const deleteDiscount = (discountId: number) => {
    if (!props.clientId) return;
    router.delete(
        route('clients.discounts.destroy', [props.clientId, discountId]),
        { preserveScroll: true }
    );
};

const provinces = [
    'Álava', 'Albacete', 'Alicante', 'Almería', 'Asturias', 'Ávila', 'Badajoz',
    'Barcelona', 'Burgos', 'Cáceres', 'Cádiz', 'Cantabria', 'Castellón', 'Ciudad Real',
    'Córdoba', 'A Coruña', 'Cuenca', 'Girona', 'Granada', 'Guadalajara', 'Gipuzkoa',
    'Huelva', 'Huesca', 'Illes Balears', 'Jaén', 'León', 'Lleida', 'Lugo', 'Madrid',
    'Málaga', 'Murcia', 'Navarra', 'Ourense', 'Palencia', 'Las Palmas', 'Pontevedra',
    'La Rioja', 'Salamanca', 'Santa Cruz de Tenerife', 'Segovia', 'Sevilla', 'Soria',
    'Tarragona', 'Teruel', 'Toledo', 'Valencia', 'Valladolid', 'Bizkaia', 'Zamora', 'Zaragoza',
    'Ceuta', 'Melilla',
];
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-8">
        <!-- Tipo e identificación -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('clients.section_identification') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="type" :value="$t('clients.type_label')" />
                    <select
                        id="type"
                        v-model="form.type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="customer">{{ $t('clients.type_client') }}</option>
                        <option value="supplier">{{ $t('clients.type_supplier') }}</option>
                        <option value="both">{{ $t('clients.type_client_supplier') }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div>
                    <InputLabel for="legal_name" :value="$t('clients.legal_name')" />
                    <TextInput
                        id="legal_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.legal_name"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.legal_name" />
                </div>

                <div>
                    <InputLabel for="nif" :value="$t('clients.tax_id')" />
                    <TextInput
                        id="nif"
                        type="text"
                        class="mt-1 block w-full uppercase"
                        v-model="form.nif"
                        required
                        :placeholder="$t('clients.tax_id_placeholder')"
                    />
                    <InputError class="mt-2" :message="form.errors.nif" />
                </div>

                <div>
                    <InputLabel for="trade_name" :value="$t('clients.trade_name')" />
                    <TextInput
                        id="trade_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.trade_name"
                    />
                    <InputError class="mt-2" :message="form.errors.trade_name" />
                </div>
            </div>
        </div>

        <!-- Dirección -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('clients.section_address') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-3">
                    <InputLabel for="address_street" :value="$t('clients.address')" />
                    <TextInput
                        id="address_street"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address_street"
                        :placeholder="$t('clients.address_placeholder')"
                    />
                    <InputError class="mt-2" :message="form.errors.address_street" />
                </div>

                <div>
                    <InputLabel for="address_city" :value="$t('clients.city')" />
                    <TextInput
                        id="address_city"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address_city"
                    />
                    <InputError class="mt-2" :message="form.errors.address_city" />
                </div>

                <div>
                    <InputLabel for="address_postal_code" :value="$t('clients.postal_code')" />
                    <TextInput
                        id="address_postal_code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address_postal_code"
                        :placeholder="$t('clients.postal_code_placeholder')"
                    />
                    <InputError class="mt-2" :message="form.errors.address_postal_code" />
                </div>

                <div>
                    <InputLabel for="address_province" :value="$t('clients.province')" />
                    <select
                        id="address_province"
                        v-model="form.address_province"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ $t('common.select') }}</option>
                        <option v-for="p in provinces" :key="p" :value="p">{{ p }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.address_province" />
                </div>
            </div>
        </div>

        <!-- Contacto -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('clients.section_contact') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="contact_person" :value="$t('clients.contact_person')" />
                    <TextInput
                        id="contact_person"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.contact_person"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_person" />
                </div>

                <div>
                    <InputLabel for="email" :value="$t('clients.email')" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="phone" :value="$t('clients.phone')" />
                    <TextInput
                        id="phone"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <InputLabel for="website" :value="$t('clients.web')" />
                    <TextInput
                        id="website"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.website"
                        :placeholder="$t('clients.web_placeholder')"
                    />
                    <InputError class="mt-2" :message="form.errors.website" />
                </div>
            </div>
        </div>

        <!-- Pago -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('clients.section_payment') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="payment_method" :value="$t('clients.payment_method')" />
                    <select
                        id="payment_method"
                        v-model="form.payment_method"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="transfer">{{ $t('common.payment_transfer') }}</option>
                        <option value="cash">{{ $t('common.payment_cash') }}</option>
                        <option value="card">{{ $t('common.payment_card') }}</option>
                        <option value="check">{{ $t('common.payment_check') }}</option>
                        <option value="direct_debit">{{ $t('common.payment_direct_debit') }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.payment_method" />
                </div>

                <div>
                    <InputLabel for="payment_terms_days" :value="$t('clients.payment_days')" />
                    <TextInput
                        id="payment_terms_days"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.payment_terms_days"
                        min="0"
                        max="365"
                    />
                    <InputError class="mt-2" :message="form.errors.payment_terms_days" />
                </div>

                <div v-if="paymentTemplates && paymentTemplates.length > 0">
                    <InputLabel for="payment_template_id" :value="$t('clients.payment_template')" />
                    <select
                        id="payment_template_id"
                        v-model="form.payment_template_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">{{ $t('clients.no_template') }}</option>
                        <option v-for="t in paymentTemplates" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.payment_template_id" />
                </div>

                <div>
                    <InputLabel for="iban" :value="$t('clients.iban')" />
                    <TextInput
                        id="iban"
                        type="text"
                        class="mt-1 block w-full uppercase"
                        v-model="form.iban"
                        :placeholder="$t('clients.iban_placeholder')"
                    />
                    <InputError class="mt-2" :message="form.errors.iban" />
                </div>
            </div>
        </div>

        <!-- Notas -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('clients.section_notes') }}</h3>
            <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :placeholder="$t('clients.notes_placeholder')"
            />
            <InputError class="mt-2" :message="form.errors.notes" />
        </div>

        <!-- Descuentos (only in edit mode) -->
        <div v-if="clientId" class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">{{ $t('clients.section_discounts') }}</h3>
                <button
                    v-if="!showNewDiscount"
                    type="button"
                    @click="showNewDiscount = true"
                    class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
                >
                    <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    {{ $t('clients.add_discount') }}
                </button>
            </div>

            <!-- Discount list -->
            <div v-if="discounts && discounts.length > 0" class="mb-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('clients.col_discount_type') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('clients.col_discount_pct') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('clients.col_discount_detail') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('clients.col_discount_validity') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('clients.col_discount_notes') }}</th>
                            <th class="px-3 py-2 w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <!-- Display row -->
                        <template v-for="d in discounts" :key="d.id">
                            <tr v-if="editingDiscountId !== d.id">
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-blue-100 text-blue-800': d.discount_type === 'general',
                                            'bg-green-100 text-green-800': d.discount_type === 'agreement',
                                            'bg-purple-100 text-purple-800': d.discount_type === 'type',
                                            'bg-amber-100 text-amber-800': d.discount_type === 'family',
                                        }"
                                    >
                                        {{ discountTypeLabels[d.discount_type] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-sm text-right font-semibold text-gray-900">{{ Number(d.discount_percent) }}%</td>
                                <td class="px-3 py-2 text-sm text-gray-500">
                                    <span v-if="d.discount_type === 'type'">{{ d.product_type === 'product' ? $t('clients.product_type_products') : $t('clients.product_type_services') }}</span>
                                    <span v-else-if="d.discount_type === 'family'">{{ d.product_family?.name || '—' }}</span>
                                    <span v-else-if="d.discount_type === 'agreement' && d.min_amount">Min: {{ Number(d.min_amount).toFixed(2) }} &euro;</span>
                                    <span v-else>—</span>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-500">
                                    <span v-if="d.valid_from || d.valid_to">
                                        {{ d.valid_from || '...' }} — {{ d.valid_to || '...' }}
                                    </span>
                                    <span v-else>{{ $t('common.always') }}</span>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-400">{{ d.notes || '' }}</td>
                                <td class="px-3 py-2 flex gap-1">
                                    <button type="button" @click="startEditDiscount(d)" class="rounded p-1 text-gray-400 hover:text-indigo-600" :title="$t('common.edit')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    </button>
                                    <button type="button" @click="deleteDiscount(d.id)" class="rounded p-1 text-gray-400 hover:text-red-600" :title="$t('common.delete')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                            <!-- Edit row -->
                            <tr v-else class="bg-indigo-50">
                                <td class="px-3 py-2">
                                    <select v-model="editDiscount.discount_type" class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="general">{{ $t('clients.discount_type_short_general') }}</option>
                                        <option value="agreement">{{ $t('clients.discount_type_short_agreement') }}</option>
                                        <option value="type">{{ $t('clients.discount_type_short_by_type') }}</option>
                                        <option value="family">{{ $t('clients.discount_type_short_by_family') }}</option>
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" v-model.number="editDiscount.discount_percent" min="0.01" max="100" step="0.1" class="block w-20 rounded-md border-gray-300 text-sm text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </td>
                                <td class="px-3 py-2">
                                    <select v-if="editDiscount.discount_type === 'type'" v-model="editDiscount.product_type" class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option :value="null">{{ $t('common.select') }}</option>
                                        <option value="product">{{ $t('clients.product_type_products') }}</option>
                                        <option value="service">{{ $t('clients.product_type_services') }}</option>
                                    </select>
                                    <select v-else-if="editDiscount.discount_type === 'family'" v-model="editDiscount.product_family_id" class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option :value="null">{{ $t('common.select') }}</option>
                                        <option v-for="f in productFamilies" :key="f.id" :value="f.id">{{ f.parent_id ? '— ' : '' }}{{ f.name }}</option>
                                    </select>
                                    <input v-else-if="editDiscount.discount_type === 'agreement'" type="number" v-model.number="editDiscount.min_amount" min="0" step="0.01" :placeholder="$t('clients.min_amount_short')" class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex gap-1">
                                        <input type="date" v-model="editDiscount.valid_from" class="block w-28 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        <input type="date" v-model="editDiscount.valid_to" class="block w-28 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" v-model="editDiscount.notes" maxlength="500" class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </td>
                                <td class="px-3 py-2 flex gap-1">
                                    <button type="button" @click="saveEditDiscount" :disabled="savingDiscount" class="rounded bg-indigo-600 px-2 py-1 text-xs text-white hover:bg-indigo-500 disabled:opacity-50">OK</button>
                                    <button type="button" @click="cancelEditDiscount" class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 hover:bg-gray-300">X</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div v-else-if="!showNewDiscount" class="mb-4 rounded-lg border-2 border-dashed border-gray-300 p-4 text-center">
                <p class="text-sm text-gray-500">{{ $t('clients.no_discounts') }}</p>
            </div>

            <!-- New discount form -->
            <div v-if="showNewDiscount" class="rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                <h4 class="mb-3 text-sm font-medium text-indigo-900">{{ $t('clients.new_discount') }}</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.discount_type') }}</label>
                        <select v-model="newDiscount.discount_type" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="general">{{ $t('clients.discount_type_general') }}</option>
                            <option value="agreement">{{ $t('clients.discount_type_agreement') }}</option>
                            <option value="type">{{ $t('clients.discount_type_by_type') }}</option>
                            <option value="family">{{ $t('clients.discount_type_by_family') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.discount_pct') }}</label>
                        <input type="number" v-model.number="newDiscount.discount_percent" min="0.01" max="100" step="0.1" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div v-if="newDiscount.discount_type === 'type'">
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.product_type') }}</label>
                        <select v-model="newDiscount.product_type" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option :value="null">{{ $t('common.select') }}</option>
                            <option value="product">{{ $t('clients.product_type_products') }}</option>
                            <option value="service">{{ $t('clients.product_type_services') }}</option>
                        </select>
                    </div>
                    <div v-if="newDiscount.discount_type === 'family'">
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.family') }}</label>
                        <select v-model="newDiscount.product_family_id" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option :value="null">{{ $t('common.select') }}</option>
                            <option v-for="f in productFamilies" :key="f.id" :value="f.id">{{ f.parent_id ? '— ' : '' }}{{ f.name }}</option>
                        </select>
                    </div>
                    <div v-if="newDiscount.discount_type === 'agreement'">
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.min_amount') }}</label>
                        <input type="number" v-model.number="newDiscount.min_amount" min="0" step="0.01" :placeholder="$t('clients.min_amount_placeholder')" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.valid_from') }}</label>
                        <input type="date" v-model="newDiscount.valid_from" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.valid_until') }}</label>
                        <input type="date" v-model="newDiscount.valid_to" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-xs font-medium text-gray-600">{{ $t('clients.discount_notes') }}</label>
                        <input type="text" v-model="newDiscount.notes" maxlength="500" :placeholder="$t('clients.discount_notes_placeholder')" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="button" @click="saveNewDiscount" :disabled="savingDiscount" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                        {{ $t('clients.save_discount') }}
                    </button>
                    <button type="button" @click="resetNewDiscount" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        {{ $t('common.cancel') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <Link :href="route('clients.index')">
                <SecondaryButton type="button">{{ $t('common.cancel') }}</SecondaryButton>
            </Link>
            <PrimaryButton :disabled="form.processing">
                {{ submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
