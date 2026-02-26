<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';

interface TenantSummary {
    id: string;
    slug: string;
    name: string;
    email: string;
    plan_id: string | null;
    trial_ends_at: string | null;
    created_at: string | null;
    suspended_at: string | null;
    company: {
        legal_name: string;
        trade_name: string | null;
        nif: string;
        city: string | null;
        verifactu_enabled: boolean;
        verifactu_environment: string | null;
    } | null;
    owner: { name: string; email: string } | null;
    users_count: number;
    invoices_count: number;
    disk_usage: number;
    disk_usage_human: string;
    last_backup_date: string | null;
    backups_count: number;
    backups_total_size: number;
    backups_total_size_human: string;
}

const props = defineProps<{
    tenants: TenantSummary[];
}>();

const search = ref('');

const filteredTenants = computed(() => {
    if (!search.value) return props.tenants;
    const q = search.value.toLowerCase();
    return props.tenants.filter(t =>
        t.slug.toLowerCase().includes(q) ||
        (t.name || '').toLowerCase().includes(q) ||
        (t.company?.legal_name || '').toLowerCase().includes(q) ||
        (t.company?.nif || '').toLowerCase().includes(q) ||
        (t.owner?.email || '').toLowerCase().includes(q)
    );
});

const totalTenants = computed(() => props.tenants.length);
const activeTenants = computed(() => props.tenants.filter(t => !t.suspended_at).length);
const suspendedTenants = computed(() => props.tenants.filter(t => t.suspended_at).length);

// Password reset modal
const showPasswordModal = ref(false);
const passwordTarget = ref<TenantSummary | null>(null);
const passwordForm = useForm({ password: '' });

const openPasswordModal = (tenant: TenantSummary) => {
    passwordTarget.value = tenant;
    passwordForm.reset();
    showPasswordModal.value = true;
};

const submitPasswordReset = () => {
    if (!passwordTarget.value) return;
    passwordForm.post(`/admin/tenants/${passwordTarget.value.id}/reset-password`, {
        onSuccess: () => {
            showPasswordModal.value = false;
            passwordForm.reset();
        },
    });
};

// Delete confirmation
const showDeleteModal = ref(false);
const deleteTarget = ref<TenantSummary | null>(null);
const deleteConfirmSlug = ref('');

const openDeleteModal = (tenant: TenantSummary) => {
    deleteTarget.value = tenant;
    deleteConfirmSlug.value = '';
    showDeleteModal.value = true;
};

const submitDelete = () => {
    if (!deleteTarget.value || deleteConfirmSlug.value !== deleteTarget.value.slug) return;
    router.delete(`/admin/tenants/${deleteTarget.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

// Actions
const toggleSuspend = (tenant: TenantSummary) => {
    const action = tenant.suspended_at ? 'unsuspend' : 'suspend';
    router.post(`/admin/tenants/${tenant.id}/${action}`);
};

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AdminLayout>
        <Head :title="$t('admin.dashboard_title')" />

        <!-- KPI Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm font-medium text-gray-500">{{ $t('admin.total_tenants') }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ totalTenants }}</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm font-medium text-gray-500">{{ $t('admin.active_tenants') }}</p>
                <p class="mt-1 text-3xl font-bold text-green-600">{{ activeTenants }}</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-sm font-medium text-gray-500">{{ $t('admin.suspended_tenants') }}</p>
                <p class="mt-1 text-3xl font-bold text-red-600">{{ suspendedTenants }}</p>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <TextInput
                v-model="search"
                type="text"
                class="w-full sm:max-w-md"
                :placeholder="$t('admin.search_placeholder')"
            />
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_slug') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_nif') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_owner') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_users') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_invoices') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_disk') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_backups') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_created') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('admin.col_status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="tenant in filteredTenants" :key="tenant.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3">
                                <a
                                    :href="`/admin/tenants/${tenant.id}`"
                                    class="font-medium text-indigo-600 hover:text-indigo-800"
                                >
                                    {{ tenant.slug }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                {{ tenant.company?.legal_name || '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ tenant.company?.nif || '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div v-if="tenant.owner">
                                    <div class="text-gray-900">{{ tenant.owner.name }}</div>
                                    <div class="text-xs text-gray-500">{{ tenant.owner.email }}</div>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-500">
                                {{ tenant.users_count }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-500">
                                {{ tenant.invoices_count }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">
                                {{ tenant.disk_usage_human }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm">
                                <span v-if="tenant.backups_count > 0" class="text-gray-700">
                                    {{ tenant.backups_count }}
                                    <span class="text-xs text-gray-400">({{ tenant.backups_total_size_human }})</span>
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ formatDate(tenant.created_at) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="tenant.suspended_at
                                        ? 'bg-red-100 text-red-800'
                                        : 'bg-green-100 text-green-800'"
                                >
                                    {{ tenant.suspended_at ? $t('admin.status_suspended') : $t('admin.status_active') }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        :href="`/admin/tenants/${tenant.id}`"
                                        class="text-indigo-600 hover:text-indigo-800"
                                        :title="$t('admin.view_detail')"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    <button
                                        @click="toggleSuspend(tenant)"
                                        class="hover:text-amber-800"
                                        :class="tenant.suspended_at ? 'text-green-600' : 'text-amber-600'"
                                        :title="tenant.suspended_at ? $t('admin.unsuspend') : $t('admin.suspend')"
                                    >
                                        <svg v-if="!tenant.suspended_at" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="openPasswordModal(tenant)"
                                        class="text-gray-600 hover:text-gray-800"
                                        :title="$t('admin.reset_password')"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="openDeleteModal(tenant)"
                                        class="text-red-600 hover:text-red-800"
                                        :title="$t('common.delete')"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredTenants.length === 0">
                            <td colspan="11" class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ $t('common.no_records') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Password Reset Modal -->
        <Modal :show="showPasswordModal" max-width="md" @close="showPasswordModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">{{ $t('admin.reset_password') }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $t('admin.reset_password_desc', { slug: passwordTarget?.slug || '' }) }}
                </p>

                <div class="mt-4">
                    <InputLabel for="new-password" :value="$t('admin.new_password')" />
                    <TextInput
                        id="new-password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="passwordForm.password"
                        @keyup.enter="submitPasswordReset"
                    />
                    <InputError class="mt-2" :message="passwordForm.errors.password" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showPasswordModal = false"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        {{ $t('common.cancel') }}
                    </button>
                    <PrimaryButton
                        @click="submitPasswordReset"
                        :disabled="passwordForm.processing || !passwordForm.password"
                        :class="{ 'opacity-25': passwordForm.processing }"
                    >
                        {{ $t('admin.reset_password') }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" max-width="md" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-red-600">{{ $t('admin.delete_tenant') }}</h3>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $t('admin.delete_tenant_warning') }}
                </p>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $t('admin.delete_confirm_type', { slug: deleteTarget?.slug || '' }) }}
                </p>

                <div class="mt-4">
                    <TextInput
                        type="text"
                        class="mt-1 block w-full"
                        v-model="deleteConfirmSlug"
                        :placeholder="deleteTarget?.slug || ''"
                    />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        {{ $t('common.cancel') }}
                    </button>
                    <button
                        @click="submitDelete"
                        :disabled="deleteConfirmSlug !== deleteTarget?.slug"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-25"
                    >
                        {{ $t('admin.confirm_delete') }}
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
