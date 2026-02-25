<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps<{
    product: {
        id: number;
        name: string;
        reference: string;
        stock_quantity: number;
        minimum_stock: number;
        track_stock: boolean;
    };
    movements: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: any[];
    };
}>();

const adjustForm = useForm({
    quantity: 0 as number,
    notes: '',
});

const submitAdjustment = () => {
    adjustForm.post(route('products.stock-adjustment', props.product.id), {
        preserveScroll: true,
        onSuccess: () => {
            adjustForm.reset();
        },
    });
};

const typeColors = computed(() => ({
    sale: 'red',
    purchase: 'green',
    adjustment: 'blue',
    return: 'yellow',
    initial: 'gray',
}));

const typeLabel = (type: string): string => {
    const map: Record<string, string> = {
        sale: trans('products.movement_type_sale'),
        purchase: trans('products.movement_type_purchase'),
        adjustment: trans('products.movement_type_adjustment'),
        return: trans('products.movement_type_return'),
        initial: trans('products.movement_type_initial'),
    };
    return map[type] || type;
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const stockColor = computed(() => {
    const qty = Number(props.product.stock_quantity);
    const min = Number(props.product.minimum_stock);
    if (qty <= 0) return 'text-red-600';
    if (qty <= min) return 'text-amber-500';
    return 'text-green-600';
});
</script>

<template>
    <Head :title="$t('products.stock_movements_title', { name: product.name })" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900">{{ $t('products.stock_movements_title', { name: product.name }) }}</h1>
                <Link :href="route('products.edit', product.id)" class="text-sm text-indigo-600 hover:underline">
                    ← {{ $t('common.back') }}
                </Link>
            </div>
        </template>

        <!-- Current stock summary -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <div class="flex items-center gap-6">
                <div>
                    <span class="text-sm text-gray-500">{{ $t('products.stock_quantity') }}</span>
                    <p class="text-2xl font-bold" :class="stockColor">{{ Number(product.stock_quantity) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ $t('products.minimum_stock') }}</span>
                    <p class="text-lg text-gray-700">{{ Number(product.minimum_stock) }}</p>
                </div>
            </div>
        </div>

        <!-- Manual adjustment form -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('products.adjustment_title') }}</h3>
            <form @submit.prevent="submitAdjustment" class="flex items-end gap-3">
                <div class="w-32">
                    <label class="block text-xs font-medium text-gray-600">{{ $t('products.adjustment_quantity') }}</label>
                    <input
                        type="number"
                        v-model.number="adjustForm.quantity"
                        step="0.0001"
                        class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="adjustForm.errors.quantity" class="mt-1 text-xs text-red-600">{{ adjustForm.errors.quantity }}</p>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600">{{ $t('products.adjustment_notes') }}</label>
                    <input
                        type="text"
                        v-model="adjustForm.notes"
                        class="mt-0.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :placeholder="$t('products.adjustment_placeholder')"
                    />
                </div>
                <button
                    type="submit"
                    :disabled="adjustForm.processing || adjustForm.quantity === 0"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                >
                    {{ $t('products.stock_adjust') }}
                </button>
            </form>
        </div>

        <!-- Movements table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_date') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_document') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('products.col_movement_qty') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">{{ $t('products.col_stock_after') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_notes') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ $t('products.col_user') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="mov in movements.data" :key="mov.id">
                        <td class="px-4 py-2 text-sm text-gray-600">{{ formatDate(mov.created_at) }}</td>
                        <td class="px-4 py-2">
                            <Badge :color="(typeColors as any)[mov.type] || 'gray'">{{ typeLabel(mov.type) }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-sm">
                            <Link
                                v-if="mov.document"
                                :href="route('documents.edit', [mov.document.document_type, mov.document.id])"
                                class="text-indigo-600 hover:underline"
                            >
                                {{ mov.document.number || `#${mov.document.id}` }}
                            </Link>
                            <span v-else class="text-gray-400">—</span>
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-sm" :class="Number(mov.quantity) >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ Number(mov.quantity) >= 0 ? '+' : '' }}{{ Number(mov.quantity) }}
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-sm text-gray-700">{{ Number(mov.stock_after) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 max-w-xs truncate">{{ mov.notes || '—' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ mov.user?.name || '—' }}</td>
                    </tr>
                    <tr v-if="movements.data.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                            {{ $t('products.no_products') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="movements.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3">
                <div class="text-sm text-gray-500">
                    {{ movements.from }}–{{ movements.to }} / {{ movements.total }}
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="link in movements.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        class="inline-flex items-center rounded-md border px-3 py-1 text-sm"
                        :class="link.active ? 'border-indigo-500 bg-indigo-50 text-indigo-600' : 'border-gray-300 text-gray-500 hover:bg-gray-50'"
                        v-html="link.label"
                        :preserve-scroll="true"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
