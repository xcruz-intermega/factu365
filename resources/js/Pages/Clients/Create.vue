<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ClientForm from './Partials/ClientForm.vue';

const props = defineProps<{
    paymentTemplates: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    type: 'customer',
    legal_name: '',
    trade_name: '',
    nif: '',
    address_street: '',
    address_city: '',
    address_postal_code: '',
    address_province: '',
    address_country: 'ES',
    phone: '',
    email: '',
    website: '',
    contact_person: '',
    payment_terms_days: 30,
    payment_template_id: null as number | null,
    payment_method: 'transfer',
    iban: '',
    notes: '',
});

const submit = () => {
    form.post(route('clients.store'));
};
</script>

<template>
    <Head title="Nuevo cliente" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Nuevo cliente</h1>
        </template>

        <ClientForm :form="form" :payment-templates="paymentTemplates" submit-label="Crear cliente" @submit="submit" />
    </AppLayout>
</template>
