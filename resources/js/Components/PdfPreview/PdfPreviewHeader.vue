<script setup lang="ts">
defineProps<{
    options: Record<string, any>;
    global: Record<string, any>;
    sampleData: any;
}>();
</script>

<template>
    <div style="margin-bottom: 20px;">
        <table style="width: 100%;">
            <tr>
                <template v-if="options.logo_position === 'center'">
                    <td style="text-align: center;">
                        <div v-if="options.show_logo" style="display: inline-block; width: 120px; height: 40px; background: #e5e7eb; border-radius: 4px; margin-bottom: 6px; line-height: 40px; font-size: 7pt; color: #9ca3af;">LOGO</div>
                        <br>
                        <span :style="{ fontSize: '14pt', fontWeight: 'bold', color: global.primary_color || '#1f2937' }">
                            {{ sampleData.company.trade_name || sampleData.company.legal_name }}
                        </span>
                        <div v-if="options.show_company_info !== false" style="font-size: 7pt; color: #6b7280; margin-top: 4px; text-align: center;">
                            <strong>{{ sampleData.company.legal_name }}</strong><br>
                            NIF: {{ sampleData.company.nif }}<br>
                            {{ sampleData.company.address_street }}<br>
                            {{ sampleData.company.address_postal_code }} {{ sampleData.company.address_city }} ({{ sampleData.company.address_province }})<br>
                            Tel: {{ sampleData.company.phone }}<br>
                            {{ sampleData.company.email }}
                        </div>
                    </td>
                </template>
                <template v-else-if="options.logo_position === 'right'">
                    <td style="width: 50%;">
                        <span :style="{ fontSize: '14pt', fontWeight: 'bold', color: global.primary_color || '#1f2937' }">
                            {{ sampleData.company.trade_name || sampleData.company.legal_name }}
                        </span>
                        <div v-if="options.show_company_info !== false" style="font-size: 7pt; color: #6b7280; margin-top: 4px;">
                            <strong>{{ sampleData.company.legal_name }}</strong><br>
                            NIF: {{ sampleData.company.nif }}<br>
                            {{ sampleData.company.address_street }}<br>
                            {{ sampleData.company.address_postal_code }} {{ sampleData.company.address_city }} ({{ sampleData.company.address_province }})
                        </div>
                    </td>
                    <td style="width: 50%; text-align: right; vertical-align: top;">
                        <div v-if="options.show_logo" style="display: inline-block; width: 120px; height: 40px; background: #e5e7eb; border-radius: 4px; line-height: 40px; font-size: 7pt; color: #9ca3af; text-align: center;">LOGO</div>
                    </td>
                </template>
                <template v-else>
                    <!-- Default: left -->
                    <td style="width: 50%; vertical-align: top;">
                        <div v-if="options.show_logo" style="width: 120px; height: 40px; background: #e5e7eb; border-radius: 4px; margin-bottom: 6px; line-height: 40px; font-size: 7pt; color: #9ca3af; text-align: center;">LOGO</div>
                        <span :style="{ fontSize: '14pt', fontWeight: 'bold', color: global.primary_color || '#1f2937' }">
                            {{ sampleData.company.trade_name || sampleData.company.legal_name }}
                        </span>
                    </td>
                    <td v-if="options.show_company_info !== false" style="width: 50%; text-align: right; vertical-align: top;">
                        <div style="font-size: 7pt; color: #6b7280; line-height: 1.5;">
                            <strong>{{ sampleData.company.legal_name }}</strong><br>
                            NIF: {{ sampleData.company.nif }}<br>
                            {{ sampleData.company.address_street }}<br>
                            {{ sampleData.company.address_postal_code }} {{ sampleData.company.address_city }} ({{ sampleData.company.address_province }})<br>
                            Tel: {{ sampleData.company.phone }}<br>
                            {{ sampleData.company.email }}
                        </div>
                    </td>
                </template>
            </tr>
        </table>
    </div>
</template>
