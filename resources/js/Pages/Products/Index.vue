<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import type { Column } from '@/Components/DataTable.vue';

const props = defineProps<{
    products: {
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
        family?: string;
        sort?: string;
        dir?: string;
    };
    families: Array<{ id: number; name: string; parent_id: number | null }>;
}>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const familyFilter = ref(props.filters.family || '');
const sortBy = ref(props.filters.sort || '');
const sortDir = ref<'asc' | 'desc'>((props.filters.dir as 'asc' | 'desc') || 'asc');

const columns = computed<Column[]>(() => [
    { key: 'reference', label: trans('products.col_ref'), sortable: true },
    { key: 'name', label: trans('products.col_name'), sortable: true },
    { key: 'family', label: trans('products.col_family') },
    { key: 'type', label: trans('products.col_type'), sortable: true },
    { key: 'unit_price', label: trans('products.col_price'), sortable: true, class: 'text-right' },
    { key: 'vat_rate', label: trans('products.col_vat'), sortable: true, class: 'text-right' },
]);

const formatCurrency = (val: number | string) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(Number(val));
};

const applyFilters = () => {
    router.get(route('products.index'), {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        family: familyFilter.value || undefined,
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

const deleteDialog = ref(false);
const deleteTarget = ref<any>(null);
const deleting = ref(false);

const confirmDelete = (product: any) => {
    deleteTarget.value = product;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('products.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('products.title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('products.title_full') }}</h1>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-4">
                <div class="w-full max-w-sm">
                    <SearchInput
                        :model-value="search"
                        @update:model-value="handleSearch"
                        :placeholder="$t('products.search_placeholder')"
                    />
                </div>
                <select
                    :value="typeFilter"
                    @change="handleTypeFilter"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">{{ $t('products.all_types') }}</option>
                    <option value="product">{{ $t('products.type_products') }}</option>
                    <option value="service">{{ $t('products.type_services') }}</option>
                </select>
                <select
                    v-if="families.length > 0"
                    :value="familyFilter"
                    @change="familyFilter = ($event.target as HTMLSelectElement).value; applyFilters()"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">{{ $t('products.all_families') }}</option>
                    <option v-for="f in families" :key="f.id" :value="f.id">{{ f.parent_id ? '— ' : '' }}{{ f.name }}</option>
                </select>
            </div>
            <Link
                :href="route('products.create')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                {{ $t('products.new_product') }}
            </Link>
        </div>

        <!-- Table -->
        <DataTable
            :columns="columns"
            :rows="products.data"
            :pagination="products"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            @sort="handleSort"
            :empty-message="$t('products.no_products')"
        >
            <template #cell-family="{ row }">
                <span v-if="row.family" class="text-sm text-gray-600">{{ row.family.name }}</span>
                <span v-else class="text-gray-400">—</span>
            </template>

            <template #cell-type="{ value }">
                <Badge :color="value === 'product' ? 'blue' : 'green'">
                    {{ value === 'product' ? $t('products.type_product') : $t('products.type_service') }}
                </Badge>
            </template>

            <template #cell-unit_price="{ value }">
                <span class="font-mono">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-vat_rate="{ value }">
                {{ value }}%
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('products.edit', row.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                        :title="$t('common.edit')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487" /></svg>
                    </Link>
                    <button
                        @click="confirmDelete(row)"
                        class="text-red-600 hover:text-red-900"
                        :title="$t('common.delete')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Delete Confirmation -->
        <ConfirmDialog
            :show="deleteDialog"
            :title="trans('products.delete_title')"
            :message="trans('products.delete_message', { name: deleteTarget?.name || '' })"
            :confirm-label="trans('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
