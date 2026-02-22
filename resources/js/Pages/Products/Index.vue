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
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('products.edit', row.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                    >
                        {{ $t('common.edit') }}
                    </Link>
                    <button
                        @click="confirmDelete(row)"
                        class="text-red-600 hover:text-red-900"
                    >
                        {{ $t('common.delete') }}
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
