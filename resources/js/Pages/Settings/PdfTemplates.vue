<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/Badge.vue';

interface Template {
    id: number;
    name: string;
    blade_view: string;
    settings: Record<string, any> | null;
    is_default: boolean;
}

const props = defineProps<{
    templates: Template[];
}>();

const setDefault = (template: Template) => {
    router.post(route('settings.pdf-templates.default', template.id));
};
</script>

<template>
    <Head title="Plantillas PDF" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Plantillas PDF</h1>
        </template>

        <!-- Settings nav -->
        <div class="mb-6 flex gap-2 border-b border-gray-200 pb-3">
            <Link :href="route('settings.company')" class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Empresa</Link>
            <Link :href="route('settings.series')" class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Series</Link>
            <Link :href="route('settings.certificates')" class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Certificados</Link>
            <Link :href="route('settings.pdf-templates')" class="rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">Plantillas PDF</Link>
        </div>

        <!-- Templates grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="tpl in templates" :key="tpl.id" class="overflow-hidden rounded-lg border bg-white shadow-sm" :class="tpl.is_default ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-200'">
                <!-- Preview area -->
                <div class="relative bg-gray-50 p-6">
                    <div class="mx-auto h-40 w-28 rounded border border-gray-200 bg-white p-2 shadow-sm">
                        <!-- Mini PDF preview mock -->
                        <div class="mb-1 h-2 w-full rounded" :style="{ backgroundColor: tpl.settings?.primary_color || '#4f46e5' }"></div>
                        <div class="mb-1 h-1 w-3/4 rounded bg-gray-200"></div>
                        <div class="mb-1 h-1 w-1/2 rounded bg-gray-200"></div>
                        <div class="mb-2 h-px w-full bg-gray-100"></div>
                        <div class="mb-0.5 h-1 w-full rounded bg-gray-100"></div>
                        <div class="mb-0.5 h-1 w-full rounded bg-gray-100"></div>
                        <div class="mb-0.5 h-1 w-full rounded bg-gray-100"></div>
                        <div class="mb-2 h-px w-full bg-gray-100"></div>
                        <div class="flex justify-end">
                            <div class="h-2 w-8 rounded" :style="{ backgroundColor: tpl.settings?.accent_color || tpl.settings?.primary_color || '#4f46e5' }"></div>
                        </div>
                    </div>
                    <Badge v-if="tpl.is_default" color="indigo" class="absolute right-3 top-3">Predeterminada</Badge>
                </div>

                <!-- Info -->
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ tpl.name }}</h3>
                    <div class="mt-2 flex items-center gap-2">
                        <div v-if="tpl.settings?.primary_color" class="h-4 w-4 rounded-full border border-gray-200" :style="{ backgroundColor: tpl.settings.primary_color }"></div>
                        <div v-if="tpl.settings?.accent_color" class="h-4 w-4 rounded-full border border-gray-200" :style="{ backgroundColor: tpl.settings.accent_color }"></div>
                        <span class="text-xs text-gray-400">{{ tpl.blade_view }}</span>
                    </div>
                    <div class="mt-3">
                        <button
                            v-if="!tpl.is_default"
                            @click="setDefault(tpl)"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Establecer como predeterminada
                        </button>
                        <span v-else class="text-xs text-green-600 font-medium">Plantilla activa</span>
                    </div>
                </div>
            </div>

            <div v-if="templates.length === 0" class="col-span-full rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                <p class="text-sm text-gray-400">No hay plantillas configuradas.</p>
            </div>
        </div>
    </AppLayout>
</template>
