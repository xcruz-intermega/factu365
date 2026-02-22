<script setup lang="ts">
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import SearchSelect from '@/Components/SearchSelect.vue';
import type { SearchSelectOption } from '@/Components/SearchSelect.vue';
import { Link, router } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

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
    }>;
    submitLabel: string;
    families?: FamilyOption[];
    productId?: number;
    components?: ProductComponentData[];
    allProducts?: AllProduct[];
}>();

const emit = defineEmits<{
    submit: [];
}>();

const showExemption = computed(() => Number(props.form.vat_rate) === 0);

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

const exemptionCodes = [
    { value: 'E1', label: 'E1 - Exenta por el artículo 20' },
    { value: 'E2', label: 'E2 - Exenta por el artículo 21' },
    { value: 'E3', label: 'E3 - Exenta por el artículo 22' },
    { value: 'E4', label: 'E4 - Exenta por el artículo 23 y 24' },
    { value: 'E5', label: 'E5 - Exenta por el artículo 25' },
    { value: 'E6', label: 'E6 - Exenta por otros' },
];

const units = [
    { value: 'unit', label: 'Unidad' },
    { value: 'hour', label: 'Hora' },
    { value: 'day', label: 'Día' },
    { value: 'month', label: 'Mes' },
    { value: 'kg', label: 'Kilogramo' },
    { value: 'm', label: 'Metro' },
    { value: 'm2', label: 'Metro²' },
    { value: 'l', label: 'Litro' },
    { value: 'pack', label: 'Pack' },
];
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-8">
        <!-- Datos básicos -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Datos del producto</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="type" value="Tipo *" />
                    <select
                        id="type"
                        v-model="form.type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="product">Producto</option>
                        <option value="service">Servicio</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div>
                    <InputLabel for="reference" value="Referencia" />
                    <TextInput
                        id="reference"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.reference"
                        placeholder="REF-001"
                    />
                    <InputError class="mt-2" :message="form.errors.reference" />
                </div>

                <div>
                    <InputLabel for="unit" value="Unidad de medida" />
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
                    <InputLabel for="product_family_id" value="Familia" />
                    <select
                        id="product_family_id"
                        v-model="form.product_family_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">Sin familia</option>
                        <option v-for="f in families" :key="f.id" :value="f.id">
                            {{ f.parent_id ? '— ' : '' }}{{ f.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.product_family_id" />
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <InputLabel for="name" value="Nombre *" />
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
                    <InputLabel for="description" value="Descripción" />
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

        <!-- Precio e impuestos -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Precio e impuestos</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="unit_price" value="Precio unitario (sin IVA) *" />
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
                    <InputLabel for="vat_rate" value="Tipo de IVA *" />
                    <select
                        id="vat_rate"
                        v-model="form.vat_rate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="21">21% - General</option>
                        <option :value="10">10% - Reducido</option>
                        <option :value="4">4% - Superreducido</option>
                        <option :value="0">0% - Exento</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.vat_rate" />
                </div>

                <div v-if="showExemption">
                    <InputLabel for="exemption_code" value="Causa de exención *" />
                    <select
                        id="exemption_code"
                        v-model="form.exemption_code"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar...</option>
                        <option v-for="e in exemptionCodes" :key="e.value" :value="e.value">{{ e.label }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.exemption_code" />
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.irpf_applicable" />
                        <span class="ms-2 text-sm text-gray-600">Aplicar retención IRPF</span>
                    </label>
                    <InputError class="mt-2" :message="form.errors.irpf_applicable" />
                </div>
            </div>
        </div>

        <!-- Escandallo (only in edit mode when productId is available) -->
        <div v-if="productId" class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Escandallo (componentes)</h3>
                <span v-if="components && components.length > 0" class="text-sm text-gray-500">
                    Coste total: <span class="font-semibold text-gray-900">{{ formatCurrency(totalComponentCost) }}</span>
                </span>
            </div>

            <!-- Component list -->
            <div v-if="components && components.length > 0" class="mb-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Componente</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Referencia</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Cantidad</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Precio ud.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Coste</th>
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
                                    title="Eliminar componente"
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
                <p class="text-sm text-gray-500">Sin componentes. Producto simple.</p>
            </div>

            <!-- Add component form -->
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600">Añadir componente</label>
                    <div class="mt-0.5">
                        <SearchSelect
                            v-model="newComponentId"
                            :options="componentOptions"
                            placeholder="Buscar producto..."
                        />
                    </div>
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-600">Cantidad</label>
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
                    Añadir
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <Link :href="route('products.index')">
                <SecondaryButton type="button">Cancelar</SecondaryButton>
            </Link>
            <PrimaryButton :disabled="form.processing">
                {{ submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
