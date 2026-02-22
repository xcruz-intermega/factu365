<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const open = ref(false);

const locales = [
    { code: 'es', label: 'ES', name: 'Español' },
    { code: 'en', label: 'EN', name: 'English' },
    { code: 'ca', label: 'CA', name: 'Català' },
];

const currentLocale = () => page.props.locale as string;

function switchLocale(code: string) {
    open.value = false;
    if (code === currentLocale()) return;
    router.patch(route('locale.update'), { locale: code }, { preserveState: false });
}

function closeDropdown() {
    setTimeout(() => open.value = false, 150);
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="flex items-center gap-1 rounded-md px-2 py-1 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
            @click="open = !open"
            @blur="closeDropdown"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 003 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            <span>{{ currentLocale().toUpperCase() }}</span>
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
        <div
            v-if="open"
            class="absolute right-0 z-50 mt-1 w-36 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5"
        >
            <button
                v-for="loc in locales"
                :key="loc.code"
                type="button"
                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-50"
                :class="currentLocale() === loc.code ? 'font-semibold text-indigo-600' : 'text-gray-700'"
                @mousedown.prevent="switchLocale(loc.code)"
            >
                <span class="w-6 text-center text-xs font-bold">{{ loc.label }}</span>
                <span>{{ loc.name }}</span>
            </button>
        </div>
    </div>
</template>
