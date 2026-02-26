<script setup lang="ts">
import FlashMessage from '@/Components/FlashMessage.vue';
import { router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

const page = usePage();
const adminEmail = computed(() => page.props.admin_email as string | null);
const appVersion = computed(() => page.props.app_version as string);

const logout = () => {
    router.post('/admin/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Top bar -->
        <nav class="bg-gray-900 shadow">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <a href="/admin/dashboard" class="font-brand text-xl font-extrabold text-white">
                            Factu365
                        </a>
                        <span class="rounded bg-red-600 px-2 py-0.5 text-xs font-bold uppercase text-white">
                            Admin
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <span v-if="adminEmail" class="text-sm text-gray-300">
                            {{ adminEmail }}
                        </span>
                        <button
                            @click="logout"
                            class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white"
                        >
                            {{ $t('common.logout') }}
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <main class="px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>

        <!-- Version footer -->
        <p class="pb-4 text-center text-xs text-gray-400">v{{ appVersion }}</p>

        <FlashMessage />
    </div>
</template>
