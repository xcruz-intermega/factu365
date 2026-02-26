<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <div style="margin-bottom: 16px;">
        <template v-if="options.layout === 'stacked'">
            <div style="border: 1px solid #e5e7eb; padding: 8px; border-radius: 4px; margin-bottom: 8px;">
                <div style="font-size: 6pt; text-transform: uppercase; color: #9ca3af; font-weight: bold; margin-bottom: 4px;">Datos del cliente</div>
                <p style="font-weight: bold; font-size: 9pt;">{{ sampleData.document.client.legal_name }}</p>
                <p style="font-size: 7pt;">NIF: {{ sampleData.document.client.nif }}</p>
                <p style="font-size: 7pt;">{{ sampleData.document.client.address_street }}</p>
                <p style="font-size: 7pt;">{{ sampleData.document.client.address_postal_code }} {{ sampleData.document.client.address_city }} ({{ sampleData.document.client.address_province }})</p>
            </div>
            <div style="border: 1px solid #e5e7eb; padding: 8px; border-radius: 4px;">
                <div style="font-size: 6pt; text-transform: uppercase; color: #9ca3af; font-weight: bold; margin-bottom: 4px;">Datos del documento</div>
                <p style="font-size: 7pt;"><strong>Fecha emisión:</strong> {{ formatDate(sampleData.document.issue_date) }}</p>
                <p style="font-size: 7pt;"><strong>Fecha vencimiento:</strong> {{ formatDate(sampleData.document.due_date) }}</p>
            </div>
        </template>
        <template v-else>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 55%; padding-right: 8px; vertical-align: top;">
                        <div style="border: 1px solid #e5e7eb; padding: 8px; border-radius: 4px;">
                            <div style="font-size: 6pt; text-transform: uppercase; color: #9ca3af; font-weight: bold; margin-bottom: 4px;">Datos del cliente</div>
                            <p style="font-weight: bold; font-size: 9pt;">{{ sampleData.document.client.legal_name }}</p>
                            <p style="font-size: 7pt;">NIF: {{ sampleData.document.client.nif }}</p>
                            <p style="font-size: 7pt;">{{ sampleData.document.client.address_street }}</p>
                            <p style="font-size: 7pt;">{{ sampleData.document.client.address_postal_code }} {{ sampleData.document.client.address_city }} ({{ sampleData.document.client.address_province }})</p>
                        </div>
                    </td>
                    <td style="width: 45%; vertical-align: top;">
                        <div style="border: 1px solid #e5e7eb; padding: 8px; border-radius: 4px;">
                            <div style="font-size: 6pt; text-transform: uppercase; color: #9ca3af; font-weight: bold; margin-bottom: 4px;">Datos del documento</div>
                            <p style="font-size: 7pt;"><strong>Fecha emisión:</strong> {{ formatDate(sampleData.document.issue_date) }}</p>
                            <p style="font-size: 7pt;"><strong>Fecha vencimiento:</strong> {{ formatDate(sampleData.document.due_date) }}</p>
                            <p style="font-size: 7pt;"><strong>Fecha operación:</strong> {{ formatDate(sampleData.document.operation_date) }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </template>
    </div>
</template>
