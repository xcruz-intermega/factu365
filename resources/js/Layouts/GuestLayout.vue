<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const appVersion = usePage().props.app_version as string;
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/" class="flex flex-col items-center gap-2">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
                <span class="font-brand text-[30px] font-extrabold text-indigo-600">Factu365</span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">v{{ appVersion }}</p>
    </div>
</template>
