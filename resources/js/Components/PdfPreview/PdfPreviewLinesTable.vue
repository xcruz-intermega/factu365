<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();

const fmt = (n: number) => n.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const colHeaders: Record<string, string> = {
    concept: 'Concepto',
    qty: 'Cantidad',
    price: 'Precio ud.',
    discount: 'Dto.',
    vat: 'IVA',
    amount: 'Importe',
    unit: 'Unidad',
};
</script>

<template>
    <div style="margin-bottom: 16px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th
                        v-for="col in (options.columns || ['concept','qty','price','discount','vat','amount'])"
                        :key="col"
                        :style="{
                            backgroundColor: '#f3f4f6',
                            borderBottom: '2px solid ' + (global.accent_color || '#4f46e5'),
                            padding: '4px 6px',
                            textAlign: col === 'concept' ? 'left' : 'right',
                            fontSize: '6pt',
                            textTransform: 'uppercase',
                            color: '#6b7280',
                            fontWeight: 'bold',
                        }"
                    >
                        {{ colHeaders[col] || col }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(line, idx) in sampleData.lines" :key="idx">
                    <template v-for="col in (options.columns || ['concept','qty','price','discount','vat','amount'])" :key="col + '-' + idx">
                        <td v-if="col === 'concept'" style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 7.5pt;">
                            <div style="font-weight: 600;">{{ line.concept }}</div>
                            <div v-if="(options.show_description !== false) && line.description" style="color: #6b7280; font-size: 6.5pt;">{{ line.description }}</div>
                        </td>
                        <td v-else-if="col === 'qty'" style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 7.5pt; text-align: right;">
                            {{ fmt(line.quantity) }}
                        </td>
                        <td v-else-if="col === 'unit'" style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 7.5pt; text-align: right;">
                            {{ line.unit }}
                        </td>
                        <td v-else-if="col === 'price'" style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 7.5pt; text-align: right;">
                            {{ fmt(line.unit_price) }} €
                        </td>
                        <td v-else-if="col === 'discount'" style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 7.5pt; text-align: right;">
                            {{ line.discount_percent > 0 ? line.discount_percent + '%' : '-' }}
                        </td>
                        <td v-else-if="col === 'vat'" style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 7.5pt; text-align: right;">
                            {{ line.vat_rate }}%
                        </td>
                        <td v-else-if="col === 'amount'" style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 7.5pt; text-align: right;">
                            {{ fmt(line.line_total) }} €
                        </td>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>
</template>
