<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

withDefaults(defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    processing?: boolean;
}>(), {
    title: 'Confirmar acción',
    message: '¿Estás seguro de que quieres continuar?',
    confirmLabel: 'Confirmar',
    cancelLabel: 'Cancelar',
    processing: false,
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <Modal :show="show" @close="emit('cancel')" max-width="md">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
            <div class="mt-2">
                <slot>
                    <p class="text-sm text-gray-600">{{ message }}</p>
                </slot>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="emit('cancel')" :disabled="processing">
                    {{ cancelLabel }}
                </SecondaryButton>
                <DangerButton @click="emit('confirm')" :disabled="processing">
                    {{ confirmLabel }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>
