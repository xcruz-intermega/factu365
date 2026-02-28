<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchInput from '@/Components/SearchInput.vue';
import ReportToolbar from '@/Components/ReportToolbar.vue';

interface ProductData {
    id: number;
    name: string;
    reference: string | null;
    description: string | null;
    type: string;
    unit_price: string;
    vat_rate: string;
    image_path: string | null;
    family: { id: number; name: string } | null;
}

const props = defineProps<{
    products: {
        data: ProductData[];
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
    };
    families: Array<{ id: number; name: string; parent_id: number | null }>;
}>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const familyFilter = ref(props.filters.family || '');
const viewMode = ref<'grid' | 'table'>('grid');

onMounted(() => {
    const stored = localStorage.getItem('catalog-view-mode');
    if (stored === 'table' || stored === 'grid') {
        viewMode.value = stored;
    }
});

const setViewMode = (mode: 'grid' | 'table') => {
    viewMode.value = mode;
    localStorage.setItem('catalog-view-mode', mode);
};

const formatCurrency = (val: number | string) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(Number(val));
};

const applyFilters = () => {
    router.get(route('catalog.index'), {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        family: familyFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleSearch = (val: string) => {
    search.value = val;
    applyFilters();
};

const pdfUrl = computed(() => route('catalog.pdf'));
</script>

<template>
    <Head :title="$t('products.catalog_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('products.catalog_title') }}</h1>
            <div class="ml-4 flex items-center gap-2">
                <ReportToolbar :pdf-url="pdfUrl" :show-print="true" />
            </div>
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
                    @change="typeFilter = ($event.target as HTMLSelectElement).value; applyFilters()"
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

            <!-- View toggle -->
            <div class="inline-flex rounded-md shadow-sm">
                <button
                    type="button"
                    @click="setViewMode('grid')"
                    class="inline-flex items-center rounded-l-md border px-3 py-2 text-sm font-medium"
                    :class="viewMode === 'grid' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 z-10' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="setViewMode('table')"
                    class="-ml-px inline-flex items-center rounded-r-md border px-3 py-2 text-sm font-medium"
                    :class="viewMode === 'table' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 z-10' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <Link
                v-for="product in products.data"
                :key="product.id"
                :href="route('products.edit', product.id)"
                class="group overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
            >
                <div class="aspect-square bg-gray-50">
                    <img
                        v-if="product.image_path"
                        :src="route('products.image', product.id)"
                        :alt="product.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center">
                        <svg class="h-16 w-16 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                        </svg>
                    </div>
                </div>
                <div class="p-3">
                    <p v-if="product.family" class="mb-1 text-xs text-indigo-600">{{ product.family.name }}</p>
                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600">{{ product.name }}</h3>
                    <p v-if="product.reference" class="mt-0.5 text-xs text-gray-500">{{ product.reference }}</p>
                    <p class="mt-2 text-sm font-bold text-gray-900">{{ formatCurrency(product.unit_price) }}</p>
                </div>
            </Link>
        </div>

        <!-- Table View -->
        <div v-else class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-12 px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_image') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_ref') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_name') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_family') }}</th>
                        <th class="px-3 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('products.col_price') }}</th>
                        <th class="px-3 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('products.col_vat') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <img
                                v-if="product.image_path"
                                :src="route('products.image', product.id)"
                                alt=""
                                class="h-8 w-8 rounded object-cover"
                            />
                            <div v-else class="flex h-8 w-8 items-center justify-center rounded bg-gray-100">
                                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                </svg>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-500">{{ product.reference || '—' }}</td>
                        <td class="px-3 py-2">
                            <Link :href="route('products.edit', product.id)" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                {{ product.name }}
                            </Link>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-500">{{ product.family?.name || '—' }}</td>
                        <td class="px-3 py-2 text-right text-sm font-mono text-gray-900">{{ formatCurrency(product.unit_price) }}</td>
                        <td class="px-3 py-2 text-right text-sm text-gray-500">{{ product.vat_rate }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div v-if="products.data.length === 0" class="mt-8 text-center">
            <p class="text-sm text-gray-500">{{ $t('products.no_products') }}</p>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-700">
                {{ products.from }}–{{ products.to }} / {{ products.total }}
            </p>
            <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                <Link
                    v-for="link in products.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    class="relative inline-flex items-center border px-3 py-2 text-sm font-medium"
                    :class="[
                        link.active ? 'z-10 border-indigo-500 bg-indigo-50 text-indigo-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50',
                        !link.url ? 'cursor-default opacity-50' : '',
                    ]"
                    v-html="link.label"
                    preserve-state
                />
            </nav>
        </div>
    </AppLayout>
</template>
