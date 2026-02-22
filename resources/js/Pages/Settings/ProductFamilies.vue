<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import SettingsNav from './Partials/SettingsNav.vue';

interface Family {
    id: number;
    name: string;
    code: string | null;
    parent_id: number | null;
    sort_order: number;
    children: Family[];
}

interface FamilyOption {
    id: number;
    name: string;
    parent_id: number | null;
}

const props = defineProps<{
    families: Family[];
    allFamilies: FamilyOption[];
}>();

// New family form
const showNew = ref(false);
const newForm = useForm({
    name: '',
    code: '',
    parent_id: null as number | null,
    sort_order: 0,
});

const submitNew = () => {
    newForm.post(route('settings.product-families.store'), {
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

const startEdit = (family: Family | FamilyOption) => {
    editingId.value = family.id;
    editForm.name = family.name;
    editForm.code = (family as Family).code || '';
    editForm.parent_id = family.parent_id;
    editForm.sort_order = (family as Family).sort_order || 0;
};

const cancelEdit = () => {
    editingId.value = null;
};

const submitEdit = (id: number) => {
    editForm.put(route('settings.product-families.update', id), {
        onSuccess: () => { editingId.value = null; },
    });
};

// Delete
const deleteDialog = ref(false);
const deleteTarget = ref<Family | null>(null);
const deleting = ref(false);

const confirmDelete = (family: Family) => {
    deleteTarget.value = family;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('settings.product-families.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};

// Flatten families for display with indentation
const flattenFamilies = (families: Family[], depth = 0): Array<Family & { depth: number }> => {
    const result: Array<Family & { depth: number }> = [];
    for (const f of families) {
        result.push({ ...f, depth });
        if (f.children?.length) {
            result.push(...flattenFamilies(f.children, depth + 1));
        }
    }
    return result;
};
</script>

<template>
    <Head title="Familias de producto" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Familias de producto</h1>
        </template>

        <SettingsNav current="product-families" />

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Familias</h3>
                <button
                    @click="showNew = !showNew"
                    class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
                >
                    <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    Nueva familia
                </button>
            </div>

            <!-- New family form -->
            <div v-if="showNew" class="mb-4 rounded-md border border-indigo-200 bg-indigo-50 p-4">
                <form @submit.prevent="submitNew" class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700">Nombre *</label>
                        <input
                            type="text"
                            v-model="newForm.name"
                            class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :class="{ 'border-red-500': newForm.errors.name }"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Código</label>
                        <input
                            type="text"
                            v-model="newForm.code"
                            class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Padre</label>
                        <select
                            v-model="newForm.parent_id"
                            class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option :value="null">Ninguno (raíz)</option>
                            <option v-for="f in allFamilies" :key="f.id" :value="f.id">
                                {{ f.parent_id ? '— ' : '' }}{{ f.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            :disabled="newForm.processing"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                        >
                            Crear
                        </button>
                        <button
                            type="button"
                            @click="showNew = false; newForm.reset()"
                            class="rounded-md bg-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Families table -->
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Código</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">Orden</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="family in flattenFamilies(families)" :key="family.id">
                        <template v-if="editingId === family.id">
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
                                <button @click="submitEdit(family.id)" class="text-sm text-indigo-600 hover:text-indigo-900 mr-2">Guardar</button>
                                <button @click="cancelEdit" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="px-4 py-2 text-sm">
                                <span :style="{ paddingLeft: family.depth * 20 + 'px' }">
                                    <span v-if="family.depth > 0" class="text-gray-400 mr-1">└</span>
                                    {{ family.name }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ family.code || '—' }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-500">{{ family.sort_order }}</td>
                            <td class="px-4 py-2 text-right">
                                <button @click="startEdit(family)" class="text-sm text-indigo-600 hover:text-indigo-900 mr-2">Editar</button>
                                <button @click="confirmDelete(family)" class="text-sm text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="families.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                            No hay familias creadas. Haz clic en "Nueva familia" para empezar.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            :show="deleteDialog"
            title="Eliminar familia"
            :message="`¿Estás seguro de que quieres eliminar '${deleteTarget?.name}'?`"
            confirm-label="Eliminar"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
