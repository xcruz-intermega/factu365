<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Facturación para autónomos y PYMEs" />

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <img src="/images/logo.svg" alt="Factu365" class="h-10 w-auto object-contain" />
                    <span class="text-2xl font-bold text-indigo-600">Factu365</span>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('register')"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Crear cuenta gratis
                    </Link>
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
    </div>
</template>
