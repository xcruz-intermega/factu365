<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();

const fmt = (n: number) => n.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

<template>
    <div style="margin-bottom: 16px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 3px 6px; font-size: 7.5pt; color: #6b7280;">Subtotal</td>
                            <td style="padding: 3px 6px; font-size: 7.5pt; text-align: right;">{{ fmt(sampleData.document.subtotal) }} €</td>
                        </tr>
                        <tr v-if="sampleData.document.total_discount > 0">
                            <td style="padding: 3px 6px; font-size: 7.5pt; color: #6b7280;">Descuento</td>
                            <td style="padding: 3px 6px; font-size: 7.5pt; text-align: right;">-{{ fmt(sampleData.document.total_discount) }} €</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 6px; font-size: 7.5pt; color: #6b7280; border-top: 1px solid #e5e7eb;">Base imponible</td>
                            <td style="padding: 3px 6px; font-size: 7.5pt; text-align: right; border-top: 1px solid #e5e7eb;">{{ fmt(sampleData.document.tax_base) }} €</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 6px; font-size: 7.5pt; color: #6b7280;">IVA</td>
                            <td style="padding: 3px 6px; font-size: 7.5pt; text-align: right;">{{ fmt(sampleData.document.total_vat) }} €</td>
                        </tr>
                        <tr v-if="sampleData.document.total_surcharge > 0">
                            <td style="padding: 3px 6px; font-size: 7.5pt; color: #6b7280;">Recargo equivalencia</td>
                            <td style="padding: 3px 6px; font-size: 7.5pt; text-align: right;">{{ fmt(sampleData.document.total_surcharge) }} €</td>
                        </tr>
                        <tr v-if="sampleData.document.total_irpf > 0">
                            <td style="padding: 3px 6px; font-size: 7.5pt; color: #6b7280;">Retención IRPF</td>
                            <td style="padding: 3px 6px; font-size: 7.5pt; text-align: right; color: #dc2626;">-{{ fmt(sampleData.document.total_irpf) }} €</td>
                        </tr>
                        <tr>
                            <td :style="{
                                padding: '6px 6px 3px',
                                fontWeight: 'bold',
                                fontSize: '10pt',
                                borderTop: '2px solid ' + (options.highlight_color || global.accent_color || '#4f46e5'),
                                color: options.highlight_color || global.accent_color || '#4f46e5',
                            }">TOTAL</td>
                            <td :style="{
                                padding: '6px 6px 3px',
                                fontWeight: 'bold',
                                fontSize: '10pt',
                                textAlign: 'right',
                                borderTop: '2px solid ' + (options.highlight_color || global.accent_color || '#4f46e5'),
                                color: options.highlight_color || global.accent_color || '#4f46e5',
                            }">{{ fmt(sampleData.document.total) }} €</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</template>
