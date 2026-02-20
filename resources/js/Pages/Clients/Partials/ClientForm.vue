<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Link } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

defineProps<{
    form: InertiaForm<{
        type: string;
        legal_name: string;
        trade_name: string;
        nif: string;
        address_street: string;
        address_city: string;
        address_postal_code: string;
        address_province: string;
        address_country: string;
        phone: string;
        email: string;
        website: string;
        contact_person: string;
        payment_terms_days: number;
        payment_method: string;
        iban: string;
        notes: string;
    }>;
    submitLabel: string;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const provinces = [
    'Álava', 'Albacete', 'Alicante', 'Almería', 'Asturias', 'Ávila', 'Badajoz',
    'Barcelona', 'Burgos', 'Cáceres', 'Cádiz', 'Cantabria', 'Castellón', 'Ciudad Real',
    'Córdoba', 'A Coruña', 'Cuenca', 'Girona', 'Granada', 'Guadalajara', 'Gipuzkoa',
    'Huelva', 'Huesca', 'Illes Balears', 'Jaén', 'León', 'Lleida', 'Lugo', 'Madrid',
    'Málaga', 'Murcia', 'Navarra', 'Ourense', 'Palencia', 'Las Palmas', 'Pontevedra',
    'La Rioja', 'Salamanca', 'Santa Cruz de Tenerife', 'Segovia', 'Sevilla', 'Soria',
    'Tarragona', 'Teruel', 'Toledo', 'Valencia', 'Valladolid', 'Bizkaia', 'Zamora', 'Zaragoza',
    'Ceuta', 'Melilla',
];
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-8">
        <!-- Tipo e identificación -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Datos de identificación</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="type" value="Tipo *" />
                    <select
                        id="type"
                        v-model="form.type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="customer">Cliente</option>
                        <option value="supplier">Proveedor</option>
                        <option value="both">Cliente y proveedor</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div>
                    <InputLabel for="legal_name" value="Razón social *" />
                    <TextInput
                        id="legal_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.legal_name"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.legal_name" />
                </div>

                <div>
                    <InputLabel for="nif" value="NIF/CIF/NIE *" />
                    <TextInput
                        id="nif"
                        type="text"
                        class="mt-1 block w-full uppercase"
                        v-model="form.nif"
                        required
                        placeholder="B12345678"
                    />
                    <InputError class="mt-2" :message="form.errors.nif" />
                </div>

                <div>
                    <InputLabel for="trade_name" value="Nombre comercial" />
                    <TextInput
                        id="trade_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.trade_name"
                    />
                    <InputError class="mt-2" :message="form.errors.trade_name" />
                </div>
            </div>
        </div>

        <!-- Dirección -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Dirección</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-3">
                    <InputLabel for="address_street" value="Dirección" />
                    <TextInput
                        id="address_street"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address_street"
                        placeholder="Calle, número, piso..."
                    />
                    <InputError class="mt-2" :message="form.errors.address_street" />
                </div>

                <div>
                    <InputLabel for="address_city" value="Ciudad" />
                    <TextInput
                        id="address_city"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address_city"
                    />
                    <InputError class="mt-2" :message="form.errors.address_city" />
                </div>

                <div>
                    <InputLabel for="address_postal_code" value="Código postal" />
                    <TextInput
                        id="address_postal_code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.address_postal_code"
                        placeholder="28001"
                    />
                    <InputError class="mt-2" :message="form.errors.address_postal_code" />
                </div>

                <div>
                    <InputLabel for="address_province" value="Provincia" />
                    <select
                        id="address_province"
                        v-model="form.address_province"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar...</option>
                        <option v-for="p in provinces" :key="p" :value="p">{{ p }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.address_province" />
                </div>
            </div>
        </div>

        <!-- Contacto -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Contacto</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="contact_person" value="Persona de contacto" />
                    <TextInput
                        id="contact_person"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.contact_person"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_person" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="phone" value="Teléfono" />
                    <TextInput
                        id="phone"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <InputLabel for="website" value="Web" />
                    <TextInput
                        id="website"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.website"
                        placeholder="https://..."
                    />
                    <InputError class="mt-2" :message="form.errors.website" />
                </div>
            </div>
        </div>

        <!-- Pago -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Condiciones de pago</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <InputLabel for="payment_method" value="Forma de pago" />
                    <select
                        id="payment_method"
                        v-model="form.payment_method"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="transfer">Transferencia</option>
                        <option value="cash">Efectivo</option>
                        <option value="card">Tarjeta</option>
                        <option value="check">Cheque</option>
                        <option value="direct_debit">Domiciliación</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.payment_method" />
                </div>

                <div>
                    <InputLabel for="payment_terms_days" value="Plazo de pago (días)" />
                    <TextInput
                        id="payment_terms_days"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.payment_terms_days"
                        min="0"
                        max="365"
                    />
                    <InputError class="mt-2" :message="form.errors.payment_terms_days" />
                </div>

                <div>
                    <InputLabel for="iban" value="IBAN" />
                    <TextInput
                        id="iban"
                        type="text"
                        class="mt-1 block w-full uppercase"
                        v-model="form.iban"
                        placeholder="ES00 0000 0000 0000 0000 0000"
                    />
                    <InputError class="mt-2" :message="form.errors.iban" />
                </div>
            </div>
        </div>

        <!-- Notas -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Notas</h3>
            <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Notas internas..."
            />
            <InputError class="mt-2" :message="form.errors.notes" />
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <Link :href="route('clients.index')">
                <SecondaryButton type="button">Cancelar</SecondaryButton>
            </Link>
            <PrimaryButton :disabled="form.processing">
                {{ submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
