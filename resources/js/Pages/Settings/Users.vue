<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';
interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface Role {
    value: string;
    label: string;
}

const props = defineProps<{
    users: User[];
    roles: Role[];
}>();

const page = usePage();
const currentUser = page.props.auth.user;

const roleLabels: Record<string, string> = {
    owner: 'Propietario',
    admin: 'Administrador',
    accountant: 'Contable',
    viewer: 'Visualizador',
};

const roleColors: Record<string, string> = {
    owner: 'bg-purple-100 text-purple-800',
    admin: 'bg-blue-100 text-blue-800',
    accountant: 'bg-green-100 text-green-800',
    viewer: 'bg-gray-100 text-gray-800',
};

// Create form
const showCreateForm = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'accountant',
});

const submitCreate = () => {
    createForm.post(route('settings.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateForm.value = false;
            createForm.reset();
        },
    });
};

// Edit form
const editingUser = ref<User | null>(null);
const editForm = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
});

const startEdit = (user: User) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.password = '';
    editForm.password_confirmation = '';
};

const cancelEdit = () => {
    editingUser.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const submitEdit = () => {
    if (!editingUser.value) return;
    editForm.put(route('settings.users.update', editingUser.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingUser.value = null;
            editForm.reset();
        },
    });
};

// Delete
const confirmingDeleteId = ref<number | null>(null);
const deleting = ref(false);

const confirmDelete = (user: User) => {
    confirmingDeleteId.value = user.id;
};

const cancelDelete = () => {
    confirmingDeleteId.value = null;
};

const executeDelete = (id: number) => {
    deleting.value = true;
    router.delete(route('settings.users.destroy', id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            confirmingDeleteId.value = null;
        },
    });
};

const availableRoles = (user?: User) => {
    if (!user) return props.roles;
    // Owner role can only be shown if user is already owner
    if (user.role === 'owner') {
        return [...props.roles, { value: 'owner', label: 'Propietario' }];
    }
    return currentUser.role === 'owner'
        ? [...props.roles, { value: 'owner', label: 'Propietario' }]
        : props.roles;
};
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Usuarios</h1>
        </template>

        <SettingsNav current="users" />

        <!-- Add user button -->
        <div class="mb-4 flex justify-end">
            <button
                v-if="!showCreateForm"
                @click="showCreateForm = true"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo usuario
            </button>
        </div>

        <!-- Create form -->
        <div v-if="showCreateForm" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Crear nuevo usuario</h3>
            <form @submit.prevent="submitCreate" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input type="text" v-model="createForm.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': createForm.errors.name }" />
                        <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" v-model="createForm.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': createForm.errors.email }" />
                        <p v-if="createForm.errors.email" class="mt-1 text-sm text-red-600">{{ createForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rol *</label>
                        <select v-model="createForm.role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                        </select>
                        <p v-if="createForm.errors.role" class="mt-1 text-sm text-red-600">{{ createForm.errors.role }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contrase&ntilde;a *</label>
                        <input type="password" v-model="createForm.password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': createForm.errors.password }" />
                        <p v-if="createForm.errors.password" class="mt-1 text-sm text-red-600">{{ createForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirmar contrase&ntilde;a *</label>
                        <input type="password" v-model="createForm.password_confirmation" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showCreateForm = false; createForm.reset(); createForm.clearErrors()" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" :disabled="createForm.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                        {{ createForm.processing ? 'Creando...' : 'Crear usuario' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Users table -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Rol</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha alta</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="user in users" :key="user.id">
                        <!-- View mode -->
                        <template v-if="editingUser?.id !== user.id">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                                {{ user.name }}
                                <span v-if="user.id === currentUser.id" class="ml-1 text-xs text-gray-400">(t&uacute;)</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ user.email }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <span :class="roleColors[user.role]" class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold">
                                    {{ roleLabels[user.role] || user.role }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ user.created_at }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <template v-if="confirmingDeleteId === user.id">
                                    <span class="text-xs text-red-600 mr-2">Eliminar?</span>
                                    <button @click="executeDelete(user.id)" :disabled="deleting" class="text-sm font-semibold text-red-600 hover:text-red-900 mr-1 disabled:opacity-50">Si</button>
                                    <button @click="cancelDelete" class="text-sm text-gray-600 hover:text-gray-900">No</button>
                                </template>
                                <template v-else>
                                    <button
                                        v-if="user.role !== 'owner' || user.id === currentUser.id"
                                        @click="startEdit(user)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        v-if="user.id !== currentUser.id && user.role !== 'owner'"
                                        @click="confirmDelete(user)"
                                        class="ml-3 text-red-600 hover:text-red-900"
                                    >
                                        Eliminar
                                    </button>
                                </template>
                            </td>
                        </template>
                        <!-- Edit mode -->
                        <template v-else>
                            <td class="px-4 py-2">
                                <input type="text" v-model="editForm.name" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': editForm.errors.name }" />
                                <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-600">{{ editForm.errors.name }}</p>
                            </td>
                            <td class="px-4 py-2">
                                <input type="email" v-model="editForm.email" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500': editForm.errors.email }" />
                                <p v-if="editForm.errors.email" class="mt-1 text-xs text-red-600">{{ editForm.errors.email }}</p>
                            </td>
                            <td class="px-4 py-2">
                                <select v-if="user.id !== currentUser.id" v-model="editForm.role" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option v-for="r in availableRoles(user)" :key="r.value" :value="r.value">{{ r.label }}</option>
                                </select>
                                <span v-else :class="roleColors[user.role]" class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold">
                                    {{ roleLabels[user.role] }}
                                </span>
                            </td>
                            <td class="px-4 py-2" colspan="1">
                                <input type="password" v-model="editForm.password" placeholder="Nueva contrase&ntilde;a (opcional)" autocomplete="new-password" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <input type="password" v-model="editForm.password_confirmation" placeholder="Confirmar" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p v-if="editForm.errors.password" class="mt-1 text-xs text-red-600">{{ editForm.errors.password }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm">
                                <button @click="submitEdit" :disabled="editForm.processing" class="text-indigo-600 hover:text-indigo-900 disabled:opacity-50">Guardar</button>
                                <button @click="cancelEdit" class="ml-2 text-gray-600 hover:text-gray-900">Cancelar</button>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No hay usuarios.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Role hierarchy info -->
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Permisos por rol</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-100 p-3">
                    <span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-800">Propietario</span>
                    <p class="mt-2 text-xs text-gray-600">Acceso completo. Gestiona usuarios, configuraci&oacute;n y toda la facturaci&oacute;n.</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-3">
                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">Administrador</span>
                    <p class="mt-2 text-xs text-gray-600">Gestiona configuraci&oacute;n, usuarios, clientes, productos y documentos.</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-3">
                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Contable</span>
                    <p class="mt-2 text-xs text-gray-600">Crea y edita clientes, productos, facturas, gastos e informes.</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-3">
                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-800">Visualizador</span>
                    <p class="mt-2 text-xs text-gray-600">Solo lectura. Puede consultar datos pero no crear ni modificar.</p>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
