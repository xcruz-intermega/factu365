<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

interface BackupTenant {
    id: string;
    slug: string | null;
    database: string;
}

interface Backup {
    filename: string;
    size: number;
    size_human: string;
    created_at: string;
    type: string;
    tenants: BackupTenant[];
    app_version: string;
}

const props = defineProps<{
    backups: Backup[];
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth.user as { role: string });
const isOwner = computed(() => currentUser.value?.role === 'owner');

const creatingTenant = ref(false);
const creatingFull = ref(false);

const createBackup = (type: 'tenant' | 'full') => {
    if (type === 'tenant') {
        creatingTenant.value = true;
    } else {
        creatingFull.value = true;
    }

    router.post(route('settings.backups.create'), { type }, {
        preserveScroll: true,
        onFinish: () => {
            creatingTenant.value = false;
            creatingFull.value = false;
        },
    });
};

// Restore dialog
const restoreDialog = ref(false);
const restoreTarget = ref<Backup | null>(null);
const restoring = ref(false);

const confirmRestore = (backup: Backup) => {
    restoreTarget.value = backup;
    restoreDialog.value = true;
};

const executeRestore = () => {
    if (!restoreTarget.value) return;
    restoring.value = true;

    router.post(route('settings.backups.restore'), {
        filename: restoreTarget.value.filename,
    }, {
        preserveScroll: true,
        onFinish: () => {
            restoring.value = false;
            restoreDialog.value = false;
            restoreTarget.value = null;
        },
    });
};

// Delete dialog
const deleteDialog = ref(false);
const deleteTarget = ref<Backup | null>(null);
const deleting = ref(false);

const confirmDelete = (backup: Backup) => {
    deleteTarget.value = backup;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;

    router.delete(route('settings.backups.destroy', deleteTarget.value.filename), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};

const formatDate = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleString();
    } catch {
        return dateStr;
    }
};

const typeColors: Record<string, string> = {
    full: 'bg-purple-100 text-purple-800',
    tenant: 'bg-blue-100 text-blue-800',
};

const typeLabels = computed<Record<string, string>>(() => ({
    full: trans('settings.backup_type_full'),
    tenant: trans('settings.backup_type_tenant'),
}));
</script>

<template>
    <Head :title="$t('settings.backups_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('settings.backups_title') }}</h1>
        </template>

        <SettingsNav current="backups" />

        <!-- Create backup section -->
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-2 text-sm font-semibold text-gray-900">{{ $t('settings.backup_create_section') }}</h3>
            <p class="mb-4 text-sm text-gray-600">{{ $t('settings.backup_create_description') }}</p>

            <div class="flex flex-wrap gap-3">
                <button
                    @click="createBackup('tenant')"
                    :disabled="creatingTenant || creatingFull"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                >
                    <svg v-if="creatingTenant" class="-ml-0.5 mr-1.5 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <svg v-else class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    {{ creatingTenant ? $t('settings.backup_creating') : $t('settings.backup_create_tenant') }}
                </button>

                <button
                    v-if="isOwner"
                    @click="createBackup('full')"
                    :disabled="creatingTenant || creatingFull"
                    class="inline-flex items-center rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 disabled:opacity-50"
                >
                    <svg v-if="creatingFull" class="-ml-0.5 mr-1.5 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <svg v-else class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Zm-3 6h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Z" />
                    </svg>
                    {{ creatingFull ? $t('settings.backup_creating') : $t('settings.backup_create_full') }}
                </button>
            </div>
        </div>

        <!-- Backups list -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('settings.backup_list_title') }}</h3>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('settings.backup_col_date') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('settings.backup_col_type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('settings.backup_col_size') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="backup in backups" :key="backup.filename">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                            {{ formatDate(backup.created_at) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span
                                :class="typeColors[backup.type] || 'bg-gray-100 text-gray-800'"
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                            >
                                {{ typeLabels[backup.type] || backup.type }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                            {{ backup.size_human }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Download -->
                                <a
                                    :href="route('settings.backups.download', backup.filename)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                    :title="$t('settings.backup_download')"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </a>

                                <!-- Restore (owner only) -->
                                <button
                                    v-if="isOwner"
                                    @click="confirmRestore(backup)"
                                    class="text-amber-600 hover:text-amber-900"
                                    :title="$t('settings.backup_restore')"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                                    </svg>
                                </button>

                                <!-- Delete -->
                                <button
                                    @click="confirmDelete(backup)"
                                    class="text-red-600 hover:text-red-900"
                                    :title="$t('common.delete')"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="backups.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ $t('settings.no_backups') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Restore confirm dialog -->
        <ConfirmDialog
            :show="restoreDialog"
            :title="$t('settings.backup_restore_title')"
            :message="trans('settings.backup_restore_message', { filename: restoreTarget?.filename || '' })"
            :confirm-label="$t('settings.backup_restore_confirm')"
            :processing="restoring"
            @confirm="executeRestore"
            @cancel="restoreDialog = false"
        />

        <!-- Delete confirm dialog -->
        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('settings.backup_delete_title')"
            :message="trans('settings.backup_delete_message', { filename: deleteTarget?.filename || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
