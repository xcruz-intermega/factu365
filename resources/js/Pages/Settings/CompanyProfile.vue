<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';

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
}

const props = defineProps<{
    company: Company | null;
}>();

const c = props.company;

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
});

const handleLogoChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        form.logo = input.files[0];
    }
};

const submit = () => {
    form.post(route('settings.company.update'), {
        forceFormData: true,
    });
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

        <SettingsNav current="company" />

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
                        <input type="file" @change="handleLogoChange" accept=".jpg,.jpeg,.png,.svg,.webp" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p v-if="form.errors.logo" class="text-sm text-red-600">{{ form.errors.logo }}</p>
                        <p v-if="company?.logo_path" class="text-xs text-gray-500">{{ $t('settings.logo_exists') }}</p>
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
