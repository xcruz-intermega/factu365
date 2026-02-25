<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

const props = defineProps<{
    families: Array<{ id: number; name: string; parent_id: number | null }>;
}>();

const form = useForm({
    type: 'product',
    product_family_id: null as number | null,
    reference: '',
    name: '',
    description: '',
    unit_price: 0,
    vat_rate: 21,
    exemption_code: '',
    irpf_applicable: false,
    unit: 'unit',
    track_stock: false,
    stock_quantity: 0,
    minimum_stock: 0,
    allow_negative_stock: true,
    stock_mode: 'self',
});

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head :title="$t('products.new_product')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('products.new_product') }}</h1>
        </template>

        <ProductForm :form="form" :families="families" :submit-label="$t('products.create_product')" @submit="submit" />
    </AppLayout>
</template>
