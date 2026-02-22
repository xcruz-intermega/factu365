<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

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
    <Head title="Datos de empresa" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Datos de empresa</h1>
        </template>

        <!-- Settings nav -->
        <div class="mb-6 flex gap-2 border-b border-gray-200 pb-3">
            <Link :href="route('settings.company')" class="rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">Empresa</Link>
            <Link :href="route('settings.series')" class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Series</Link>
            <Link :href="route('settings.certificates')" class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Certificados</Link>
            <Link :href="route('settings.pdf-templates')" class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Plantillas PDF</Link>
            <Link :href="route('settings.users')" class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Usuarios</Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Identity -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Identidad fiscal</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Razón social *</label>
                        <input type="text" v-model="form.legal_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': form.errors.legal_name }" />
                        <p v-if="form.errors.legal_name" class="mt-1 text-sm text-red-600">{{ form.errors.legal_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre comercial</label>
                        <input type="text" v-model="form.trade_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIF/CIF *</label>
                        <input type="text" v-model="form.nif" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': form.errors.nif }" />
                        <p v-if="form.errors.nif" class="mt-1 text-sm text-red-600">{{ form.errors.nif }}</p>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Dirección</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-sm font-medium text-gray-700">Dirección</label>
                        <input type="text" v-model="form.address_street" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código postal</label>
                        <input type="text" v-model="form.address_postal_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ciudad</label>
                        <input type="text" v-model="form.address_city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provincia</label>
                        <input type="text" v-model="form.address_province" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">País</label>
                        <input type="text" v-model="form.address_country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" maxlength="2" />
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Contacto</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" v-model="form.phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Web</label>
                        <input type="text" v-model="form.website" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>
            </div>

            <!-- Fiscal + Logo -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Fiscal</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Régimen fiscal</label>
                            <select v-model="form.tax_regime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="general">General</option>
                                <option value="simplified">Simplificado</option>
                                <option value="surcharge">Recargo de equivalencia</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Retención IRPF predeterminada (%)</label>
                            <input type="number" v-model="form.irpf_rate" step="0.5" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Logo</h3>
                    <div class="space-y-3">
                        <input type="file" @change="handleLogoChange" accept=".jpg,.jpeg,.png,.svg,.webp" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p v-if="form.errors.logo" class="text-sm text-red-600">{{ form.errors.logo }}</p>
                        <p v-if="company?.logo_path" class="text-xs text-gray-500">Logo actual guardado. Sube uno nuevo para reemplazarlo.</p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                    {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                </button>
            </div>
        </form>

        <!-- Demo Data -->
        <div class="mt-6 rounded-lg border border-amber-300 bg-amber-50 p-4 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-amber-800">Datos de prueba</h3>
            <p class="mb-3 text-sm text-amber-700">
                Genera datos ficticios para demostración: ~100 clientes, 70 productos,
                200 documentos (facturas, presupuestos, albaranes) y 100 gastos.
            </p>

            <div v-if="!showDemoConfirm">
                <button
                    type="button"
                    @click="showDemoConfirm = true"
                    class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500"
                >
                    Generar datos de prueba
                </button>
            </div>
            <div v-else class="rounded-md border border-amber-400 bg-amber-100 p-3">
                <p class="mb-3 text-sm font-medium text-amber-900">
                    Se crearán ~1.000 registros ficticios. Esta acción no se puede deshacer fácilmente. ¿Continuar?
                </p>
                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="seedDemoData"
                        :disabled="demoProcessing"
                        class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 disabled:opacity-50"
                    >
                        {{ demoProcessing ? 'Generando...' : 'Confirmar' }}
                    </button>
                    <button
                        type="button"
                        @click="showDemoConfirm = false"
                        :disabled="demoProcessing"
                        class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
