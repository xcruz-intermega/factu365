<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import ClientForm from './Partials/ClientForm.vue';

const props = defineProps<{
    paymentTemplates: Array<{ id: number; name: string }>;
}>();

const page = usePage();
const urlParams = new URLSearchParams(page.url.split('?')[1] || '');
const initialType = ['customer', 'supplier', 'both'].includes(urlParams.get('type') || '') ? urlParams.get('type')! : 'customer';

const pageTitle = computed(() => {
    if (initialType === 'supplier') return trans('clients.new_supplier');
    return trans('clients.new_client');
});

const form = useForm({
    type: initialType,
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
    <Head :title="pageTitle" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ pageTitle }}</h1>
        </template>

        <ClientForm :form="form" :payment-templates="paymentTemplates" :submit-label="$t('clients.create_client')" @submit="submit" />
    </AppLayout>
</template>
