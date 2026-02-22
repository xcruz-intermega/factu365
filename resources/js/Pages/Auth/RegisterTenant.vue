<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const host = window.location.host;

const form = useForm({
    company_name: '',
    slug: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const sanitizeSlug = () => {
    form.slug = form.slug
        .toLowerCase()
        .replace(/[^a-z0-9-]/g, '')
        .replace(/^-+|-+$/g, '');
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear cuenta" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="company_name" value="Nombre de la empresa" />
                <TextInput
                    id="company_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.company_name"
                    required
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.company_name" />
            </div>

            <div class="mt-4">
                <InputLabel for="slug" value="Identificador de empresa" />
                <div class="mt-1 flex items-center">
                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                        {{ host }}/
                    </span>
                    <TextInput
                        id="slug"
                        type="text"
                        class="block w-full rounded-l-none"
                        v-model="form.slug"
                        @input="sanitizeSlug"
                        required
                        placeholder="mi-empresa"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.slug" />
            </div>

            <div class="mt-4">
                <InputLabel for="name" value="Tu nombre" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar contraseña" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Crear cuenta
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
