<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

const form = useForm({
    type: 'product',
    reference: '',
    name: '',
    description: '',
    unit_price: 0,
    vat_rate: 21,
    exemption_code: '',
    irpf_applicable: false,
    unit: 'unit',
});

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Nuevo producto" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Nuevo producto</h1>
        </template>

        <ProductForm :form="form" submit-label="Crear producto" @submit="submit" />
    </AppLayout>
</template>
