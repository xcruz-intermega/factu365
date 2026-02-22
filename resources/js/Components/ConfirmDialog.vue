<script setup lang="ts">
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = withDefaults(defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    processing?: boolean;
}>(), {
    title: '',
    message: '',
    confirmLabel: '',
    cancelLabel: '',
    processing: false,
});

const resolvedTitle = computed(() => props.title || trans('common.confirm_action'));
const resolvedMessage = computed(() => props.message || trans('common.confirm_message'));
const resolvedConfirmLabel = computed(() => props.confirmLabel || trans('common.confirm'));
const resolvedCancelLabel = computed(() => props.cancelLabel || trans('common.cancel'));

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <Modal :show="show" @close="emit('cancel')" max-width="md">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">{{ resolvedTitle }}</h3>
            <div class="mt-2">
                <slot>
                    <p class="text-sm text-gray-600">{{ resolvedMessage }}</p>
                </slot>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="emit('cancel')" :disabled="processing">
                    {{ resolvedCancelLabel }}
                </SecondaryButton>
                <DangerButton @click="emit('confirm')" :disabled="processing">
                    {{ resolvedConfirmLabel }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>
