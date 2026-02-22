<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const showLogin = ref(false);
const appVersion = usePage().props.app_version as string;

const form = useForm({
    company: '',
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('central.login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Facturación para autónomos y PYMEs" />

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <img src="/images/logo.svg" alt="Factu365" class="h-10 w-auto object-contain" />
                    <span class="font-brand text-[30px] font-extrabold text-indigo-600">Factu365</span>
                </div>
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        class="rounded-md px-4 py-2 text-sm font-semibold text-indigo-600 hover:text-indigo-500"
                        @click="showLogin = !showLogin"
                    >
                        Iniciar sesión
                    </button>
                    <Link
                        :href="route('register')"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Crear cuenta gratis
                    </Link>
                </div>
            </div>

            <!-- Login dropdown panel -->
            <div v-if="showLogin" class="border-t border-gray-200 bg-white">
                <div class="mx-auto max-w-md px-4 py-6 sm:px-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="company" value="Empresa" />
                            <TextInput
                                id="company"
                                v-model="form.company"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Identificador o nombre de empresa"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.company" />
                        </div>

                        <div>
                            <InputLabel for="login-email" value="Email" />
                            <TextInput
                                id="login-email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="login-password" value="Contraseña" />
                            <TextInput
                                id="login-password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center">
                            <Checkbox id="remember" v-model:checked="form.remember" />
                            <label for="remember" class="ms-2 text-sm text-gray-600">
                                Recordarme
                            </label>
                        </div>

                        <PrimaryButton
                            class="w-full justify-center"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Iniciar sesión
                        </PrimaryButton>
                    </form>
                </div>
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Facturación con VERI*FACTU
                    </h2>
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600">
                        Software de facturación para autónomos y PYMEs en España.
                        Cumple con la normativa VERI*FACTU (RD 1007/2023).
                    </p>
                    <div class="mt-10">
                        <Link
                            :href="route('register')"
                            class="rounded-md bg-indigo-600 px-6 py-3 text-lg font-semibold text-white shadow-sm hover:bg-indigo-500"
                        >
                            Empezar gratis
                        </Link>
                    </div>
                </div>

                <div class="mt-20 grid grid-cols-1 gap-8 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-900">Facturas y presupuestos</h3>
                        <p class="mt-2 text-gray-600">
                            Crea facturas, presupuestos y albaranes. Convierte entre documentos con un clic.
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-900">VERI*FACTU</h3>
                        <p class="mt-2 text-gray-600">
                            Envío automático a la AEAT. Hash encadenado, XML firmado y QR verificable.
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-900">Informes fiscales</h3>
                        <p class="mt-2 text-gray-600">
                            Modelo 303, 390 y 130. Dashboard con gráficos de facturación y gastos.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-gray-200 py-6 text-center text-xs text-gray-400">
            Factu365 v{{ appVersion }}
        </footer>
    </div>
</template>
