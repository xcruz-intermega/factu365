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

interface Expense {
    id: number;
    expense_date: string;
    category_id: number | null;
    supplier_client_id: number | null;
    supplier_name: string | null;
    invoice_number: string | null;
    concept: string;
    subtotal: string;
    vat_rate: string;
    irpf_rate: string;
    payment_status: string;
    payment_date: string | null;
    payment_method: string | null;
    attachment_path: string | null;
    notes: string | null;
}

const props = defineProps<{
    expense: Expense;
    categories: Category[];
    suppliers: Supplier[];
}>();

const form = useForm({
    _method: 'put',
    expense_date: props.expense.expense_date,
    category_id: props.expense.category_id,
    supplier_client_id: props.expense.supplier_client_id,
    supplier_name: props.expense.supplier_name || '',
    invoice_number: props.expense.invoice_number || '',
    concept: props.expense.concept,
    subtotal: Number(props.expense.subtotal),
    vat_rate: Number(props.expense.vat_rate),
    irpf_rate: Number(props.expense.irpf_rate),
    payment_status: props.expense.payment_status,
    payment_date: props.expense.payment_date || '',
    payment_method: props.expense.payment_method || '',
    attachment: null as File | null,
    attachment_path: props.expense.attachment_path,
    notes: props.expense.notes || '',
});

const submit = () => {
    form.post(route('expenses.update', props.expense.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Editar gasto" />

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
                <h1 class="text-lg font-semibold text-gray-900">Editar gasto</h1>
            </div>
        </template>

        <ExpenseForm
            :form="form"
            :categories="categories"
            :suppliers="suppliers"
            :is-edit="true"
            @submit="submit"
        />
    </AppLayout>
</template>
