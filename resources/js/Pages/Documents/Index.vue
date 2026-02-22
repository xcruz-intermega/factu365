<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import type { Column } from '@/Components/DataTable.vue';

const props = defineProps<{
    documents: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: any[];
    };
    documentType: string;
    documentTypeLabel: string;
    filters: {
        search?: string;
        status?: string;
        sort?: string;
        dir?: string;
    };
    statuses: Array<{ value: string; label: string }>;
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const sortBy = ref(props.filters.sort || '');
const sortDir = ref<'asc' | 'desc'>((props.filters.dir as 'asc' | 'desc') || 'desc');

const columns: Column[] = [
    { key: 'number', label: 'Número', sortable: true },
    { key: 'client', label: 'Cliente' },
    { key: 'issue_date', label: 'Fecha', sortable: true },
    { key: 'total', label: 'Total', sortable: true, class: 'text-right' },
    { key: 'status', label: 'Estado', sortable: true },
];

const statusColors: Record<string, 'gray' | 'green' | 'blue' | 'yellow' | 'red' | 'indigo' | 'purple'> = {
    draft: 'gray',
    finalized: 'blue',
    sent: 'indigo',
    paid: 'green',
    partial: 'yellow',
    overdue: 'red',
    cancelled: 'red',
    registered: 'blue',
    created: 'blue',
    accepted: 'green',
    rejected: 'red',
    converted: 'purple',
};

const statusLabels: Record<string, string> = {
    draft: 'Borrador',
    finalized: 'Finalizada',
    sent: 'Enviada',
    paid: 'Pagada',
    partial: 'Pago parcial',
    overdue: 'Vencida',
    cancelled: 'Anulada',
    registered: 'Registrada',
    created: 'Creado',
    accepted: 'Aceptado',
    rejected: 'Rechazado',
    converted: 'Convertido',
};

const formatCurrency = (val: number | string) => {
    const num = typeof val === 'string' ? parseFloat(val) : val;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(num);
};

const formatDate = (val: string) => {
    if (!val) return '';
    const d = new Date(val);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const applyFilters = () => {
    router.get(route('documents.index', { type: props.documentType }), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
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

const handleStatusFilter = (e: Event) => {
    statusFilter.value = (e.target as HTMLSelectElement).value;
    applyFilters();
};

// Delete confirmation
const deleteDialog = ref(false);
const deleteTarget = ref<any>(null);
const deleting = ref(false);

const confirmDelete = (doc: any) => {
    deleteTarget.value = doc;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('documents.destroy', { type: props.documentType, document: deleteTarget.value.id }), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="documentTypeLabel + 's'" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ documentTypeLabel }}s</h1>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-4">
                <div class="w-full max-w-sm">
                    <SearchInput
                        :model-value="search"
                        @update:model-value="handleSearch"
                        placeholder="Buscar por número o cliente..."
                    />
                </div>
                <select
                    :value="statusFilter"
                    @change="handleStatusFilter"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Todos los estados</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>
            <Link
                :href="route('documents.create', { type: documentType })"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Nuevo {{ documentTypeLabel.toLowerCase() }}
            </Link>
        </div>

        <!-- Table -->
        <DataTable
            :columns="columns"
            :rows="documents.data"
            :pagination="documents"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            @sort="handleSort"
            :empty-message="`No se encontraron ${documentTypeLabel.toLowerCase()}s.`"
        >
            <template #cell-number="{ row }">
                <Link
                    :href="route('documents.edit', { type: documentType, document: row.id })"
                    class="font-medium text-indigo-600 hover:text-indigo-900"
                >
                    {{ row.number || 'Borrador' }}
                </Link>
            </template>

            <template #cell-client="{ row }">
                <span v-if="row.client">
                    {{ row.client.trade_name || row.client.legal_name }}
                    <span class="text-gray-400 text-xs ml-1">{{ row.client.nif }}</span>
                </span>
                <span v-else class="text-gray-400 italic">Sin cliente</span>
            </template>

            <template #cell-issue_date="{ value }">
                {{ formatDate(value) }}
            </template>

            <template #cell-total="{ value }">
                <span class="font-medium">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-status="{ value }">
                <Badge :color="statusColors[value] || 'gray'">
                    {{ statusLabels[value] || value }}
                </Badge>
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('documents.edit', { type: documentType, document: row.id })"
                        class="text-indigo-600 hover:text-indigo-900"
                    >
                        Editar
                    </Link>
                    <button
                        v-if="['draft', 'created'].includes(row.status)"
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
            title="Eliminar documento"
            :message="`¿Estás seguro de que quieres eliminar este documento? Esta acción no se puede deshacer.`"
            confirm-label="Eliminar"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
