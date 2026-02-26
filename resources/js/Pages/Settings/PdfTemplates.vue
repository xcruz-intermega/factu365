<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';
import Badge from '@/Components/Badge.vue';
import Modal from '@/Components/Modal.vue';

interface Template {
    id: number;
    name: string;
    blade_view: string;
    settings: Record<string, any> | null;
    layout_json: Record<string, any> | null;
    is_default: boolean;
}

const props = defineProps<{
    templates: Template[];
}>();

const setDefault = (template: Template) => {
    router.post(route('settings.pdf-templates.default', template.id));
};

const deleteTarget = ref<Template | null>(null);
const showDeleteModal = ref(false);
const showImportModal = ref(false);
const importFile = ref<File | null>(null);
const importing = ref(false);

const confirmDelete = (tpl: Template) => {
    deleteTarget.value = tpl;
    showDeleteModal.value = true;
};

const doDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('settings.pdf-templates.destroy', deleteTarget.value.id), {
        onFinish: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
    });
};

const onImportFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    importFile.value = target.files?.[0] || null;
};

const doImport = () => {
    if (!importFile.value) return;
    importing.value = true;

    const formData = new FormData();
    formData.append('file', importFile.value);

    router.post(route('settings.pdf-templates.import'), formData, {
        forceFormData: true,
        onFinish: () => {
            importing.value = false;
            showImportModal.value = false;
            importFile.value = null;
        },
    });
};

const exportTemplate = (tpl: Template) => {
    window.open(route('settings.pdf-templates.export', tpl.id), '_blank');
};
</script>

<template>
    <Head :title="$t('settings.pdf_title')" />

    <AppLayout>
        <template #header>
            <div class="flex flex-1 items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900">{{ $t('settings.pdf_title') }}</h1>
                <div class="flex items-center gap-3">
                    <button
                        @click="showImportModal = true"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        {{ $t('settings.pdf_import') }}
                    </button>
                    <Link
                        :href="route('settings.pdf-templates.create')"
                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-500"
                    >
                        {{ $t('settings.pdf_create') }}
                    </Link>
                </div>
            </div>
        </template>

        <SettingsNav current="pdf-templates" />

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
                    <Badge v-if="tpl.is_default" color="indigo" class="absolute right-3 top-3">{{ $t('settings.pdf_default') }}</Badge>
                </div>

                <!-- Info -->
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ tpl.name }}</h3>
                    <div class="mt-2 flex items-center gap-2">
                        <div v-if="tpl.settings?.primary_color" class="h-4 w-4 rounded-full border border-gray-200" :style="{ backgroundColor: tpl.settings.primary_color }"></div>
                        <div v-if="tpl.settings?.accent_color" class="h-4 w-4 rounded-full border border-gray-200" :style="{ backgroundColor: tpl.settings.accent_color }"></div>
                        <span v-if="tpl.layout_json" class="text-xs text-indigo-500 font-medium">{{ $t('settings.pdf_custom') }}</span>
                        <span v-else class="text-xs text-gray-400">{{ $t('pdf.' + tpl.blade_view) }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="mt-3 flex items-center gap-3">
                        <button
                            v-if="!tpl.is_default"
                            @click="setDefault(tpl)"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            {{ $t('settings.pdf_set_default') }}
                        </button>
                        <span v-else class="text-xs text-green-600 font-medium">{{ $t('settings.pdf_active') }}</span>

                        <span class="text-gray-200">|</span>

                        <Link
                            v-if="tpl.layout_json"
                            :href="route('settings.pdf-templates.edit', tpl.id)"
                            class="text-sm text-gray-600 hover:text-indigo-600"
                        >
                            {{ $t('common.edit') }}
                        </Link>

                        <button
                            @click="exportTemplate(tpl)"
                            class="text-sm text-gray-600 hover:text-indigo-600"
                        >
                            {{ $t('settings.pdf_export') }}
                        </button>

                        <button
                            v-if="!tpl.is_default"
                            @click="confirmDelete(tpl)"
                            class="text-sm text-red-600 hover:text-red-500"
                        >
                            {{ $t('common.delete') }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="templates.length === 0" class="col-span-full rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                <p class="text-sm text-gray-400">{{ $t('settings.no_pdf_templates') }}</p>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="relative z-10 p-6">
                <h3 class="text-lg font-medium text-gray-900">{{ $t('settings.pdf_delete_title') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ $t('settings.pdf_delete_confirm', { name: deleteTarget?.name || '' }) }}</p>
                <div class="mt-4 flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ $t('common.cancel') }}</button>
                    <button @click="doDelete" class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-500">{{ $t('common.delete') }}</button>
                </div>
            </div>
        </Modal>

        <!-- Import modal -->
        <Modal :show="showImportModal" @close="showImportModal = false">
            <div class="relative z-10 p-6">
                <h3 class="text-lg font-medium text-gray-900">{{ $t('settings.pdf_import') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ $t('settings.pdf_import_description') }}</p>
                <div class="mt-4">
                    <input type="file" accept=".json" @change="onImportFileChange" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button @click="showImportModal = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ $t('common.cancel') }}</button>
                    <button @click="doImport" :disabled="!importFile || importing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-500 disabled:opacity-50">
                        {{ importing ? $t('settings.pdf_importing') : $t('settings.pdf_import') }}
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
