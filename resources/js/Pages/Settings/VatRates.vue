<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import type { VatRate } from '@/types/vatRate';

const props = defineProps<{
    vatRatesList: VatRate[];
}>();

// New VAT rate form
const showForm = ref(false);
const form = useForm({
    name: '',
    rate: 0,
    surcharge_rate: 0,
    is_default: false,
    is_exempt: false,
    sort_order: 0,
});

const submitNew = () => {
    form.post(route('settings.vat-rates.store'), {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        },
    });
};

// Edit inline
const editingId = ref<number | null>(null);
const editForm = useForm({
    name: '',
    rate: 0,
    surcharge_rate: 0,
    is_default: false,
    is_exempt: false,
    sort_order: 0,
});

const startEdit = (vr: VatRate) => {
    editingId.value = vr.id;
    editForm.name = vr.name;
    editForm.rate = Number(vr.rate);
    editForm.surcharge_rate = Number(vr.surcharge_rate);
    editForm.is_default = vr.is_default;
    editForm.is_exempt = vr.is_exempt;
    editForm.sort_order = vr.sort_order;
};

const saveEdit = (id: number) => {
    editForm.put(route('settings.vat-rates.update', id), {
        onSuccess: () => { editingId.value = null; },
    });
};

const cancelEdit = () => { editingId.value = null; };

// Delete (modal confirmation)
const deleteDialog = ref(false);
const deleteTarget = ref<VatRate | null>(null);
const deleting = ref(false);

const confirmDelete = (vr: VatRate) => {
    deleteTarget.value = vr;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('settings.vat-rates.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('settings.vat_rates_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('settings.vat_rates_title') }}</h1>
        </template>

        <!-- New VAT rate button -->
        <div class="mb-4 flex justify-end">
            <button @click="showForm = !showForm" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ showForm ? $t('common.cancel') : $t('settings.new_vat_rate') }}
            </button>
        </div>

        <!-- New VAT rate form -->
        <div v-if="showForm" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <form @submit.prevent="submitNew" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.vat_rate_name') }}</label>
                    <input type="text" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :placeholder="$t('settings.vat_rate_name')" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.vat_rate_rate') }}</label>
                    <input type="number" v-model="form.rate" step="0.01" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    <p v-if="form.errors.rate" class="mt-1 text-xs text-red-600">{{ form.errors.rate }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.vat_rate_surcharge') }}</label>
                    <input type="number" v-model="form.surcharge_rate" step="0.01" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div class="flex items-end gap-4">
                    <label class="flex items-center gap-1.5">
                        <input type="checkbox" v-model="form.is_default" class="rounded border-gray-300 text-indigo-600" />
                        <span class="text-xs text-gray-600">{{ $t('settings.vat_rate_default') }}</span>
                    </label>
                </div>
                <div class="flex items-end gap-4">
                    <label class="flex items-center gap-1.5">
                        <input type="checkbox" v-model="form.is_exempt" class="rounded border-gray-300 text-indigo-600" />
                        <span class="text-xs text-gray-600">{{ $t('settings.vat_rate_exempt') }}</span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.vat_rate_sort') }}</label>
                    <input type="number" v-model="form.sort_order" min="0" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div class="flex items-end">
                    <button type="submit" :disabled="form.processing" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">{{ $t('common.create') }}</button>
                </div>
            </form>
        </div>

        <!-- VAT rates table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_vat_name') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_vat_rate') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_vat_surcharge') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_vat_default') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_vat_exempt') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_vat_sort') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="vr in vatRatesList" :key="vr.id" class="hover:bg-gray-50">
                        <template v-if="editingId === vr.id">
                            <td class="px-4 py-2"><input type="text" v-model="editForm.name" class="w-full rounded-md border-gray-300 text-sm" /></td>
                            <td class="px-4 py-2"><input type="number" v-model="editForm.rate" step="0.01" min="0" max="100" class="w-24 rounded-md border-gray-300 text-right text-sm" /></td>
                            <td class="px-4 py-2"><input type="number" v-model="editForm.surcharge_rate" step="0.01" min="0" max="100" class="w-24 rounded-md border-gray-300 text-right text-sm" /></td>
                            <td class="px-4 py-2 text-center"><input type="checkbox" v-model="editForm.is_default" class="rounded border-gray-300 text-indigo-600" /></td>
                            <td class="px-4 py-2 text-center"><input type="checkbox" v-model="editForm.is_exempt" class="rounded border-gray-300 text-indigo-600" /></td>
                            <td class="px-4 py-2"><input type="number" v-model="editForm.sort_order" min="0" class="w-16 rounded-md border-gray-300 text-right text-sm" /></td>
                            <td class="px-4 py-2 text-right">
                                <button @click="saveEdit(vr.id)" class="text-sm text-green-600 hover:text-green-900 mr-2">{{ $t('common.save') }}</button>
                                <button @click="cancelEdit" class="text-sm text-gray-400 hover:text-gray-600">{{ $t('common.cancel') }}</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                                {{ vr.name }}
                                <span v-if="vr.is_default" class="ml-1.5 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">{{ $t('settings.vat_rate_default') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ Number(vr.rate).toFixed(2) }}%</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ Number(vr.surcharge_rate).toFixed(2) }}%</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <span v-if="vr.is_default" class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">{{ $t('common.yes') }}</span>
                                <span v-else class="text-xs text-gray-400">{{ $t('common.no') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <span v-if="vr.is_exempt" class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">{{ $t('common.yes') }}</span>
                                <span v-else class="text-xs text-gray-400">{{ $t('common.no') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ vr.sort_order }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="startEdit(vr)" class="text-indigo-600 hover:text-indigo-900" :title="$t('common.edit')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487" /></svg>
                                    </button>
                                    <button @click="confirmDelete(vr)" class="text-red-600 hover:text-red-900" :title="$t('common.delete')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 00-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="vatRatesList.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">{{ $t('settings.no_vat_rates') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('settings.delete_vat_rate_title')"
            :message="trans('settings.delete_vat_rate_message', { name: deleteTarget?.name || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
