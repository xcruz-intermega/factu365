<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();
</script>

<template>
    <div style="margin-top: 12px;">
        <table style="width: 100%;">
            <tr>
                <template v-if="options.position === 'right'">
                    <td>
                        <div v-if="options.show_legal_text !== false" style="font-size: 6pt; color: #9ca3af; padding-right: 8px;">
                            <strong>Código QR VERI*FACTU</strong><br>
                            Factura verificable conforme al RD 1007/2023.<br>
                            Escanee el código QR para verificar la autenticidad de esta factura en la Agencia Tributaria.
                        </div>
                    </td>
                    <td :style="{ width: ((options.size || 80) + 10) + 'px', textAlign: 'right' }">
                        <div :style="{
                            width: (options.size || 80) + 'px',
                            height: (options.size || 80) + 'px',
                            background: '#e5e7eb',
                            borderRadius: '4px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            fontSize: '6pt',
                            color: '#9ca3af',
                        }">QR</div>
                    </td>
                </template>
                <template v-else>
                    <td :style="{ width: ((options.size || 80) + 10) + 'px' }">
                        <div :style="{
                            width: (options.size || 80) + 'px',
                            height: (options.size || 80) + 'px',
                            background: '#e5e7eb',
                            borderRadius: '4px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            fontSize: '6pt',
                            color: '#9ca3af',
                        }">QR</div>
                    </td>
                    <td>
                        <div v-if="options.show_legal_text !== false" style="font-size: 6pt; color: #9ca3af; padding-left: 8px;">
                            <strong>Código QR VERI*FACTU</strong><br>
                            Factura verificable conforme al RD 1007/2023.<br>
                            Escanee el código QR para verificar la autenticidad de esta factura en la Agencia Tributaria.
                        </div>
                    </td>
                </template>
            </tr>
        </table>
    </div>
</template>
