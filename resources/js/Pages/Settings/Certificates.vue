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
                        <button @click="toggleActive(cert)" :class="cert.is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800'" :title="cert.is_active ? $t('common.deactivate') : $t('common.activate')">
                            <svg v-if="cert.is_active" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                        <button @click="confirmDelete(cert)" class="text-red-600 hover:text-red-900" :title="$t('common.delete')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
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
