<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PdfPreviewHeader from '@/Components/PdfPreview/PdfPreviewHeader.vue';
import PdfPreviewTitleBar from '@/Components/PdfPreview/PdfPreviewTitleBar.vue';
import PdfPreviewClientInfo from '@/Components/PdfPreview/PdfPreviewClientInfo.vue';
import PdfPreviewLinesTable from '@/Components/PdfPreview/PdfPreviewLinesTable.vue';
import PdfPreviewVatBreakdown from '@/Components/PdfPreview/PdfPreviewVatBreakdown.vue';
import PdfPreviewTotals from '@/Components/PdfPreview/PdfPreviewTotals.vue';
import PdfPreviewNotes from '@/Components/PdfPreview/PdfPreviewNotes.vue';
import PdfPreviewFooterText from '@/Components/PdfPreview/PdfPreviewFooterText.vue';
import PdfPreviewQrVerifactu from '@/Components/PdfPreview/PdfPreviewQrVerifactu.vue';
import PdfPreviewLegalText from '@/Components/PdfPreview/PdfPreviewLegalText.vue';
import PdfPreviewPageFooter from '@/Components/PdfPreview/PdfPreviewPageFooter.vue';
import { sampleInvoice } from '@/data/sampleInvoice';

interface Block {
    type: string;
    enabled: boolean;
    options: Record<string, any>;
}

interface LayoutJson {
    blocks: Block[];
    global: {
        font_family: string;
        font_size: string;
        primary_color: string;
        accent_color: string;
    };
}

interface Template {
    id: number;
    name: string;
    layout_json: LayoutJson | null;
}

const props = defineProps<{
    template: Template | null;
    defaultLayout: LayoutJson;
}>();

const blockLabels = computed((): Record<string, string> => ({
    header: trans('settings.pdf_block_header'),
    title_bar: trans('settings.pdf_block_title_bar'),
    client_info: trans('settings.pdf_block_client_info'),
    lines_table: trans('settings.pdf_block_lines_table'),
    vat_breakdown: trans('settings.pdf_block_vat_breakdown'),
    totals: trans('settings.pdf_block_totals'),
    notes: trans('settings.pdf_block_notes'),
    footer_text: trans('settings.pdf_block_footer_text'),
    qr_verifactu: trans('settings.pdf_block_qr_verifactu'),
    legal_text: trans('settings.pdf_block_legal_text'),
    page_footer: trans('settings.pdf_block_page_footer'),
}));

const blockComponents: Record<string, any> = {
    header: PdfPreviewHeader,
    title_bar: PdfPreviewTitleBar,
    client_info: PdfPreviewClientInfo,
    lines_table: PdfPreviewLinesTable,
    vat_breakdown: PdfPreviewVatBreakdown,
    totals: PdfPreviewTotals,
    notes: PdfPreviewNotes,
    footer_text: PdfPreviewFooterText,
    qr_verifactu: PdfPreviewQrVerifactu,
    legal_text: PdfPreviewLegalText,
    page_footer: PdfPreviewPageFooter,
};

const name = ref(props.template?.name || '');
const layout = reactive<LayoutJson>(
    JSON.parse(JSON.stringify(props.template?.layout_json || props.defaultLayout))
);

const expandedBlock = ref<string | null>(null);
const saving = ref(false);
const previewLoading = ref(false);

const toggleExpand = (type: string) => {
    expandedBlock.value = expandedBlock.value === type ? null : type;
};

const enabledBlocks = computed(() => layout.blocks.filter(b => b.enabled));

const allColumns = ['concept', 'qty', 'price', 'discount', 'vat', 'amount', 'unit'];

const columnLabels = computed((): Record<string, string> => ({
    concept: trans('settings.pdf_col_concept'),
    qty: trans('settings.pdf_col_qty'),
    price: trans('settings.pdf_col_price'),
    discount: trans('settings.pdf_col_discount'),
    vat: trans('settings.pdf_col_vat'),
    amount: trans('settings.pdf_col_amount'),
    unit: trans('settings.pdf_col_unit'),
}));

const toggleColumn = (block: Block, col: string) => {
    const cols = block.options.columns || [];
    const idx = cols.indexOf(col);
    if (idx >= 0) {
        cols.splice(idx, 1);
    } else {
        cols.push(col);
    }
    block.options.columns = [...cols];
};

// Drag & drop
const dragIndex = ref<number | null>(null);
const dragOverIndex = ref<number | null>(null);

const onDragStart = (idx: number) => {
    dragIndex.value = idx;
};

const onDragOver = (e: DragEvent, idx: number) => {
    e.preventDefault();
    dragOverIndex.value = idx;
};

const onDrop = (idx: number) => {
    if (dragIndex.value !== null && dragIndex.value !== idx) {
        const item = layout.blocks.splice(dragIndex.value, 1)[0];
        layout.blocks.splice(idx, 0, item);
    }
    dragIndex.value = null;
    dragOverIndex.value = null;
};

const onDragEnd = () => {
    dragIndex.value = null;
    dragOverIndex.value = null;
};

const save = () => {
    saving.value = true;
    const data = {
        name: name.value,
        layout_json: {
            blocks: layout.blocks,
            global: layout.global,
        },
    };

    if (props.template?.id) {
        router.put(route('settings.pdf-templates.update', props.template.id), data, {
            onFinish: () => { saving.value = false; },
        });
    } else {
        router.post(route('settings.pdf-templates.store'), data, {
            onFinish: () => { saving.value = false; },
        });
    }
};

const previewPdf = () => {
    previewLoading.value = true;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('settings.pdf-templates.preview');
    form.target = '_blank';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const jsonInput = document.createElement('input');
    jsonInput.type = 'hidden';
    jsonInput.name = 'layout_json';
    jsonInput.value = JSON.stringify({ blocks: layout.blocks, global: layout.global });
    form.appendChild(jsonInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    setTimeout(() => { previewLoading.value = false; }, 1000);
};

const hasOptions = (type: string): boolean => {
    return ['header', 'title_bar', 'client_info', 'lines_table', 'totals', 'qr_verifactu', 'page_footer'].includes(type);
};

const pageTitle = computed(() =>
    props.template ? trans('settings.pdf_edit') : trans('settings.pdf_create')
);
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900">{{ pageTitle }}</h1>
                <div class="flex gap-2">
                    <button
                        @click="previewPdf"
                        :disabled="previewLoading"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        {{ $t('settings.pdf_preview_pdf') }}
                    </button>
                    <a
                        :href="route('settings.pdf-templates')"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        {{ $t('common.cancel') }}
                    </a>
                    <button
                        @click="save"
                        :disabled="saving || !name.trim()"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{ saving ? $t('common.saving') : $t('common.save') }}
                    </button>
                </div>
            </div>
        </template>

        <div class="flex gap-6" style="min-height: calc(100vh - 180px);">
            <!-- Left: Configuration Panel -->
            <div class="w-[420px] shrink-0 space-y-4 overflow-y-auto">
                <!-- Template name -->
                <div class="rounded-lg border bg-white p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('settings.pdf_template_name') }}</label>
                    <input
                        v-model="name"
                        type="text"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :placeholder="$t('settings.pdf_template_name_placeholder')"
                    />
                </div>

                <!-- Global settings -->
                <div class="rounded-lg border bg-white p-4">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('settings.pdf_global_settings') }}</h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('settings.pdf_font') }}</label>
                            <select v-model="layout.global.font_family" class="w-full rounded-md border-gray-300 text-sm">
                                <option value="DejaVu Sans">DejaVu Sans</option>
                                <option value="Helvetica">Helvetica</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Courier">Courier</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('settings.pdf_primary_color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" v-model="layout.global.primary_color" class="h-8 w-8 shrink-0 cursor-pointer appearance-none rounded border border-gray-300 p-0" />
                                    <input type="text" v-model="layout.global.primary_color" class="flex-1 rounded-md border-gray-300 text-xs" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('settings.pdf_accent_color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" v-model="layout.global.accent_color" class="h-8 w-8 shrink-0 cursor-pointer appearance-none rounded border border-gray-300 p-0" />
                                    <input type="text" v-model="layout.global.accent_color" class="flex-1 rounded-md border-gray-300 text-xs" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Blocks -->
                <div class="rounded-lg border bg-white p-4">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ $t('settings.pdf_blocks') }}</h3>
                    <div class="space-y-1">
                        <div
                            v-for="(block, idx) in layout.blocks"
                            :key="block.type"
                            class="rounded-md border"
                            :class="[
                                dragOverIndex === idx ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200',
                                !block.enabled ? 'opacity-60' : '',
                            ]"
                            draggable="true"
                            @dragstart="onDragStart(idx)"
                            @dragover="onDragOver($event, idx)"
                            @drop="onDrop(idx)"
                            @dragend="onDragEnd"
                        >
                            <!-- Block header row -->
                            <div class="flex items-center gap-2 px-3 py-2">
                                <span class="cursor-grab text-gray-400 hover:text-gray-600" title="Drag">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                                </span>

                                <label class="flex flex-1 items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" v-model="block.enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <span :class="block.enabled ? 'text-gray-900 font-medium' : 'text-gray-500'">{{ blockLabels[block.type] || block.type }}</span>
                                </label>

                                <button
                                    v-if="block.enabled && hasOptions(block.type)"
                                    @click="toggleExpand(block.type)"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <svg class="h-4 w-4 transition-transform" :class="expandedBlock === block.type ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </div>

                            <!-- Block options (expanded) -->
                            <div v-if="expandedBlock === block.type && block.enabled" class="border-t border-gray-100 px-3 py-3 bg-gray-50 space-y-2">
                                <!-- Header options -->
                                <template v-if="block.type === 'header'">
                                    <label class="flex items-center gap-2 text-xs">
                                        <input type="checkbox" v-model="block.options.show_logo" class="rounded border-gray-300 text-indigo-600" />
                                        {{ $t('settings.pdf_opt_show_logo') }}
                                    </label>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_logo_position') }}</label>
                                        <select v-model="block.options.logo_position" class="w-full rounded-md border-gray-300 text-xs">
                                            <option value="left">{{ $t('settings.pdf_opt_left') }}</option>
                                            <option value="center">{{ $t('settings.pdf_opt_center') }}</option>
                                            <option value="right">{{ $t('settings.pdf_opt_right') }}</option>
                                        </select>
                                    </div>
                                    <label class="flex items-center gap-2 text-xs">
                                        <input type="checkbox" v-model="block.options.show_company_info" class="rounded border-gray-300 text-indigo-600" />
                                        {{ $t('settings.pdf_opt_show_company_info') }}
                                    </label>
                                </template>

                                <!-- Title bar options -->
                                <template v-if="block.type === 'title_bar'">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_bg_color') }}</label>
                                            <div class="flex items-center gap-1">
                                                <input type="color" v-model="block.options.background_color" class="h-6 w-6 shrink-0 cursor-pointer appearance-none rounded border border-gray-300 p-0" />
                                                <input type="text" v-model="block.options.background_color" class="flex-1 rounded-md border-gray-300 text-xs" :placeholder="layout.global.accent_color" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_text_color') }}</label>
                                            <div class="flex items-center gap-1">
                                                <input type="color" v-model="block.options.text_color" class="h-6 w-6 shrink-0 cursor-pointer appearance-none rounded border border-gray-300 p-0" />
                                                <input type="text" v-model="block.options.text_color" class="flex-1 rounded-md border-gray-300 text-xs" placeholder="#ffffff" />
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Client info options -->
                                <template v-if="block.type === 'client_info'">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_layout') }}</label>
                                        <select v-model="block.options.layout" class="w-full rounded-md border-gray-300 text-xs">
                                            <option value="side-by-side">{{ $t('settings.pdf_opt_side_by_side') }}</option>
                                            <option value="stacked">{{ $t('settings.pdf_opt_stacked') }}</option>
                                        </select>
                                    </div>
                                </template>

                                <!-- Lines table options -->
                                <template v-if="block.type === 'lines_table'">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_columns') }}</label>
                                        <div class="flex flex-wrap gap-2">
                                            <label v-for="col in allColumns" :key="col" class="flex items-center gap-1 text-xs">
                                                <input
                                                    type="checkbox"
                                                    :checked="(block.options.columns || []).includes(col)"
                                                    @change="toggleColumn(block, col)"
                                                    class="rounded border-gray-300 text-indigo-600"
                                                />
                                                {{ columnLabels[col] || col }}
                                            </label>
                                        </div>
                                    </div>
                                    <label class="flex items-center gap-2 text-xs">
                                        <input type="checkbox" v-model="block.options.show_description" class="rounded border-gray-300 text-indigo-600" />
                                        {{ $t('settings.pdf_opt_show_description') }}
                                    </label>
                                </template>

                                <!-- Totals options -->
                                <template v-if="block.type === 'totals'">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_highlight_color') }}</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" v-model="block.options.highlight_color" class="h-6 w-6 shrink-0 cursor-pointer appearance-none rounded border border-gray-300 p-0" />
                                            <input type="text" v-model="block.options.highlight_color" class="flex-1 rounded-md border-gray-300 text-xs" :placeholder="layout.global.accent_color" />
                                        </div>
                                    </div>
                                </template>

                                <!-- QR options -->
                                <template v-if="block.type === 'qr_verifactu'">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_qr_position') }}</label>
                                        <select v-model="block.options.position" class="w-full rounded-md border-gray-300 text-xs">
                                            <option value="left">{{ $t('settings.pdf_opt_left') }}</option>
                                            <option value="right">{{ $t('settings.pdf_opt_right') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_qr_size') }}</label>
                                        <select v-model.number="block.options.size" class="w-full rounded-md border-gray-300 text-xs">
                                            <option :value="60">60px</option>
                                            <option :value="80">80px</option>
                                            <option :value="100">100px</option>
                                        </select>
                                    </div>
                                    <label class="flex items-center gap-2 text-xs">
                                        <input type="checkbox" v-model="block.options.show_legal_text" class="rounded border-gray-300 text-indigo-600" />
                                        {{ $t('settings.pdf_opt_show_legal_text') }}
                                    </label>
                                </template>

                                <!-- Page footer options -->
                                <template v-if="block.type === 'page_footer'">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ $t('settings.pdf_opt_footer_content') }}</label>
                                        <select v-model="block.options.content" class="w-full rounded-md border-gray-300 text-xs">
                                            <option value="company">{{ $t('settings.pdf_opt_company_data') }}</option>
                                            <option value="custom">{{ $t('settings.pdf_opt_custom_text') }}</option>
                                        </select>
                                    </div>
                                    <div v-if="block.options.content === 'custom'">
                                        <input type="text" v-model="block.options.custom_text" class="w-full rounded-md border-gray-300 text-xs" placeholder="Texto personalizado..." />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Preview Panel -->
            <div class="flex-1 overflow-hidden rounded-lg border bg-gray-100 p-6">
                <div class="mx-auto" style="width: 595px; transform-origin: top center;">
                    <div
                        class="bg-white shadow-lg"
                        :style="{
                            width: '595px',
                            minHeight: '842px',
                            padding: '30px 40px',
                            fontFamily: layout.global.font_family + ', sans-serif',
                            fontSize: layout.global.font_size || '9pt',
                            color: '#1f2937',
                            lineHeight: '1.4',
                            position: 'relative',
                        }"
                    >
                        <template v-for="block in enabledBlocks" :key="block.type">
                            <component
                                :is="blockComponents[block.type]"
                                :options="block.options"
                                :global="layout.global"
                                :sampleData="sampleInvoice"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}
input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 4px;
}
input[type="color"]::-moz-color-swatch {
    border: none;
    border-radius: 4px;
}
</style>
