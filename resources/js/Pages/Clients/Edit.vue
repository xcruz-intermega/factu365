<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ClientForm from './Partials/ClientForm.vue';

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

const props = defineProps<{
    client: {
        id: number;
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
        discounts: DiscountData[];
    };
    paymentTemplates: Array<{ id: number; name: string }>;
    productFamilies: Array<{ id: number; name: string; parent_id: number | null }>;
}>();

const form = useForm({
    type: props.client.type || 'customer',
    legal_name: props.client.legal_name || '',
    trade_name: props.client.trade_name || '',
    nif: props.client.nif || '',
    address_street: props.client.address_street || '',
    address_city: props.client.address_city || '',
    address_postal_code: props.client.address_postal_code || '',
    address_province: props.client.address_province || '',
    address_country: props.client.address_country || 'ES',
    phone: props.client.phone || '',
    email: props.client.email || '',
    website: props.client.website || '',
    contact_person: props.client.contact_person || '',
    payment_terms_days: props.client.payment_terms_days || 30,
    payment_template_id: props.client.payment_template_id ?? null,
    payment_method: props.client.payment_method || 'transfer',
    iban: props.client.iban || '',
    notes: props.client.notes || '',
});

const submit = () => {
    form.put(route('clients.update', props.client.id));
};
</script>

<template>
    <Head :title="`Editar ${client.legal_name}`" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Editar: {{ client.legal_name }}</h1>
        </template>

        <ClientForm
            :form="form"
            :payment-templates="paymentTemplates"
            :client-id="client.id"
            :discounts="client.discounts"
            :product-families="productFamilies"
            submit-label="Guardar cambios"
            @submit="submit"
        />
    </AppLayout>
</template>
