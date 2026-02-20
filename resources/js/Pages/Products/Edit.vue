<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

const props = defineProps<{
    product: {
        id: number;
        type: string;
        reference: string;
        name: string;
        description: string;
        unit_price: number;
        vat_rate: number;
        exemption_code: string;
        irpf_applicable: boolean;
        unit: string;
    };
}>();

const form = useForm({
    type: props.product.type || 'product',
    reference: props.product.reference || '',
    name: props.product.name || '',
    description: props.product.description || '',
    unit_price: props.product.unit_price || 0,
    vat_rate: props.product.vat_rate || 21,
    exemption_code: props.product.exemption_code || '',
    irpf_applicable: props.product.irpf_applicable || false,
    unit: props.product.unit || 'unit',
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <Head :title="`Editar ${product.name}`" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Editar: {{ product.name }}</h1>
        </template>

        <ProductForm :form="form" submit-label="Guardar cambios" @submit="submit" />
    </AppLayout>
</template>
