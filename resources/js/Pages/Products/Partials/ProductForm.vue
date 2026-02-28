<script setup lang="ts">
import { ref, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import type { SearchSelectOption } from '@/Components/SearchSelect.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
import type { VatRate } from '@/types/vatRate';

interface FamilyOption {
    id: number;
    name: string;
    parent_id: number | null;
}

interface ComponentProduct {
    id: number;
    name: string;
    reference: string;
    unit_price: number;
}

interface ProductComponentData {
    id: number;
    parent_product_id: number;
    component_product_id: number;
    quantity: string;
    component: ComponentProduct;
}

interface AllProduct {
    id: number;
    name: string;
    reference: string;
    unit_price: number;
}

const props = defineProps<{
    form: InertiaForm<{
        type: string;
        product_family_id: number | null;
        reference: string;
        name: string;
        description: string;
        unit_price: number;
        vat_rate: number;
        exemption_code: string;
        irpf_applicable: boolean;
        unit: string;
        image: File | null;
        track_stock: boolean;
        stock_quantity: number;
        minimum_stock: number;
        allow_negative_stock: boolean;
        stock_mode: string;
    }>;
    submitLabel: string;
    families?: FamilyOption[];
    productId?: number;
    components?: ProductComponentData[];
    allProducts?: AllProduct[];
    imageUrl?: string | null;
}>();

const imagePreview = ref<string | null>(props.imageUrl || null);

const handleImageChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        props.form.image = input.files[0];
        imagePreview.value = URL.createObjectURL(input.files[0]);
    }
};

const removeImage = () => {
    if (props.productId) {
        router.delete(route('products.image.delete', props.productId), {
            preserveScroll: true,
            onSuccess: () => {
                props.form.image = null;
                imagePreview.value = null;
            },
        });
    } else {
        props.form.image = null;
        imagePreview.value = null;
    }
};

const emit = defineEmits<{
    submit: [];
}>();

const vatRates = computed(() => (usePage().props.vatRates || []) as VatRate[]);
const showExemption = computed(() => {
    const vr = vatRates.value.find(v => Number(v.rate) === Number(props.form.vat_rate));
    return vr ? vr.is_exempt : Number(props.form.vat_rate) === 0;
});

// Escandallo state
const newComponentId = ref<number | null>(null);
const newComponentQty = ref<number>(1);
const addingComponent = ref(false);

const componentOptions = computed<SearchSelectOption[]>(() => {
    if (!props.allProducts) return [];
    const existingIds = (props.components || []).map(c => c.component_product_id);
    return props.allProducts
        .filter(p => !existingIds.includes(p.id))
        .map(p => ({
            value: p.id,
            label: p.name,
            sublabel: p.reference || undefined,
        }));
});

const totalComponentCost = computed(() => {
    if (!props.components) return 0;
    return props.components.reduce((sum, c) => {
        return sum + Number(c.quantity) * Number(c.component.unit_price);
    }, 0);
});

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};

const addComponent = () => {
    if (!newComponentId.value || !props.productId) return;
    addingComponent.value = true;
    router.post(
        route('products.components.store', props.productId),
        {
            component_product_id: newComponentId.value,
            quantity: newComponentQty.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                newComponentId.value = null;
                newComponentQty.value = 1;
                addingComponent.value = false;
            },
        }
    );
};

const removeComponent = (componentId: number) => {
    if (!props.productId) return;
    router.delete(
        route('products.components.destroy', [props.productId, componentId]),
        { preserveScroll: true }
    );
};

const exemptionCodes = computed(() => [
    { value: 'E1', label: trans('products.exemption_e1') },
    { value: 'E2', label: trans('products.exemption_e2') },
    { value: 'E3', label: trans('products.exemption_e3') },
    { value: 'E4', label: trans('products.exemption_e4') },
    { value: 'E5', label: trans('products.exemption_e5') },
    { value: 'E6', label: trans('products.exemption_e6') },
]);

const units = computed(() => [
    { value: 'unit', label: trans('products.unit_unidad') },
    { value: 'hour', label: trans('products.unit_hora') },
    { value: 'day', label: trans('products.unit_dia') },
    { value: 'month', label: trans('products.unit_mes') },
    { value: 'kg', label: trans('products.unit_kg') },
    { value: 'm', label: trans('products.unit_metro') },
    { value: 'm2', label: trans('products.unit_m2') },
    { value: 'l', label: trans('products.unit_litro') },
    { value: 'pack', label: trans('products.unit_pack') },
]);
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-8">
        <!-- Datos básicos -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('products.section_data') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="type" :value="$t('products.type_label')" />
                    <select
                        id="type"
                        v-model="form.type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="product">{{ $t('products.type_product') }}</option>
                        <option value="service">{{ $t('products.type_service') }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div>
                    <InputLabel for="reference" :value="$t('products.reference')" />
                    <TextInput
                        id="reference"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.reference"
                        :placeholder="$t('products.reference_placeholder')"
                    />
                    <InputError class="mt-2" :message="form.errors.reference" />
                </div>

                <div>
                    <InputLabel for="unit" :value="$t('products.unit_measure')" />
                    <select
                        id="unit"
                        v-model="form.unit"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="u in units" :key="u.value" :value="u.value">{{ u.label }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.unit" />
                </div>

                <div v-if="families && families.length > 0">
                    <InputLabel for="product_family_id" :value="$t('products.family')" />
                    <select
                        id="product_family_id"
                        v-model="form.product_family_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">{{ $t('products.no_family') }}</option>
                        <option v-for="f in families" :key="f.id" :value="f.id">
                            {{ f.parent_id ? '— ' : '' }}{{ f.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.product_family_id" />
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <InputLabel for="name" :value="$t('products.name')" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <InputLabel for="description" :value="$t('products.description')" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>
            </div>
        </div>

        <!-- Imagen del producto -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('products.section_image') }}</h3>
            <div class="flex items-start gap-6">
                <div v-if="imagePreview" class="shrink-0">
                    <img :src="imagePreview" alt="" class="h-32 w-32 rounded-lg border border-gray-200 object-cover" />
                </div>
                <div v-else class="flex h-32 w-32 shrink-0 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50">
                    <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                    </svg>
                </div>
                <div class="space-y-3">
                    <input
                        type="file"
                        @change="handleImageChange"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                    />
                    <p class="text-xs text-gray-500">{{ $t('products.image_help') }}</p>
                    <InputError class="mt-1" :message="form.errors.image" />
                    <button
                        v-if="imagePreview"
                        type="button"
                        @click="removeImage"
                        class="text-sm text-red-600 hover:text-red-800"
                    >
                        {{ $t('products.remove_image') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Precio e impuestos -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('products.section_pricing') }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="unit_price" :value="$t('products.unit_price')" />
                    <div class="relative mt-1">
                        <TextInput
                            id="unit_price"
                            type="number"
                            class="block w-full pr-12"
                            v-model="form.unit_price"
                            required
                            min="0"
                            step="0.01"
                        />
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 sm:text-sm">&euro;</span>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.unit_price" />
                </div>

                <div>
                    <InputLabel for="vat_rate" :value="$t('products.vat_type')" />
                    <select
                        id="vat_rate"
                        v-model="form.vat_rate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="vr in vatRates" :key="vr.id" :value="Number(vr.rate)">{{ Number(vr.rate) }}% - {{ vr.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.vat_rate" />
                </div>

                <div v-if="showExemption">
                    <InputLabel for="exemption_code" :value="$t('products.exemption_cause')" />
                    <select
                        id="exemption_code"
                        v-model="form.exemption_code"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ $t('common.select') }}</option>
                        <option v-for="e in exemptionCodes" :key="e.value" :value="e.value">{{ e.label }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.exemption_code" />
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.irpf_applicable" />
                        <span class="ms-2 text-sm text-gray-600">{{ $t('products.apply_irpf') }}</span>
                    </label>
                    <InputError class="mt-2" :message="form.errors.irpf_applicable" />
                </div>
            </div>
        </div>

        <!-- Stock (only for products, not services) -->
        <div v-if="form.type === 'product'" class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $t('products.section_stock') }}</h3>

            <div class="space-y-4">
                <!-- Track stock toggle -->
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.track_stock" />
                    <span class="ms-2 text-sm text-gray-600">{{ $t('products.track_stock') }}</span>
                </label>
                <InputError class="mt-1" :message="form.errors.track_stock" />

                <!-- Stock fields (visible when tracking) -->
                <div v-if="form.track_stock" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <InputLabel for="stock_quantity" :value="$t('products.stock_quantity')" />
                        <TextInput
                            id="stock_quantity"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.stock_quantity"
                            step="0.0001"
                            min="0"
                            :disabled="!!productId"
                        />
                        <p v-if="productId" class="mt-1 text-xs text-gray-500">
                            <Link :href="route('products.stock-movements', productId)" class="text-indigo-600 hover:underline">
                                {{ $t('products.stock_adjust') }} →
                            </Link>
                        </p>
                        <InputError class="mt-2" :message="form.errors.stock_quantity" />
                    </div>

                    <div>
                        <InputLabel for="minimum_stock" :value="$t('products.minimum_stock')" />
                        <TextInput
                            id="minimum_stock"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.minimum_stock"
                            step="0.0001"
                            min="0"
                        />
                        <InputError class="mt-2" :message="form.errors.minimum_stock" />
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center">
                            <Checkbox v-model:checked="form.allow_negative_stock" />
                            <span class="ms-2 text-sm text-gray-600">{{ $t('products.allow_negative_stock') }}</span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.allow_negative_stock" />
                    </div>

                    <!-- Stock mode (only if composite product) -->
                    <div v-if="components && components.length > 0" class="sm:col-span-2 lg:col-span-3">
                        <InputLabel :value="$t('products.stock_mode')" />
                        <div class="mt-2 flex gap-6">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.stock_mode" value="self" class="text-indigo-600 focus:ring-indigo-500" />
                                <span class="ms-2 text-sm text-gray-600">{{ $t('products.stock_mode_self') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.stock_mode" value="components" class="text-indigo-600 focus:ring-indigo-500" />
                                <span class="ms-2 text-sm text-gray-600">{{ $t('products.stock_mode_components') }}</span>
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.stock_mode" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Escandallo (only in edit mode when productId is available) -->
        <div v-if="productId" class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">{{ $t('products.section_components') }}</h3>
                <span v-if="components && components.length > 0" class="text-sm text-gray-500">
                    {{ $t('products.total_cost') }}<span class="font-semibold text-gray-900">{{ formatCurrency(totalComponentCost) }}</span>
                </span>
            </div>

            <!-- Component list -->
            <div v-if="components && components.length > 0" class="mb-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_component') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_reference') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('products.col_quantity') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('products.col_unit_price') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('products.col_cost') }}</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="comp in components" :key="comp.id">
                            <td class="px-3 py-2 text-sm text-gray-900">{{ comp.component.name }}</td>
                            <td class="px-3 py-2 text-sm text-gray-500">{{ comp.component.reference || '—' }}</td>
                            <td class="px-3 py-2 text-sm text-right text-gray-900">{{ Number(comp.quantity) }}</td>
                            <td class="px-3 py-2 text-sm text-right text-gray-500">{{ formatCurrency(Number(comp.component.unit_price)) }}</td>
                            <td class="px-3 py-2 text-sm text-right font-medium text-gray-900">{{ formatCurrency(Number(comp.quantity) * Number(comp.component.unit_price)) }}</td>
                            <td class="px-3 py-2">
                                <button
                                    type="button"
                                    @click="removeComponent(comp.id)"
                                    class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                    :title="$t('products.remove_component')"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="mb-4 rounded-lg border-2 border-dashed border-gray-300 p-4 text-center">
                <p class="text-sm text-gray-500">{{ $t('products.no_components') }}</p>
            </div>

            <!-- Add component form -->
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600">{{ $t('products.add_component') }}</label>
                    <div class="mt-0.5">
                        <SearchSelect
                            v-model="newComponentId"
                            :options="componentOptions"
                            :placeholder="$t('products.search_product')"
                        />
                    </div>
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-600">{{ $t('products.quantity') }}</label>
                    <input
                        type="number"
                        v-model.number="newComponentQty"
                        min="0.0001"
                        step="0.0001"
                        class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>
                <button
                    type="button"
                    @click="addComponent"
                    :disabled="!newComponentId || addingComponent"
                    class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-100 disabled:opacity-50"
                >
                    {{ $t('common.add') }}
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <Link :href="route('products.index')">
                <SecondaryButton type="button">{{ $t('common.cancel') }}</SecondaryButton>
            </Link>
            <PrimaryButton :disabled="form.processing">
                {{ submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
