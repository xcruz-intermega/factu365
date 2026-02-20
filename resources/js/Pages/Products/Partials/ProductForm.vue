<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Link } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

const props = defineProps<{
    form: InertiaForm<{
        type: string;
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
}>();

const emit = defineEmits<{
    submit: [];
}>();

const showExemption = computed(() => Number(props.form.vat_rate) === 0);

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
