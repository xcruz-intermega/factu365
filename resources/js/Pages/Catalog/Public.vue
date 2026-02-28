<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

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
        family?: string;
    };
    families: Array<{ id: number; name: string; parent_id: number | null }>;
    company: {
        legal_name: string;
        trade_name: string | null;
        phone: string | null;
        email: string | null;
        website: string | null;
    };
}>();

const search = ref(props.filters.search || '');
const familyFilter = ref(props.filters.family || '');

const formatCurrency = (val: number | string) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(Number(val));
};

const applyFilters = () => {
    router.get(route('catalog.public'), {
        search: search.value || undefined,
        family: familyFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

let searchTimeout: ReturnType<typeof setTimeout>;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
};

const pdfUrl = route('catalog.public.pdf');
</script>

<template>
    <Head :title="(company.trade_name || company.legal_name) + ' — ' + $t('products.public_catalog_title')" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="border-b border-gray-200 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ company.trade_name || company.legal_name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $t('products.public_catalog_subtitle') }}</p>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Toolbar -->
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 gap-4">
                    <div class="w-full max-w-sm">
                        <input
                            v-model="search"
                            @input="handleSearch"
                            type="text"
                            :placeholder="$t('products.search_placeholder')"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
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
                <a
                    :href="pdfUrl"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    {{ $t('products.download_catalog_pdf') }}
                </a>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
                >
                    <div class="aspect-square bg-gray-50">
                        <img
                            v-if="product.image_path"
                            :src="route('catalog.product.image', product.id)"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <svg class="h-16 w-16 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="p-4">
                        <p v-if="product.family" class="mb-1 text-xs font-medium text-indigo-600">{{ product.family.name }}</p>
                        <h3 class="text-sm font-semibold text-gray-900">{{ product.name }}</h3>
                        <p v-if="product.reference" class="mt-0.5 text-xs text-gray-500">{{ product.reference }}</p>
                        <p v-if="product.description" class="mt-1 line-clamp-2 text-xs text-gray-500">{{ product.description }}</p>
                        <p class="mt-3 text-lg font-bold text-gray-900">{{ formatCurrency(product.unit_price) }}</p>
                        <p class="text-xs text-gray-400">+ {{ product.vat_rate }}% {{ $t('products.col_vat') }}</p>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="products.data.length === 0" class="mt-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                </svg>
                <p class="mt-4 text-sm text-gray-500">{{ $t('products.no_products') }}</p>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="mt-8 flex items-center justify-between">
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
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ company.trade_name || company.legal_name }}</p>
                        <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-gray-500">
                            <span v-if="company.phone">{{ company.phone }}</span>
                            <span v-if="company.email">{{ company.email }}</span>
                            <span v-if="company.website">{{ company.website }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">Factu365</p>
                </div>
            </div>
        </footer>
    </div>
</template>
