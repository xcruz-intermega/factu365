<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

interface Category {
    id: number;
    name: string;
    code: string | null;
    parent_id: number | null;
    sort_order: number;
    children: Category[];
}

interface CategoryOption {
    id: number;
    name: string;
    parent_id: number | null;
}

const props = defineProps<{
    categories: Category[];
    allCategories: CategoryOption[];
}>();

// New category form
const showNew = ref(false);
const newForm = useForm({
    name: '',
    code: '',
    parent_id: null as number | null,
    sort_order: 0,
});

const submitNew = () => {
    newForm.post(route('expense-categories.store'), {
        onSuccess: () => {
            showNew.value = false;
            newForm.reset();
        },
    });
};

// Edit
const editingId = ref<number | null>(null);
const editForm = useForm({
    name: '',
    code: '',
    parent_id: null as number | null,
    sort_order: 0,
});

const startEdit = (category: Category | CategoryOption) => {
    editingId.value = category.id;
    editForm.name = category.name;
    editForm.code = (category as Category).code || '';
    editForm.parent_id = category.parent_id;
    editForm.sort_order = (category as Category).sort_order || 0;
};

const cancelEdit = () => {
    editingId.value = null;
};

const submitEdit = (id: number) => {
    editForm.put(route('expense-categories.update', id), {
        onSuccess: () => { editingId.value = null; },
    });
};

// Delete (modal confirmation)
const deleteDialog = ref(false);
const deleteTarget = ref<Category | null>(null);
const deleting = ref(false);

const confirmDelete = (category: Category) => {
    deleteTarget.value = category;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('expense-categories.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};

// Flatten categories for display with indentation
const flattenCategories = (categories: Category[], depth = 0): Array<Category & { depth: number }> => {
    const result: Array<Category & { depth: number }> = [];
    for (const c of categories) {
        result.push({ ...c, depth });
        if (c.children?.length) {
            result.push(...flattenCategories(c.children, depth + 1));
        }
    }
    return result;
};
</script>

<template>
    <Head :title="$t('expenses.categories_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('expenses.categories_title') }}</h1>
        </template>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('expenses.categories_section') }}</h3>
                <button
                    @click="showNew = !showNew"
                    class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
                >
                    <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    {{ $t('expenses.new_category') }}
                </button>
            </div>

            <!-- New category form -->
            <div v-if="showNew" class="mb-4 rounded-md border border-indigo-200 bg-indigo-50 p-4">
                <form @submit.prevent="submitNew" class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700">{{ $t('expenses.category_name') }}</label>
                        <input
                            type="text"
                            v-model="newForm.name"
                            class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :class="{ 'border-red-500': newForm.errors.name }"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('expenses.category_code') }}</label>
                        <input
                            type="text"
                            v-model="newForm.code"
                            class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">{{ $t('expenses.category_parent') }}</label>
                        <select
                            v-model="newForm.parent_id"
                            class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option :value="null">{{ $t('expenses.category_no_parent') }}</option>
                            <option v-for="c in allCategories" :key="c.id" :value="c.id">
                                {{ c.parent_id ? '— ' : '' }}{{ c.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            :disabled="newForm.processing"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                        >
                            {{ $t('common.create') }}
                        </button>
                        <button
                            type="button"
                            @click="showNew = false; newForm.reset()"
                            class="rounded-md bg-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Categories table -->
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('expenses.col_category_name') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('expenses.col_category_code') }}</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('expenses.col_category_order') }}</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="category in flattenCategories(categories)" :key="category.id">
                        <template v-if="editingId === category.id">
                            <td class="px-4 py-2">
                                <input
                                    type="text"
                                    v-model="editForm.name"
                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </td>
                            <td class="px-4 py-2">
                                <input
                                    type="text"
                                    v-model="editForm.code"
                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </td>
                            <td class="px-4 py-2">
                                <input
                                    type="number"
                                    v-model="editForm.sort_order"
                                    min="0"
                                    class="block w-20 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right"
                                />
                            </td>
                            <td class="px-4 py-2 text-right">
                                <button @click="submitEdit(category.id)" class="text-sm text-indigo-600 hover:text-indigo-900 mr-2">{{ $t('common.save') }}</button>
                                <button @click="cancelEdit" class="text-sm text-gray-600 hover:text-gray-900">{{ $t('common.cancel') }}</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="px-4 py-2 text-sm">
                                <span :style="{ paddingLeft: category.depth * 20 + 'px' }">
                                    <span v-if="category.depth > 0" class="text-gray-400 mr-1">└</span>
                                    {{ category.name }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ category.code || '—' }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-500">{{ category.sort_order }}</td>
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="startEdit(category)" class="text-indigo-600 hover:text-indigo-900" :title="$t('common.edit')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487" /></svg>
                                    </button>
                                    <button @click="confirmDelete(category)" class="text-red-600 hover:text-red-900" :title="$t('common.delete')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                            {{ $t('expenses.no_categories') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('expenses.delete_category_title')"
            :message="trans('expenses.delete_category_message', { name: deleteTarget?.name || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
