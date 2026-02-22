<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
interface Series {
    id: number;
    document_type: string;
    prefix: string;
    next_number: number;
    padding: number;
    fiscal_year: number;
    is_default: boolean;
}

const props = defineProps<{
    series: Series[];
    documentTypes: Array<{ value: string; label: string }>;
}>();

const typeLabels: Record<string, string> = {};
props.documentTypes.forEach(t => { typeLabels[t.value] = t.label; });

// New series form
const showForm = ref(false);
const form = useForm({
    document_type: 'invoice',
    prefix: '',
    next_number: 1,
    padding: 5,
    fiscal_year: new Date().getFullYear(),
    is_default: true,
});

const submitNew = () => {
    form.post(route('settings.series.store'), {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        },
    });
};

// Edit inline
const editingId = ref<number | null>(null);
const editForm = useForm({
    prefix: '',
    next_number: 1,
    padding: 5,
    is_default: false,
});

const startEdit = (s: Series) => {
    editingId.value = s.id;
    editForm.prefix = s.prefix;
    editForm.next_number = s.next_number;
    editForm.padding = s.padding;
    editForm.is_default = s.is_default;
};

const saveEdit = (id: number) => {
    editForm.put(route('settings.series.update', id), {
        onSuccess: () => { editingId.value = null; },
    });
};

const cancelEdit = () => { editingId.value = null; };

// Delete (modal confirmation)
const deleteDialog = ref(false);
const deleteTarget = ref<Series | null>(null);
const deleting = ref(false);

const confirmDelete = (s: Series) => {
    deleteTarget.value = s;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('settings.series.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('settings.series_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('settings.series_title') }}</h1>
        </template>

        <SettingsNav current="series" />

        <!-- New series button -->
        <div class="mb-4 flex justify-end">
            <button @click="showForm = !showForm" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ showForm ? $t('common.cancel') : $t('settings.new_series') }}
            </button>
        </div>

        <!-- New series form -->
        <div v-if="showForm" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <form @submit.prevent="submitNew" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.doc_type') }}</label>
                    <select v-model="form.document_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option v-for="t in documentTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.prefix') }}</label>
                    <input type="text" v-model="form.prefix" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :placeholder="$t('settings.prefix_placeholder')" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.next_number') }}</label>
                    <input type="number" v-model="form.next_number" min="1" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.padding') }}</label>
                    <input type="number" v-model="form.padding" min="1" max="10" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.fiscal_year') }}</label>
                    <input type="number" v-model="form.fiscal_year" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div class="flex items-end">
                    <button type="submit" :disabled="form.processing" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">{{ $t('common.create') }}</button>
                </div>
            </form>
        </div>

        <!-- Series table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_prefix') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_next_number') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_padding') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_year') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">{{ $t('settings.col_default') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="s in series" :key="s.id" class="hover:bg-gray-50">
                        <template v-if="editingId === s.id">
                            <td class="px-4 py-2 text-sm">{{ typeLabels[s.document_type] || s.document_type }}</td>
                            <td class="px-4 py-2"><input type="text" v-model="editForm.prefix" class="w-full rounded-md border-gray-300 text-sm" /></td>
                            <td class="px-4 py-2"><input type="number" v-model="editForm.next_number" min="1" class="w-20 rounded-md border-gray-300 text-right text-sm" /></td>
                            <td class="px-4 py-2"><input type="number" v-model="editForm.padding" min="1" max="10" class="w-16 rounded-md border-gray-300 text-right text-sm" /></td>
                            <td class="px-4 py-2 text-center text-sm">{{ s.fiscal_year }}</td>
                            <td class="px-4 py-2 text-center"><input type="checkbox" v-model="editForm.is_default" class="rounded border-gray-300 text-indigo-600" /></td>
                            <td class="px-4 py-2 text-right">
                                <button @click="saveEdit(s.id)" class="text-sm text-green-600 hover:text-green-900 mr-2">{{ $t('common.save') }}</button>
                                <button @click="cancelEdit" class="text-sm text-gray-400 hover:text-gray-600">{{ $t('common.cancel') }}</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">{{ typeLabels[s.document_type] || s.document_type }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-mono text-gray-700">{{ s.prefix }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ s.next_number }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ s.padding }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-700">{{ s.fiscal_year }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <span v-if="s.is_default" class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">{{ $t('common.yes') }}</span>
                                <span v-else class="text-xs text-gray-400">{{ $t('common.no') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="startEdit(s)" class="text-indigo-600 hover:text-indigo-900" :title="$t('common.edit')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487" /></svg>
                                    </button>
                                    <button @click="confirmDelete(s)" class="text-red-600 hover:text-red-900" :title="$t('common.delete')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 00-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="series.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">{{ $t('settings.no_series') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('settings.delete_series_title')"
            :message="trans('settings.delete_series_message', { prefix: deleteTarget?.prefix || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
