<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import type { Column, PaginationData } from '@/Components/DataTable.vue';

const props = defineProps<{
    clients: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: any[];
    };
    filters: {
        search?: string;
        type?: string;
        sort?: string;
        dir?: string;
    };
}>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const sortBy = ref(props.filters.sort || '');
const sortDir = ref<'asc' | 'desc'>((props.filters.dir as 'asc' | 'desc') || 'asc');

const columns: Column[] = [
    { key: 'legal_name', label: 'Razón social', sortable: true },
    { key: 'nif', label: 'NIF/CIF', sortable: true },
    { key: 'type', label: 'Tipo', sortable: true },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Teléfono' },
];

const typeLabels: Record<string, string> = {
    customer: 'Cliente',
    supplier: 'Proveedor',
    both: 'Ambos',
};

const typeColors: Record<string, 'blue' | 'green' | 'purple'> = {
    customer: 'blue',
    supplier: 'green',
    both: 'purple',
};

const applyFilters = () => {
    router.get(route('clients.index'), {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        sort: sortBy.value || undefined,
        dir: sortDir.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleSort = (key: string, dir: 'asc' | 'desc') => {
    sortBy.value = key;
    sortDir.value = dir;
    applyFilters();
};

const handleSearch = (val: string) => {
    search.value = val;
    applyFilters();
};

const handleTypeFilter = (e: Event) => {
    typeFilter.value = (e.target as HTMLSelectElement).value;
    applyFilters();
};

// Delete confirmation
const deleteDialog = ref(false);
const deleteTarget = ref<any>(null);
const deleting = ref(false);

const confirmDelete = (client: any) => {
    deleteTarget.value = client;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('clients.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head title="Clientes" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Clientes</h1>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-4">
                <div class="w-full max-w-sm">
                    <SearchInput
                        :model-value="search"
                        @update:model-value="handleSearch"
                        placeholder="Buscar por nombre, NIF o email..."
                    />
                </div>
                <select
                    :value="typeFilter"
                    @change="handleTypeFilter"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Todos los tipos</option>
                    <option value="customer">Clientes</option>
                    <option value="supplier">Proveedores</option>
                    <option value="both">Ambos</option>
                </select>
            </div>
            <Link
                :href="route('clients.create')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Nuevo cliente
            </Link>
        </div>

        <!-- Table -->
        <DataTable
            :columns="columns"
            :rows="clients.data"
            :pagination="clients"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            @sort="handleSort"
            empty-message="No se encontraron clientes."
        >
            <template #cell-type="{ value }">
                <Badge :color="typeColors[value] || 'gray'">
                    {{ typeLabels[value] || value }}
                </Badge>
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('clients.edit', row.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                    >
                        Editar
                    </Link>
                    <button
                        @click="confirmDelete(row)"
                        class="text-red-600 hover:text-red-900"
                    >
                        Eliminar
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Delete Confirmation -->
        <ConfirmDialog
            :show="deleteDialog"
            title="Eliminar cliente"
            :message="`¿Estás seguro de que quieres eliminar a ${deleteTarget?.legal_name}? Esta acción se puede deshacer.`"
            confirm-label="Eliminar"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
