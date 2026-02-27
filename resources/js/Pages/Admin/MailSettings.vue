<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';

interface MailConfig {
    host: string | null;
    port: number;
    username: string | null;
    encryption: string | null;
    from_address: string | null;
    from_name: string | null;
    is_active: boolean;
    tested_at: string | null;
    has_password: boolean;
}

const props = defineProps<{
    mailConfig: MailConfig | null;
}>();

const form = useForm({
    host: props.mailConfig?.host ?? '',
    port: props.mailConfig?.port ?? 587,
    username: props.mailConfig?.username ?? '',
    password: '',
    encryption: props.mailConfig?.encryption ?? 'tls',
    from_address: props.mailConfig?.from_address ?? '',
    from_name: props.mailConfig?.from_name ?? '',
    is_active: props.mailConfig?.is_active ?? false,
});

const testing = ref(false);
const showGuide = ref(false);

const encryptionOptions = computed(() => [
    { value: 'tls', label: trans('admin.mail_settings_encryption_tls') },
    { value: 'ssl', label: trans('admin.mail_settings_encryption_ssl') },
    { value: '', label: trans('admin.mail_settings_encryption_none') },
]);

const testedAtFormatted = computed(() => {
    if (!props.mailConfig?.tested_at) return null;
    return new Date(props.mailConfig.tested_at).toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const submitForm = () => {
    form.put('/admin/settings/mail', {
        preserveScroll: true,
    });
};

const testConnection = () => {
    testing.value = true;
    router.post('/admin/settings/mail/test', {
        host: form.host,
        port: form.port,
        username: form.username,
        password: form.password,
        encryption: form.encryption,
        from_address: form.from_address,
        from_name: form.from_name,
    }, {
        preserveScroll: true,
        onFinish: () => {
            testing.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="$t('admin.mail_settings_title')" />

        <div class="mx-auto max-w-3xl">
            <!-- Header -->
            <div class="mb-6 flex items-center gap-3">
                <a href="/admin/dashboard" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.mail_settings_title') }}</h1>
            </div>

            <!-- SMTP Form -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ $t('admin.mail_settings_smtp_section') }}</h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Host -->
                        <div>
                            <InputLabel for="host" :value="$t('admin.mail_settings_host')" />
                            <TextInput
                                id="host"
                                v-model="form.host"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="smtp-relay.brevo.com"
                            />
                            <InputError class="mt-1" :message="form.errors.host" />
                        </div>

                        <!-- Port -->
                        <div>
                            <InputLabel for="port" :value="$t('admin.mail_settings_port')" />
                            <TextInput
                                id="port"
                                v-model.number="form.port"
                                type="number"
                                class="mt-1 block w-full"
                                min="1"
                                max="65535"
                            />
                            <InputError class="mt-1" :message="form.errors.port" />
                        </div>

                        <!-- Encryption -->
                        <div>
                            <InputLabel for="encryption" :value="$t('admin.mail_settings_encryption')" />
                            <select
                                id="encryption"
                                v-model="form.encryption"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option v-for="opt in encryptionOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.encryption" />
                        </div>

                        <!-- Username -->
                        <div>
                            <InputLabel for="username" :value="$t('admin.mail_settings_username')" />
                            <TextInput
                                id="username"
                                v-model="form.username"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.username" />
                        </div>

                        <!-- Password -->
                        <div>
                            <InputLabel for="password" :value="$t('admin.mail_settings_password')" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                :placeholder="mailConfig?.has_password ? '••••••••' : ''"
                            />
                            <p v-if="mailConfig?.has_password" class="mt-1 text-xs text-gray-500">
                                {{ $t('admin.mail_settings_password_placeholder') }}
                            </p>
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>

                        <!-- From Address -->
                        <div>
                            <InputLabel for="from_address" :value="$t('admin.mail_settings_from_address')" />
                            <TextInput
                                id="from_address"
                                v-model="form.from_address"
                                type="email"
                                class="mt-1 block w-full"
                                placeholder="noreply@factu365.intermega.es"
                            />
                            <InputError class="mt-1" :message="form.errors.from_address" />
                        </div>

                        <!-- From Name -->
                        <div>
                            <InputLabel for="from_name" :value="$t('admin.mail_settings_from_name')" />
                            <TextInput
                                id="from_name"
                                v-model="form.from_name"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Factu365"
                            />
                            <InputError class="mt-1" :message="form.errors.from_name" />
                        </div>
                    </div>

                    <!-- Active toggle -->
                    <div class="flex items-center gap-3 pt-2">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="peer sr-only"
                            />
                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-indigo-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-900">{{ $t('admin.mail_settings_is_active') }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center gap-3 border-t pt-4">
                        <PrimaryButton
                            type="submit"
                            :disabled="form.processing"
                            :class="{ 'opacity-25': form.processing }"
                        >
                            {{ $t('common.save') }}
                        </PrimaryButton>

                        <button
                            type="button"
                            @click="testConnection"
                            :disabled="testing || !form.host || !form.from_address"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-25"
                        >
                            <span v-if="testing" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ $t('admin.mail_settings_test_button') }}
                            </span>
                            <span v-else>{{ $t('admin.mail_settings_test_button') }}</span>
                        </button>

                        <span v-if="testedAtFormatted" class="text-sm text-green-600">
                            {{ $t('admin.mail_settings_last_test') }}: {{ testedAtFormatted }}
                        </span>
                        <span v-else-if="mailConfig" class="text-sm text-gray-400">
                            {{ $t('admin.mail_settings_not_tested') }}
                        </span>
                    </div>
                </form>
            </div>

            <!-- Configuration Guide -->
            <div class="mt-6 rounded-lg bg-white shadow">
                <button
                    @click="showGuide = !showGuide"
                    class="flex w-full items-center justify-between p-6 text-left"
                >
                    <h2 class="text-lg font-semibold text-gray-900">{{ $t('admin.mail_settings_guide_title') }}</h2>
                    <svg
                        class="h-5 w-5 text-gray-400 transition-transform"
                        :class="{ 'rotate-180': showGuide }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div v-show="showGuide" class="border-t px-6 pb-6 pt-4">
                    <!-- Provider -->
                    <div class="mb-6">
                        <h3 class="mb-2 text-sm font-semibold text-gray-900">{{ $t('admin.mail_settings_guide_provider') }}</h3>
                        <div class="rounded-md bg-blue-50 p-4 text-sm text-blue-800">
                            <p class="font-medium">Brevo (ex Sendinblue)</p>
                            <p class="mt-1">{{ $t('admin.mail_settings_guide_provider_desc') }}</p>
                            <ol class="mt-2 list-inside list-decimal space-y-1 text-blue-700">
                                <li>{{ $t('admin.mail_settings_guide_step1') }}</li>
                                <li>{{ $t('admin.mail_settings_guide_step2') }}</li>
                                <li>{{ $t('admin.mail_settings_guide_step3') }}</li>
                            </ol>
                        </div>
                    </div>

                    <!-- DNS Records -->
                    <div class="mb-6">
                        <h3 class="mb-2 text-sm font-semibold text-gray-900">{{ $t('admin.mail_settings_guide_dns') }}</h3>
                        <div class="space-y-3">
                            <div class="rounded-md bg-gray-50 p-3">
                                <p class="text-sm font-medium text-gray-900">SPF</p>
                                <p class="mt-1 text-xs text-gray-600">{{ $t('admin.mail_settings_guide_spf') }}</p>
                                <code class="mt-1 block rounded bg-gray-200 px-2 py-1 text-xs text-gray-800">v=spf1 include:sendinblue.com ~all</code>
                            </div>
                            <div class="rounded-md bg-gray-50 p-3">
                                <p class="text-sm font-medium text-gray-900">DKIM</p>
                                <p class="mt-1 text-xs text-gray-600">{{ $t('admin.mail_settings_guide_dkim') }}</p>
                            </div>
                            <div class="rounded-md bg-gray-50 p-3">
                                <p class="text-sm font-medium text-gray-900">DMARC</p>
                                <p class="mt-1 text-xs text-gray-600">{{ $t('admin.mail_settings_guide_dmarc') }}</p>
                                <code class="mt-1 block rounded bg-gray-200 px-2 py-1 text-xs text-gray-800">v=DMARC1; p=none; rua=mailto:admin@tudominio.com; fo=1</code>
                            </div>
                        </div>
                    </div>

                    <!-- Verify -->
                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-gray-900">{{ $t('admin.mail_settings_guide_verify') }}</h3>
                        <p class="text-sm text-gray-600">{{ $t('admin.mail_settings_guide_verify_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
