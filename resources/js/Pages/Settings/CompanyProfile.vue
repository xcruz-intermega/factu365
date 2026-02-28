<script setup lang="ts">
import { Head, useForm, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

import ConfirmDialog from '@/Components/ConfirmDialog.vue';

interface Company {
    id?: number;
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
    logo_path: string | null;
    verifactu_enabled: boolean;
    verifactu_environment: string;
    catalog_enabled: boolean;
}

const props = defineProps<{
    company: Company | null;
    logoUrl: string | null;
}>();

const c = props.company;

const tenantSlug = computed(() => (usePage().props as any).tenant?.slug || '');
const catalogPublicUrl = computed(() => {
    return `${window.location.origin}/${tenantSlug.value}/catalogo`;
});

const form = useForm({
    legal_name: c?.legal_name || '',
    trade_name: c?.trade_name || '',
    nif: c?.nif || '',
    address_street: c?.address_street || '',
    address_city: c?.address_city || '',
    address_postal_code: c?.address_postal_code || '',
    address_province: c?.address_province || '',
    address_country: c?.address_country || 'ES',
    phone: c?.phone || '',
    email: c?.email || '',
    website: c?.website || '',
    tax_regime: c?.tax_regime || 'general',
    irpf_rate: c?.irpf_rate ? Number(c.irpf_rate) : 15,
    logo: null as File | null,
    catalog_enabled: c?.catalog_enabled ?? false,
});

const logoPreview = ref<string | null>(null);

const handleLogoChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        form.logo = input.files[0];
        logoPreview.value = URL.createObjectURL(input.files[0]);
    }
};

const deleteLogo = () => {
    router.delete(route('settings.company.logo.delete'));
};

const submit = () => {
    form.post(route('settings.company.update'), {
        forceFormData: true,
    });
};

// VeriFactu settings (separate form)
const verifactuForm = useForm({
    verifactu_enabled: c?.verifactu_enabled ?? false,
    verifactu_environment: c?.verifactu_environment ?? 'sandbox',
});

const isProductionLocked = computed(() => c?.verifactu_environment === 'production');
const showProductionConfirm = ref(false);
const pendingEnvironment = ref('sandbox');

const onEnvironmentChange = (value: string) => {
    if (value === 'production') {
        pendingEnvironment.value = 'production';
        showProductionConfirm.value = true;
        // Revert select until confirmed
        verifactuForm.verifactu_environment = 'sandbox';
    }
};

const confirmProduction = () => {
    verifactuForm.verifactu_environment = 'production';
    showProductionConfirm.value = false;
};

const cancelProduction = () => {
    showProductionConfirm.value = false;
};

const submitVerifactu = () => {
    verifactuForm.patch(route('settings.company.verifactu.update'));
};

const showDemoConfirm = ref(false);
const demoProcessing = ref(false);

const seedDemoData = () => {
    demoProcessing.value = true;
    router.post(route('settings.demo-data'), {}, {
        onFinish: () => {
            demoProcessing.value = false;
            showDemoConfirm.value = false;
        },
    });
};
</script>

<template>
    <Head :title="$t('settings.company_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('settings.company_title') }}</h1>
        </template>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Identity -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('settings.section_identity') }}</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.legal_name') }}</label>
                        <input type="text" v-model="form.legal_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': form.errors.legal_name }" />
                        <p v-if="form.errors.legal_name" class="mt-1 text-sm text-red-600">{{ form.errors.legal_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.trade_name') }}</label>
                        <input type="text" v-model="form.trade_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.tax_id') }}</label>
                        <input type="text" v-model="form.nif" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': form.errors.nif }" />
                        <p v-if="form.errors.nif" class="mt-1 text-sm text-red-600">{{ form.errors.nif }}</p>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('settings.section_address') }}</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.address') }}</label>
                        <input type="text" v-model="form.address_street" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.postal_code') }}</label>
                        <input type="text" v-model="form.address_postal_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.city') }}</label>
                        <input type="text" v-model="form.address_city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.province') }}</label>
                        <input type="text" v-model="form.address_province" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.country') }}</label>
                        <input type="text" v-model="form.address_country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" maxlength="2" />
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('settings.section_contact') }}</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.phone') }}</label>
                        <input type="text" v-model="form.phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.email') }}</label>
                        <input type="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $t('settings.web') }}</label>
                        <input type="text" v-model="form.website" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>
            </div>

            <!-- Fiscal + Logo -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('settings.section_fiscal') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $t('settings.tax_regime') }}</label>
                            <select v-model="form.tax_regime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="general">{{ $t('settings.regime_general') }}</option>
                                <option value="simplified">{{ $t('settings.regime_simplified') }}</option>
                                <option value="surcharge">{{ $t('settings.regime_surcharge') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $t('settings.default_irpf') }}</label>
                            <input type="number" v-model="form.irpf_rate" step="0.5" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('settings.section_logo') }}</h3>
                    <div class="space-y-3">
                        <!-- Current / new logo preview -->
                        <div v-if="logoPreview || logoUrl" class="flex items-center gap-4">
                            <img
                                :src="logoPreview || logoUrl!"
                                alt="Logo"
                                class="h-16 w-auto max-w-[200px] rounded border border-gray-200 object-contain bg-white p-1"
                            />
                            <button
                                v-if="!logoPreview && logoUrl"
                                type="button"
                                @click="deleteLogo"
                                class="text-sm text-red-600 hover:text-red-800"
                            >{{ $t('settings.remove_logo') }}</button>
                        </div>
                        <input type="file" @change="handleLogoChange" accept=".jpg,.jpeg,.png,.svg,.webp" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p v-if="form.errors.logo" class="text-sm text-red-600">{{ form.errors.logo }}</p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                    {{ form.processing ? $t('common.saving') : $t('common.save_changes') }}
                </button>
            </div>
        </form>

        <!-- Public Catalog -->
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-gray-900">{{ $t('settings.section_catalog') }}</h3>
            <p class="mb-4 text-sm text-gray-500">{{ $t('settings.catalog_description') }}</p>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    role="switch"
                    :aria-checked="form.catalog_enabled"
                    @click="form.catalog_enabled = !form.catalog_enabled; submit()"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                    :class="form.catalog_enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                >
                    <span
                        class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="form.catalog_enabled ? 'translate-x-5' : 'translate-x-0'"
                    />
                </button>
                <span class="text-sm font-medium text-gray-700">
                    {{ form.catalog_enabled ? $t('settings.catalog_enabled') : $t('settings.catalog_disabled') }}
                </span>
            </div>

            <div v-if="form.catalog_enabled" class="mt-4 rounded-md bg-indigo-50 p-3">
                <p class="text-xs font-medium text-gray-600">{{ $t('settings.catalog_public_url') }}</p>
                <a
                    :href="catalogPublicUrl"
                    target="_blank"
                    class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800"
                >
                    {{ catalogPublicUrl }}
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- VeriFactu -->
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-gray-900">{{ $t('settings.section_verifactu') }}</h3>
            <p class="mb-4 text-sm text-gray-500">{{ $t('settings.verifactu_description') }}</p>

            <form @submit.prevent="submitVerifactu" class="space-y-4">
                <!-- Toggle -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="verifactuForm.verifactu_enabled"
                        @click="verifactuForm.verifactu_enabled = !verifactuForm.verifactu_enabled"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                        :class="verifactuForm.verifactu_enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                    >
                        <span
                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="verifactuForm.verifactu_enabled ? 'translate-x-5' : 'translate-x-0'"
                        />
                    </button>
                    <span class="text-sm font-medium text-gray-700">
                        {{ verifactuForm.verifactu_enabled ? $t('settings.verifactu_enabled') : $t('settings.verifactu_disabled') }}
                    </span>
                </div>

                <!-- Environment selector (only visible if enabled) -->
                <div v-if="verifactuForm.verifactu_enabled" class="max-w-xs">
                    <label class="block text-sm font-medium text-gray-700">{{ $t('settings.verifactu_environment') }}</label>

                    <!-- Locked production badge -->
                    <div v-if="isProductionLocked" class="mt-1 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            {{ $t('settings.verifactu_production') }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $t('settings.verifactu_production_locked') }}</span>
                    </div>

                    <!-- Editable selector -->
                    <select
                        v-else
                        v-model="verifactuForm.verifactu_environment"
                        @change="onEnvironmentChange(($event.target as HTMLSelectElement).value)"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="sandbox">{{ $t('settings.verifactu_sandbox') }}</option>
                        <option value="production">{{ $t('settings.verifactu_production') }}</option>
                    </select>
                </div>

                <!-- Save button -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="verifactuForm.processing"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{ verifactuForm.processing ? $t('common.saving') : $t('common.save_changes') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Confirm production dialog -->
        <ConfirmDialog
            :show="showProductionConfirm"
            :title="$t('settings.verifactu_confirm_production_title')"
            :message="$t('settings.verifactu_confirm_production_message')"
            :confirm-label="$t('settings.verifactu_confirm_production_button')"
            @confirm="confirmProduction"
            @cancel="cancelProduction"
        />

        <!-- Demo Data -->
        <div class="mt-6 rounded-lg border border-amber-300 bg-amber-50 p-4 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-amber-800">{{ $t('settings.section_demo') }}</h3>
            <p class="mb-3 text-sm text-amber-700">
                {{ $t('settings.demo_description') }}
            </p>

            <div v-if="!showDemoConfirm">
                <button
                    type="button"
                    @click="showDemoConfirm = true"
                    class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500"
                >
                    {{ $t('settings.generate_demo') }}
                </button>
            </div>
            <div v-else class="rounded-md border border-amber-400 bg-amber-100 p-3">
                <p class="mb-3 text-sm font-medium text-amber-900">
                    {{ $t('settings.demo_confirm') }}
                </p>
                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="seedDemoData"
                        :disabled="demoProcessing"
                        class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 disabled:opacity-50"
                    >
                        {{ demoProcessing ? $t('settings.generating') : $t('common.confirm') }}
                    </button>
                    <button
                        type="button"
                        @click="showDemoConfirm = false"
                        :disabled="demoProcessing"
                        class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                    >
                        {{ $t('common.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
