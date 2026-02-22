<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';
import Badge from '@/Components/Badge.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

interface Cert {
    id: number;
    name: string;
    subject_cn: string | null;
    serial_number: string | null;
    valid_from: string | null;
    valid_until: string | null;
    is_active: boolean;
    is_expired: boolean;
    is_valid: boolean;
}

const props = defineProps<{
    certificates: Cert[];
}>();

const showUpload = ref(false);
const form = useForm({
    name: '',
    certificate: null as File | null,
    password: '',
});

const handleFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) form.certificate = input.files[0];
};

const submitUpload = () => {
    form.post(route('settings.certificates.upload'), {
        forceFormData: true,
        onSuccess: () => {
            showUpload.value = false;
            form.reset();
        },
    });
};

const toggleActive = (cert: Cert) => {
    router.post(route('settings.certificates.toggle', cert.id));
};

// Delete (modal confirmation)
const deleteDialog = ref(false);
const deleteTarget = ref<Cert | null>(null);
const deleting = ref(false);

const confirmDelete = (cert: Cert) => {
    deleteTarget.value = cert;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('settings.certificates.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <Head :title="$t('settings.certificates_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('settings.certificates_title') }}</h1>
        </template>

        <SettingsNav current="certificates" />

        <div class="mb-4 flex justify-end">
            <button @click="showUpload = !showUpload" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ showUpload ? $t('common.cancel') : $t('settings.upload_certificate') }}
            </button>
        </div>

        <!-- Upload form -->
        <div v-if="showUpload" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <form @submit.prevent="submitUpload" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.cert_name') }}</label>
                    <input type="text" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 text-sm" :placeholder="$t('settings.cert_name_placeholder')" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.cert_file') }}</label>
                    <input type="file" @change="handleFileChange" accept=".p12,.pfx" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-indigo-700" />
                    <p v-if="form.errors.certificate" class="mt-1 text-xs text-red-600">{{ form.errors.certificate }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">{{ $t('settings.cert_password') }}</label>
                    <div class="flex gap-2">
                        <input type="password" v-model="form.password" class="mt-1 block w-full rounded-md border-gray-300 text-sm" />
                        <button type="submit" :disabled="form.processing" class="mt-1 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">{{ $t('common.upload') }}</button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
            </form>
        </div>

        <!-- Certificates list -->
        <div class="space-y-3">
            <div v-for="cert in certificates" :key="cert.id" class="rounded-lg border bg-white p-4 shadow-sm" :class="cert.is_active ? 'border-green-300' : 'border-gray-200'">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-semibold text-gray-900">{{ cert.name }}</h4>
                            <Badge v-if="cert.is_valid" color="green">{{ $t('settings.cert_valid') }}</Badge>
                            <Badge v-else-if="cert.is_expired" color="red">{{ $t('settings.cert_expired') }}</Badge>
                            <Badge v-else-if="cert.is_active" color="yellow">{{ $t('settings.cert_active') }}</Badge>
                            <Badge v-else color="gray">{{ $t('settings.cert_inactive') }}</Badge>
                        </div>
                        <p v-if="cert.subject_cn" class="mt-1 text-xs text-gray-500">{{ $t('settings.cert_cn') }} {{ cert.subject_cn }}</p>
                        <p class="text-xs text-gray-400">
                            <span v-if="cert.valid_from">{{ $t('settings.cert_valid_from') }} {{ cert.valid_from }}</span>
                            <span v-if="cert.valid_until"> {{ $t('settings.cert_valid_until') }} {{ cert.valid_until }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="toggleActive(cert)" class="text-sm" :class="cert.is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800'">
                            {{ cert.is_active ? $t('common.deactivate') : $t('common.activate') }}
                        </button>
                        <button @click="confirmDelete(cert)" class="text-sm text-red-600 hover:text-red-900">{{ $t('common.delete') }}</button>
                    </div>
                </div>
            </div>

            <div v-if="certificates.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                <p class="text-sm text-gray-400">{{ $t('settings.no_certificates') }}</p>
            </div>
        </div>

        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('settings.delete_cert_title')"
            :message="trans('settings.delete_cert_message', { name: deleteTarget?.name || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
