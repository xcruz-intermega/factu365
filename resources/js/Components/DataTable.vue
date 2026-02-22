<script setup lang="ts">
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { router } from '@inertiajs/vue3';

export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    wrap?: boolean;
}

export interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = withDefaults(defineProps<{
    columns: Column[];
    rows: any[];
    pagination?: PaginationData;
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    routeName?: string;
    queryParams?: Record<string, any>;
    emptyMessage?: string;
    compact?: boolean;
}>(), {
    sortBy: '',
    sortDir: 'asc',
    emptyMessage: '',
    compact: false,
});

const resolvedEmptyMessage = computed(() => props.emptyMessage || trans('common.no_records'));

const emit = defineEmits<{
    sort: [key: string, dir: 'asc' | 'desc'];
}>();

const handleSort = (column: Column) => {
    if (!column.sortable) return;

    const dir = props.sortBy === column.key && props.sortDir === 'asc' ? 'desc' : 'asc';
    emit('sort', column.key, dir);
};

const goToPage = (url: string | null) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const sortIcon = (column: Column) => {
    if (!column.sortable || props.sortBy !== column.key) return '';
    return props.sortDir === 'asc' ? '↑' : '↓';
};
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            scope="col"
                            class="py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            :class="[compact ? 'px-3' : 'px-6', col.class, { 'cursor-pointer hover:text-gray-700 select-none': col.sortable }]"
                            @click="handleSort(col)"
                        >
                            <span class="flex items-center gap-1">
                                {{ col.label }}
                                <span v-if="sortIcon(col)" class="text-indigo-600">{{ sortIcon(col) }}</span>
                            </span>
                        </th>
                        <th scope="col" class="relative py-3" :class="compact ? 'px-3' : 'px-6'">
                            <span class="sr-only">{{ $t('common.actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-if="rows.length === 0">
                        <td :colspan="columns.length + 1" class="py-12 text-center text-sm text-gray-500" :class="compact ? 'px-3' : 'px-6'">
                            {{ resolvedEmptyMessage }}
                        </td>
                    </tr>
                    <tr v-for="(row, index) in rows" :key="row.id || index" class="hover:bg-gray-50">
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            class="text-sm text-gray-900"
                            :class="[compact ? 'px-3 py-3' : 'px-6 py-4', col.class, { 'whitespace-nowrap': !col.wrap }]"
                        >
                            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                {{ row[col.key] }}
                            </slot>
                        </td>
                        <td class="whitespace-nowrap text-right text-sm font-medium" :class="compact ? 'px-3 py-3' : 'px-6 py-4'">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">
                    {{ $t('common.showing') }}
                    <span class="font-medium">{{ pagination.from }}</span>
                    {{ $t('common.to') }}
                    <span class="font-medium">{{ pagination.to }}</span>
                    {{ $t('common.of') }}
                    <span class="font-medium">{{ pagination.total }}</span>
                    {{ $t('common.results') }}
                </p>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <button
                        v-for="link in pagination.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url || link.active"
                        class="relative inline-flex items-center px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0"
                        :class="{
                            'bg-indigo-600 text-white focus-visible:outline-indigo-600': link.active,
                            'text-gray-900 hover:bg-gray-50': !link.active && link.url,
                            'text-gray-400 cursor-not-allowed': !link.url,
                        }"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </div>
</template>
