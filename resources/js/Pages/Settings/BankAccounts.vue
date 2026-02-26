<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

interface BankAccount {
    id: number;
    name: string;
    iban: string | null;
    initial_balance: string;
    opening_date: string;
    is_default: boolean;
    is_active: boolean;
    current_balance: number;
    entries_count: number;
}

const props = defineProps<{
    accounts: BankAccount[];
}>();

const showNew = ref(false);
const newForm = useForm({
    name: '',
    iban: '',
    initial_balance: 0,
    opening_date: new Date().toISOString().slice(0, 10),
    is_default: false,
});

const submitNew = () => {
    newForm.post(route('settings.bank-accounts.store'), {
        onSuccess: () => {
            showNew.value = false;
            newForm.reset();
        },
    });
};

const editingId = ref<number | null>(null);
const editForm = useForm({
    name: '',
    iban: '',
    initial_balance: 0,
    opening_date: '',
    is_default: false,
    is_active: true,
});

const startEdit = (account: BankAccount) => {
    editingId.value = account.id;
    editForm.name = account.name;
    editForm.iban = account.iban || '';
    editForm.initial_balance = parseFloat(account.initial_balance);
    editForm.opening_date = account.opening_date;
    editForm.is_default = account.is_default;
    editForm.is_active = account.is_active;
};

const submitEdit = (id: number) => {
    editForm.put(route('settings.bank-accounts.update', id), {
        onSuccess: () => { editingId.value = null; },
    });
};

const deleteDialog = ref(false);
const deleteTarget = ref<BankAccount | null>(null);
const deleting = ref(false);

const confirmDelete = (account: BankAccount) => {
    deleteTarget.value = account;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('settings.bank-accounts.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
};
</script>

<template>
    <Head :title="$t('treasury.bank_accounts_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('treasury.bank_accounts_title') }}</h1>
        </template>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('treasury.bank_accounts_title') }}</h3>
                <button
                    @click="showNew = !showNew"
                    class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
                >
                    <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    {{ $t('treasury.new_account') }}
                </button>
            </div>

            <!-- New account form -->
            <div v-if="showNew" class="mb-6 rounded-md border border-indigo-200 bg-indigo-50 p-4">
                <form @submit.prevent="submitNew" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.account_name') }}</label>
                            <input type="text" v-model="newForm.name" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                            <p v-if="newForm.errors.name" class="mt-1 text-xs text-red-600">{{ newForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.account_iban') }}</label>
                            <input type="text" v-model="newForm.iban" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" placeholder="ES00 0000 0000 0000 0000 0000" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.initial_balance') }}</label>
                            <input type="number" v-model.number="newForm.initial_balance" step="0.01" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.opening_date') }}</label>
                            <input type="date" v-model="newForm.opening_date" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" v-model="newForm.is_default" class="rounded border-gray-300 text-indigo-600" />
                            {{ $t('treasury.is_default') }}
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="newForm.processing" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50">{{ $t('common.create') }}</button>
                        <button type="button" @click="showNew = false" class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">{{ $t('common.cancel') }}</button>
                    </div>
                </form>
            </div>

            <!-- Accounts list -->
            <div class="space-y-4">
                <div v-for="account in accounts" :key="account.id" class="rounded-md border border-gray-200 p-4">
                    <template v-if="editingId === account.id">
                        <form @submit.prevent="submitEdit(account.id)" class="space-y-4">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.account_name') }}</label>
                                    <input type="text" v-model="editForm.name" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.account_iban') }}</label>
                                    <input type="text" v-model="editForm.iban" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.initial_balance') }}</label>
                                    <input type="number" v-model.number="editForm.initial_balance" step="0.01" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">{{ $t('treasury.opening_date') }}</label>
                                    <input type="date" v-model="editForm.opening_date" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" v-model="editForm.is_default" class="rounded border-gray-300 text-indigo-600" />
                                    {{ $t('treasury.is_default') }}
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" v-model="editForm.is_active" class="rounded border-gray-300 text-indigo-600" />
                                    {{ $t('treasury.is_active') }}
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" :disabled="editForm.processing" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50">{{ $t('common.save') }}</button>
                                <button type="button" @click="editingId = null" class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">{{ $t('common.cancel') }}</button>
                            </div>
                        </form>
                    </template>
                    <template v-else>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-gray-900">{{ account.name }}</span>
                                <span v-if="account.is_default" class="ml-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ $t('treasury.is_default') }}</span>
                                <span v-if="!account.is_active" class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Inactiva</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="startEdit(account)" class="text-indigo-600 hover:text-indigo-900" :title="$t('common.edit')">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487" /></svg>
                                </button>
                                <button @click="confirmDelete(account)" class="text-red-600 hover:text-red-900" :title="$t('common.delete')">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                            <span v-if="account.iban">IBAN: {{ account.iban }}</span>
                            <span>{{ $t('treasury.initial_balance') }}: {{ formatCurrency(parseFloat(account.initial_balance)) }}</span>
                            <span class="font-semibold" :class="account.current_balance >= 0 ? 'text-green-700' : 'text-red-700'">
                                {{ $t('treasury.account_balance') }}: {{ formatCurrency(account.current_balance) }}
                            </span>
                        </div>
                    </template>
                </div>

                <div v-if="accounts.length === 0" class="py-8 text-center text-sm text-gray-500">
                    {{ $t('treasury.no_accounts') }}
                </div>
            </div>
        </div>

        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('treasury.delete_account_title')"
            :message="trans('treasury.delete_account_message', { name: deleteTarget?.name || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
