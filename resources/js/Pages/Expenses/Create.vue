<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ExpenseForm from './Partials/ExpenseForm.vue';

interface Category {
    id: number;
    name: string;
    code: string;
}

interface Supplier {
    id: number;
    legal_name: string;
    trade_name: string | null;
    nif: string;
}

const props = defineProps<{
    categories: Category[];
    suppliers: Supplier[];
}>();

const form = useForm({
    expense_date: new Date().toISOString().split('T')[0],
    category_id: null as number | null,
    supplier_client_id: null as number | null,
    supplier_name: '',
    invoice_number: '',
    concept: '',
    subtotal: 0,
    vat_rate: 21,
    irpf_rate: 0,
    payment_status: 'pending',
    payment_date: '',
    payment_method: '',
    attachment: null as File | null,
    notes: '',
});

const submit = () => {
    form.post(route('expenses.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Nuevo gasto" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('expenses.index')"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-lg font-semibold text-gray-900">Nuevo gasto</h1>
            </div>
        </template>

        <ExpenseForm
            :form="form"
            :categories="categories"
            :suppliers="suppliers"
            @submit="submit"
        />
    </AppLayout>
</template>
