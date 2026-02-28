<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

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

const props = defineProps<{
    product: {
        id: number;
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
        image_path: string | null;
        track_stock: boolean;
        stock_quantity: number;
        minimum_stock: number;
        allow_negative_stock: boolean;
        stock_mode: string;
        components: ProductComponentData[];
    };
    families: Array<{ id: number; name: string; parent_id: number | null }>;
    allProducts: Array<{ id: number; name: string; reference: string; unit_price: number }>;
}>();

const imageUrl = props.product.image_path
    ? route('products.image', props.product.id)
    : null;

const form = useForm({
    _method: 'put',
    type: props.product.type || 'product',
    product_family_id: props.product.product_family_id ?? null,
    reference: props.product.reference || '',
    name: props.product.name || '',
    description: props.product.description || '',
    unit_price: props.product.unit_price || 0,
    vat_rate: props.product.vat_rate || 21,
    exemption_code: props.product.exemption_code || '',
    irpf_applicable: props.product.irpf_applicable || false,
    unit: props.product.unit || 'unit',
    image: null as File | null,
    track_stock: props.product.track_stock || false,
    stock_quantity: Number(props.product.stock_quantity) || 0,
    minimum_stock: Number(props.product.minimum_stock) || 0,
    allow_negative_stock: props.product.allow_negative_stock ?? true,
    stock_mode: props.product.stock_mode || 'self',
});

const submit = () => {
    form.post(route('products.update', props.product.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="$t('products.edit_product', { name: product.name })" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('products.edit_product', { name: product.name }) }}</h1>
        </template>

        <ProductForm
            :form="form"
            :families="families"
            :product-id="product.id"
            :components="product.components"
            :all-products="allProducts"
            :image-url="imageUrl"
            :submit-label="$t('common.save_changes')"
            @submit="submit"
        />
    </AppLayout>
</template>
