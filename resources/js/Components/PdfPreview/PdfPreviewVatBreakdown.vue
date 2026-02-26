<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();

const fmt = (n: number) => n.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

<template>
    <div v-if="sampleData.vatBreakdown.length > 0" style="margin-bottom: 12px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="background: #f9fafb; padding: 3px 6px; text-align: left; font-size: 6pt; text-transform: uppercase; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Tipo IVA</th>
                    <th style="background: #f9fafb; padding: 3px 6px; text-align: right; font-size: 6pt; text-transform: uppercase; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Base imponible</th>
                    <th style="background: #f9fafb; padding: 3px 6px; text-align: right; font-size: 6pt; text-transform: uppercase; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Cuota IVA</th>
                    <th v-if="sampleData.vatBreakdown.reduce((s: number, v: any) => s + v.surcharge, 0) > 0" style="background: #f9fafb; padding: 3px 6px; text-align: right; font-size: 6pt; text-transform: uppercase; color: #9ca3af; border-bottom: 1px solid #e5e7eb;">Recargo</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(vat, idx) in sampleData.vatBreakdown" :key="idx">
                    <td style="padding: 3px 6px; font-size: 7pt; border-bottom: 1px solid #f3f4f6;">{{ vat.vat_rate }}%</td>
                    <td style="padding: 3px 6px; font-size: 7pt; text-align: right; border-bottom: 1px solid #f3f4f6;">{{ fmt(vat.base) }} €</td>
                    <td style="padding: 3px 6px; font-size: 7pt; text-align: right; border-bottom: 1px solid #f3f4f6;">{{ fmt(vat.vat) }} €</td>
                    <td v-if="sampleData.vatBreakdown.reduce((s: number, v: any) => s + v.surcharge, 0) > 0" style="padding: 3px 6px; font-size: 7pt; text-align: right; border-bottom: 1px solid #f3f4f6;">{{ fmt(vat.surcharge) }} €</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
