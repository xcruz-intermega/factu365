<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from './Partials/SettingsNav.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

interface TemplateLine {
    days_from_issue: number;
    percentage: number;
}

interface Template {
    id: number;
    name: string;
    is_default: boolean;
    lines: TemplateLine[];
}

const props = defineProps<{
    templates: Template[];
}>();

// New template form
const showNew = ref(false);
const newForm = useForm({
    name: '',
    is_default: false,
    lines: [{ days_from_issue: 0, percentage: 100 }] as TemplateLine[],
});

const addNewLine = () => {
    newForm.lines.push({ days_from_issue: 30, percentage: 0 });
};

const removeNewLine = (index: number) => {
    newForm.lines.splice(index, 1);
};

const submitNew = () => {
    newForm.post(route('settings.payment-templates.store'), {
        onSuccess: () => {
            showNew.value = false;
            newForm.reset();
            newForm.lines = [{ days_from_issue: 0, percentage: 100 }];
        },
    });
};

// Edit template
const editingId = ref<number | null>(null);
const editForm = useForm({
    name: '',
    is_default: false,
    lines: [] as TemplateLine[],
});

const startEdit = (tpl: Template) => {
    editingId.value = tpl.id;
    editForm.name = tpl.name;
    editForm.is_default = tpl.is_default;
    editForm.lines = tpl.lines.map(l => ({
        days_from_issue: l.days_from_issue,
        percentage: Number(l.percentage),
    }));
};

const addEditLine = () => {
    editForm.lines.push({ days_from_issue: 30, percentage: 0 });
};

const removeEditLine = (index: number) => {
    editForm.lines.splice(index, 1);
};

const submitEdit = (id: number) => {
    editForm.put(route('settings.payment-templates.update', id), {
        onSuccess: () => { editingId.value = null; },
    });
};

// Delete (modal confirmation)
const deleteDialog = ref(false);
const deleteTarget = ref<Template | null>(null);
const deleting = ref(false);

const confirmDelete = (tpl: Template) => {
    deleteTarget.value = tpl;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('settings.payment-templates.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteDialog.value = false;
            deleteTarget.value = null;
        },
    });
};

const totalPercentage = (lines: TemplateLine[]) => {
    return lines.reduce((sum, l) => sum + Number(l.percentage), 0);
};
</script>

<template>
    <Head :title="$t('settings.templates_title')" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">{{ $t('settings.templates_title') }}</h1>
        </template>

        <SettingsNav current="payment-templates" />

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">{{ $t('settings.templates_section') }}</h3>
                <button
                    @click="showNew = !showNew"
                    class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-100"
                >
                    <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    {{ $t('settings.new_template') }}
                </button>
            </div>

            <!-- New template form -->
            <div v-if="showNew" class="mb-6 rounded-md border border-indigo-200 bg-indigo-50 p-4">
                <form @submit.prevent="submitNew" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700">{{ $t('settings.template_name') }}</label>
                            <input type="text" v-model="newForm.name" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" v-model="newForm.is_default" class="rounded border-gray-300 text-indigo-600" />
                                {{ $t('settings.template_default') }}
                            </label>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-700">{{ $t('settings.template_due_dates') }}</label>
                            <span class="text-xs" :class="totalPercentage(newForm.lines) === 100 ? 'text-green-600' : 'text-red-600'">
                                {{ $t('settings.template_total') }} {{ totalPercentage(newForm.lines).toFixed(2) }}%
                            </span>
                        </div>
                        <div v-for="(line, i) in newForm.lines" :key="i" class="mb-2 flex items-center gap-3">
                            <div>
                                <label class="text-xs text-gray-500">{{ $t('settings.template_days') }}</label>
                                <input type="number" v-model.number="line.days_from_issue" min="0" class="block w-20 rounded-md border-gray-300 text-sm" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ $t('settings.template_pct') }}</label>
                                <input type="number" v-model.number="line.percentage" min="0" max="100" step="0.01" class="block w-24 rounded-md border-gray-300 text-sm" />
                            </div>
                            <button v-if="newForm.lines.length > 1" type="button" @click="removeNewLine(i)" class="mt-4 text-red-500 hover:text-red-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <button type="button" @click="addNewLine" class="text-sm text-indigo-600 hover:text-indigo-800">{{ $t('settings.add_due_date') }}</button>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="newForm.processing" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50">{{ $t('common.create') }}</button>
                        <button type="button" @click="showNew = false" class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">{{ $t('common.cancel') }}</button>
                    </div>
                </form>
            </div>

            <!-- Templates list -->
            <div class="space-y-4">
                <div v-for="tpl in templates" :key="tpl.id" class="rounded-md border border-gray-200 p-4">
                    <template v-if="editingId === tpl.id">
                        <form @submit.prevent="submitEdit(tpl.id)" class="space-y-4">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700">{{ $t('settings.template_name') }}</label>
                                    <input type="text" v-model="editForm.name" class="mt-0.5 block w-full rounded-md border-gray-300 text-sm" />
                                </div>
                                <div class="flex items-end">
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" v-model="editForm.is_default" class="rounded border-gray-300 text-indigo-600" />
                                        {{ $t('settings.template_default') }}
                                    </label>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <label class="text-xs font-medium text-gray-700">{{ $t('settings.template_due_dates') }}</label>
                                    <span class="text-xs" :class="totalPercentage(editForm.lines) === 100 ? 'text-green-600' : 'text-red-600'">
                                        {{ $t('settings.template_total') }} {{ totalPercentage(editForm.lines).toFixed(2) }}%
                                    </span>
                                </div>
                                <div v-for="(line, i) in editForm.lines" :key="i" class="mb-2 flex items-center gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">{{ $t('settings.template_days') }}</label>
                                        <input type="number" v-model.number="line.days_from_issue" min="0" class="block w-20 rounded-md border-gray-300 text-sm" />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">%</label>
                                        <input type="number" v-model.number="line.percentage" min="0" max="100" step="0.01" class="block w-24 rounded-md border-gray-300 text-sm" />
                                    </div>
                                    <button v-if="editForm.lines.length > 1" type="button" @click="removeEditLine(i)" class="mt-4 text-red-500 hover:text-red-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <button type="button" @click="addEditLine" class="text-sm text-indigo-600 hover:text-indigo-800">{{ $t('settings.add_due_date') }}</button>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" :disabled="editForm.processing" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50">{{ $t('common.save') }}</button>
                                <button type="button" @click="editingId = null" class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">{{ $t('common.cancel') }}</button>
                            </div>
                        </form>
                    </template>
                    <template v-else>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-gray-900">{{ tpl.name }}</span>
                                <span v-if="tpl.is_default" class="ml-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ $t('settings.template_default') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="startEdit(tpl)" class="text-sm text-indigo-600 hover:text-indigo-900">{{ $t('common.edit') }}</button>
                                <button @click="confirmDelete(tpl)" class="text-sm text-red-600 hover:text-red-900">{{ $t('common.delete') }}</button>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-3">
                            <span v-for="(line, i) in tpl.lines" :key="i" class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600">
                                {{ Number(line.percentage) }}% {{ $t('settings.template_at') }} {{ line.days_from_issue }} {{ $t('settings.template_days_unit') }}
                            </span>
                        </div>
                    </template>
                </div>

                <div v-if="templates.length === 0" class="py-8 text-center text-sm text-gray-500">
                    {{ $t('settings.no_templates') }}
                </div>
            </div>
        </div>

        <ConfirmDialog
            :show="deleteDialog"
            :title="$t('settings.delete_template_title')"
            :message="trans('settings.delete_template_message', { name: deleteTarget?.name || '' })"
            :confirm-label="$t('common.delete')"
            :processing="deleting"
            @confirm="executeDelete"
            @cancel="deleteDialog = false"
        />
    </AppLayout>
</template>
