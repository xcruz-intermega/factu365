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

interface TenantDetail {
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
    company_full: {
        legal_name: string;
        trade_name: string | null;
        nif: string;
        address_street: string | null;
        address_city: string | null;
        address_postal_code: string | null;
        address_province: string | null;
        address_country: string | null;
        phone: string | null;
        email: string | null;
        website: string | null;
        tax_regime: string | null;
        irpf_rate: string | null;
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
    backups: Array<{
        filename: string;
        date: string | null;
        size: number;
        size_human: string;
        type: string;
    }>;
    clients_count: number;
    products_count: number;
    expenses_count: number;
    documents_breakdown: {
        invoices: number;
        quotes: number;
        delivery_notes: number;
        rectificatives: number;
        purchase_invoices: number;
    };
    revenue_this_year: number;
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        locale: string;
        created_at: string;
    }>;
    last_document_date: string | null;
}

const props = defineProps<{
    tenant: TenantDetail;
}>();

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(amount);
};

const roleLabels = computed(() => ({
    owner: trans('admin.role_owner'),
    admin: trans('admin.role_admin'),
    accountant: trans('admin.role_accountant'),
    viewer: trans('admin.role_viewer'),
}));

// Password reset modal
const showPasswordModal = ref(false);
const passwordForm = useForm({ password: '' });

const submitPasswordReset = () => {
    passwordForm.post(`/admin/tenants/${props.tenant.id}/reset-password`, {
        onSuccess: () => {
            showPasswordModal.value = false;
            passwordForm.reset();
        },
    });
};

// Delete confirmation
const showDeleteModal = ref(false);
const deleteConfirmSlug = ref('');

const submitDelete = () => {
    if (deleteConfirmSlug.value !== props.tenant.slug) return;
    router.delete(`/admin/tenants/${props.tenant.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

// Suspend/unsuspend
const toggleSuspend = () => {
    const action = props.tenant.suspended_at ? 'unsuspend' : 'suspend';
    router.post(`/admin/tenants/${props.tenant.id}/${action}`);
};
</script>

<template>
    <AdminLayout>
        <Head :title="`${tenant.slug} - ${$t('admin.tenant_detail')}`" />

        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="/admin/dashboard"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ tenant.slug }}</h1>
                <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                    :class="tenant.suspended_at
                        ? 'bg-red-100 text-red-800'
                        : 'bg-green-100 text-green-800'"
                >
                    {{ tenant.suspended_at ? $t('admin.status_suspended') : $t('admin.status_active') }}
                </span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    @click="toggleSuspend"
                    class="rounded-md px-3 py-2 text-sm font-medium"
                    :class="tenant.suspended_at
                        ? 'bg-green-600 text-white hover:bg-green-700'
                        : 'bg-amber-600 text-white hover:bg-amber-700'"
                >
                    {{ tenant.suspended_at ? $t('admin.unsuspend') : $t('admin.suspend') }}
                </button>
                <button
                    @click="showPasswordModal = true"
                    class="rounded-md bg-gray-600 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700"
                >
                    {{ $t('admin.reset_password') }}
                </button>
                <a
                    :href="`/${tenant.slug}/dashboard`"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    {{ $t('admin.access_tenant') }}
                </a>
                <button
                    @click="showDeleteModal = true"
                    class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    {{ $t('admin.delete_tenant') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Section 1: Tenant Info -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ $t('admin.section_tenant_info') }}</h2>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <dt class="text-gray-500">UUID</dt>
                    <dd class="font-mono text-xs text-gray-700">{{ tenant.id }}</dd>

                    <dt class="text-gray-500">Slug</dt>
                    <dd class="text-gray-900">{{ tenant.slug }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.col_name') }}</dt>
                    <dd class="text-gray-900">{{ tenant.name || '-' }}</dd>

                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-gray-900">{{ tenant.email || '-' }}</dd>

                    <dt class="text-gray-500">Plan</dt>
                    <dd class="text-gray-900">{{ tenant.plan_id || '-' }}</dd>

                    <dt class="text-gray-500">Trial</dt>
                    <dd class="text-gray-900">{{ formatDate(tenant.trial_ends_at) }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.col_created') }}</dt>
                    <dd class="text-gray-900">{{ formatDate(tenant.created_at) }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.col_status') }}</dt>
                    <dd>
                        <span
                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="tenant.suspended_at
                                ? 'bg-red-100 text-red-800'
                                : 'bg-green-100 text-green-800'"
                        >
                            {{ tenant.suspended_at ? $t('admin.status_suspended') : $t('admin.status_active') }}
                        </span>
                    </dd>
                </dl>
            </div>

            <!-- Section 2: Company Profile -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ $t('admin.section_company') }}</h2>
                <template v-if="tenant.company_full">
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <dt class="text-gray-500">{{ $t('admin.legal_name') }}</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.legal_name || '-' }}</dd>

                        <dt class="text-gray-500">{{ $t('admin.trade_name') }}</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.trade_name || '-' }}</dd>

                        <dt class="text-gray-500">NIF/CIF</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.nif || '-' }}</dd>

                        <dt class="text-gray-500">{{ $t('admin.address') }}</dt>
                        <dd class="text-gray-900">
                            {{ tenant.company_full.address_street || '' }}
                            <template v-if="tenant.company_full.address_postal_code || tenant.company_full.address_city">
                                <br>{{ tenant.company_full.address_postal_code }} {{ tenant.company_full.address_city }}
                            </template>
                            <template v-if="tenant.company_full.address_province">
                                <br>{{ tenant.company_full.address_province }}
                            </template>
                        </dd>

                        <dt class="text-gray-500">{{ $t('admin.phone') }}</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.phone || '-' }}</dd>

                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.email || '-' }}</dd>

                        <dt class="text-gray-500">Web</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.website || '-' }}</dd>

                        <dt class="text-gray-500">{{ $t('admin.tax_regime') }}</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.tax_regime || '-' }}</dd>

                        <dt class="text-gray-500">IRPF</dt>
                        <dd class="text-gray-900">{{ tenant.company_full.irpf_rate }}%</dd>

                        <dt class="text-gray-500">VeriFactu</dt>
                        <dd>
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="tenant.company_full.verifactu_enabled
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-600'"
                            >
                                {{ tenant.company_full.verifactu_enabled ? $t('admin.enabled') : $t('admin.disabled') }}
                            </span>
                            <span v-if="tenant.company_full.verifactu_enabled" class="ml-1 text-xs text-gray-500">
                                ({{ tenant.company_full.verifactu_environment }})
                            </span>
                        </dd>
                    </dl>
                </template>
                <p v-else class="text-sm text-gray-400">{{ $t('admin.no_company_profile') }}</p>
            </div>

            <!-- Section 3: Users -->
            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ $t('admin.section_users') }} ({{ tenant.users.length }})
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('admin.col_name') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('admin.col_role') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('admin.col_locale') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('admin.col_created') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in tenant.users" :key="user.id">
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900">{{ user.name }}</td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-500">{{ user.email }}</td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="{
                                            'bg-purple-100 text-purple-800': user.role === 'owner',
                                            'bg-blue-100 text-blue-800': user.role === 'admin',
                                            'bg-green-100 text-green-800': user.role === 'accountant',
                                            'bg-gray-100 text-gray-800': user.role === 'viewer',
                                        }"
                                    >
                                        {{ roleLabels[user.role as keyof typeof roleLabels] || user.role }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-500 uppercase">{{ user.locale }}</td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 4: Statistics -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ $t('admin.section_stats') }}</h2>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <dt class="text-gray-500">{{ $t('admin.stat_invoices') }}</dt>
                    <dd class="font-semibold text-gray-900">{{ tenant.documents_breakdown.invoices }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.stat_quotes') }}</dt>
                    <dd class="text-gray-900">{{ tenant.documents_breakdown.quotes }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.stat_delivery_notes') }}</dt>
                    <dd class="text-gray-900">{{ tenant.documents_breakdown.delivery_notes }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.stat_rectificatives') }}</dt>
                    <dd class="text-gray-900">{{ tenant.documents_breakdown.rectificatives }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.stat_purchase_invoices') }}</dt>
                    <dd class="text-gray-900">{{ tenant.documents_breakdown.purchase_invoices }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.stat_clients') }}</dt>
                    <dd class="text-gray-900">{{ tenant.clients_count }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.stat_products') }}</dt>
                    <dd class="text-gray-900">{{ tenant.products_count }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.stat_expenses') }}</dt>
                    <dd class="text-gray-900">{{ tenant.expenses_count }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.revenue_this_year') }}</dt>
                    <dd class="font-semibold text-green-600">{{ formatCurrency(tenant.revenue_this_year) }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.last_document') }}</dt>
                    <dd class="text-gray-900">{{ formatDate(tenant.last_document_date) }}</dd>
                </dl>
            </div>

            <!-- Section 5: Storage & Backups -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ $t('admin.section_storage') }}</h2>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <dt class="text-gray-500">{{ $t('admin.disk_usage') }}</dt>
                    <dd class="font-semibold text-gray-900">{{ tenant.disk_usage_human }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.backups_count') }}</dt>
                    <dd class="text-gray-900">{{ tenant.backups_count }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.backups_total_size') }}</dt>
                    <dd class="text-gray-900">{{ tenant.backups_total_size_human }}</dd>

                    <dt class="text-gray-500">{{ $t('admin.last_backup') }}</dt>
                    <dd class="text-gray-900">{{ tenant.last_backup_date ? formatDate(tenant.last_backup_date) : $t('admin.no_backup') }}</dd>
                </dl>

                <!-- Backups table -->
                <div v-if="tenant.backups.length > 0" class="mt-4">
                    <h3 class="mb-2 text-sm font-medium text-gray-700">{{ $t('admin.backups_list') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('admin.backup_file') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('admin.backup_date') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">{{ $t('admin.backup_type') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">{{ $t('admin.backup_size') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="backup in tenant.backups" :key="backup.filename">
                                    <td class="whitespace-nowrap px-3 py-2 text-xs font-mono text-gray-700">{{ backup.filename }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ formatDate(backup.date) }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                            :class="backup.type === 'full' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                                        >
                                            {{ backup.type === 'full' ? $t('admin.backup_type_full') : $t('admin.backup_type_tenant') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-right text-sm text-gray-500">{{ backup.size_human }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <p v-else class="mt-3 text-sm text-gray-400">{{ $t('admin.no_backup') }}</p>
            </div>
        </div>

        <!-- Password Reset Modal -->
        <Modal :show="showPasswordModal" max-width="md" @close="showPasswordModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">{{ $t('admin.reset_password') }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $t('admin.reset_password_desc', { slug: tenant.slug }) }}
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
                    {{ $t('admin.delete_confirm_type', { slug: tenant.slug }) }}
                </p>

                <div class="mt-4">
                    <TextInput
                        type="text"
                        class="mt-1 block w-full"
                        v-model="deleteConfirmSlug"
                        :placeholder="tenant.slug"
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
                        :disabled="deleteConfirmSlug !== tenant.slug"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-25"
                    >
                        {{ $t('admin.confirm_delete') }}
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
