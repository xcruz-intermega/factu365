<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

interface SearchResult {
    type: string;
    label: string;
    detail: string | null;
    url: string;
}

const open = ref(false);
const query = ref('');
const results = ref<SearchResult[]>([]);
const loading = ref(false);
const selectedIndex = ref(0);
const inputRef = ref<HTMLInputElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout>;

const typeIcons: Record<string, string> = {
    client: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    product: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    expense: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
};

const typeLabels = computed<Record<string, string>>(() => ({
    client: trans('search.type_client'),
    product: trans('search.type_product'),
    document: trans('search.type_document'),
    expense: trans('search.type_expense'),
}));

const typeColors: Record<string, string> = {
    client: 'text-blue-500',
    product: 'text-emerald-500',
    document: 'text-indigo-500',
    expense: 'text-amber-500',
};

watch(query, (val) => {
    clearTimeout(debounceTimer);
    if (val.length < 2) {
        results.value = [];
        return;
    }
    loading.value = true;
    debounceTimer = setTimeout(async () => {
        try {
            const response = await fetch(route('global-search') + '?q=' + encodeURIComponent(val));
            const data = await response.json();
            results.value = data.results;
            selectedIndex.value = 0;
        } finally {
            loading.value = false;
        }
    }, 250);
});

const openSearch = () => {
    open.value = true;
    query.value = '';
    results.value = [];
    selectedIndex.value = 0;
    nextTick(() => inputRef.value?.focus());
};

const closeSearch = () => {
    open.value = false;
};

const navigate = (result: SearchResult) => {
    closeSearch();
    router.visit(result.url);
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, results.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
    } else if (e.key === 'Enter' && results.value[selectedIndex.value]) {
        e.preventDefault();
        navigate(results.value[selectedIndex.value]);
    }
};

const handleGlobalKeydown = (e: KeyboardEvent) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        if (open.value) {
            closeSearch();
        } else {
            openSearch();
        }
    }
    if (e.key === 'Escape' && open.value) {
        closeSearch();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleGlobalKeydown);
});

defineExpose({ openSearch });
</script>

<template>
    <!-- Trigger button -->
    <button
        @click="openSearch"
        class="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-400 shadow-sm hover:border-gray-400 hover:text-gray-500"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <span class="hidden sm:inline">{{ $t('search.button') }}</span>
        <kbd class="ml-2 hidden rounded border border-gray-200 bg-gray-50 px-1.5 text-xs font-medium text-gray-400 sm:inline">
            <span class="text-xs">⌘</span>K
        </kbd>
    </button>

    <!-- Search modal overlay -->
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed inset-0 z-[60] overflow-y-auto p-4 sm:p-6 md:p-20" @click.self="closeSearch">
                <div class="fixed inset-0 bg-gray-500/25" />
                <div class="relative mx-auto max-w-xl rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
                    <!-- Search input -->
                    <div class="flex items-center border-b border-gray-100 px-4">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input
                            ref="inputRef"
                            v-model="query"
                            type="text"
                            class="w-full border-0 bg-transparent px-3 py-4 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0"
                            :placeholder="$t('search.placeholder')"
                            @keydown="handleKeydown"
                        />
                        <span v-if="loading" class="h-5 w-5 animate-spin rounded-full border-2 border-gray-300 border-t-indigo-600"></span>
                    </div>

                    <!-- Results -->
                    <ul v-if="results.length > 0" class="max-h-80 scroll-py-2 overflow-y-auto py-2">
                        <li
                            v-for="(result, i) in results"
                            :key="i"
                            class="flex cursor-pointer items-center gap-3 px-4 py-2.5"
                            :class="selectedIndex === i ? 'bg-indigo-50' : 'hover:bg-gray-50'"
                            @click="navigate(result)"
                            @mouseenter="selectedIndex = i"
                        >
                            <svg class="h-5 w-5 shrink-0" :class="typeColors[result.type] || 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="typeIcons[result.type] || ''" />
                            </svg>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ result.label }}</p>
                                <p v-if="result.detail" class="truncate text-xs text-gray-500">{{ result.detail }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                                {{ typeLabels[result.type] || result.type }}
                            </span>
                        </li>
                    </ul>

                    <!-- Empty state -->
                    <div v-else-if="query.length >= 2 && !loading" class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-500">{{ $t('search.no_results', { query: query }) }}</p>
                    </div>

                    <!-- Footer hint -->
                    <div v-if="query.length < 2 && !loading" class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-400">{{ $t('search.hint') }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-4 py-2.5 text-xs text-gray-400">
                        <span class="flex items-center gap-1"><kbd class="rounded border border-gray-200 bg-gray-50 px-1">↑↓</kbd> {{ $t('search.navigate') }}</span>
                        <span class="flex items-center gap-1"><kbd class="rounded border border-gray-200 bg-gray-50 px-1">↵</kbd> {{ $t('search.open') }}</span>
                        <span class="flex items-center gap-1"><kbd class="rounded border border-gray-200 bg-gray-50 px-1">esc</kbd> {{ $t('search.close') }}</span>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
