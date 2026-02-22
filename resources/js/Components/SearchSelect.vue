<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { trans } from 'laravel-vue-i18n';

export interface SearchSelectOption {
    value: number | string | null;
    label: string;
    sublabel?: string;
}

const props = withDefaults(defineProps<{
    modelValue: number | string | null;
    options: SearchSelectOption[];
    placeholder?: string;
    hasError?: boolean;
}>(), {
    placeholder: '',
    hasError: false,
});

const resolvedPlaceholder = computed(() => props.placeholder || trans('common.select'));

const emit = defineEmits<{
    'update:modelValue': [value: number | string | null];
}>();

const isOpen = ref(false);
const search = ref('');
const highlightedIndex = ref(-1);
const inputRef = ref<HTMLInputElement | null>(null);
const dropdownRef = ref<HTMLDivElement | null>(null);
const containerRef = ref<HTMLDivElement | null>(null);

const selectedOption = computed(() => {
    if (props.modelValue == null) return null;
    return props.options.find(o => o.value === props.modelValue) ?? null;
});

const filteredOptions = computed(() => {
    if (!search.value) return props.options;
    const q = search.value.toLowerCase();
    return props.options.filter(o =>
        o.label.toLowerCase().includes(q) ||
        (o.sublabel && o.sublabel.toLowerCase().includes(q))
    );
});

const displayValue = computed(() => {
    if (isOpen.value) return search.value;
    if (selectedOption.value) return selectedOption.value.label;
    return '';
});

function open() {
    if (isOpen.value) return;
    isOpen.value = true;
    search.value = '';
    highlightedIndex.value = -1;
    nextTick(() => inputRef.value?.focus());
}

function close() {
    if (!isOpen.value) return;
    isOpen.value = false;
    search.value = '';
    highlightedIndex.value = -1;
}

function select(option: SearchSelectOption) {
    emit('update:modelValue', option.value);
    close();
}

function clear() {
    emit('update:modelValue', null);
    close();
}

function onInputFocus() {
    open();
}

function onInput(e: Event) {
    search.value = (e.target as HTMLInputElement).value;
    highlightedIndex.value = 0;
    if (!isOpen.value) isOpen.value = true;
}

function onKeydown(e: KeyboardEvent) {
    if (!isOpen.value) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter') {
            open();
            e.preventDefault();
        }
        return;
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            highlightedIndex.value = Math.min(
                highlightedIndex.value + 1,
                filteredOptions.value.length - 1
            );
            scrollToHighlighted();
            break;
        case 'ArrowUp':
            e.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
            scrollToHighlighted();
            break;
        case 'Enter':
            e.preventDefault();
            if (highlightedIndex.value >= 0 && highlightedIndex.value < filteredOptions.value.length) {
                select(filteredOptions.value[highlightedIndex.value]);
            }
            break;
        case 'Escape':
            e.preventDefault();
            close();
            break;
        case 'Tab':
            close();
            break;
    }
}

function scrollToHighlighted() {
    nextTick(() => {
        const el = dropdownRef.value?.querySelector('[data-highlighted="true"]');
        el?.scrollIntoView({ block: 'nearest' });
    });
}

function onClickOutside(e: MouseEvent) {
    if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
        close();
    }
}

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside);
});
</script>

<template>
    <div ref="containerRef" class="relative">
        <div class="relative">
            <input
                ref="inputRef"
                type="text"
                :value="displayValue"
                :placeholder="!selectedOption ? resolvedPlaceholder : ''"
                @focus="onInputFocus"
                @input="onInput"
                @keydown="onKeydown"
                class="block w-full rounded-md border-gray-300 pr-16 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :class="[
                    hasError ? 'border-red-500' : 'border-gray-300',
                    !isOpen && selectedOption ? 'text-gray-900' : '',
                ]"
                autocomplete="off"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-0.5">
                <!-- Clear button -->
                <button
                    v-if="selectedOption && !isOpen"
                    type="button"
                    @mousedown.prevent="clear"
                    class="rounded p-0.5 text-gray-400 hover:text-gray-600"
                    :title="$t('common.clear_selection')"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <!-- Dropdown arrow -->
                <button
                    type="button"
                    @mousedown.prevent="isOpen ? close() : open()"
                    class="rounded p-0.5 text-gray-400 hover:text-gray-600"
                    tabindex="-1"
                >
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Dropdown -->
        <div
            v-if="isOpen"
            ref="dropdownRef"
            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg"
        >
            <div
                v-if="filteredOptions.length === 0"
                class="px-3 py-2 text-sm text-gray-500"
            >
                {{ $t('common.no_results') }}
            </div>
            <div
                v-for="(option, idx) in filteredOptions"
                :key="option.value ?? 'null'"
                :data-highlighted="idx === highlightedIndex"
                @mousedown.prevent="select(option)"
                @mouseenter="highlightedIndex = idx"
                class="cursor-pointer px-3 py-2 text-sm"
                :class="{
                    'bg-indigo-600 text-white': idx === highlightedIndex,
                    'text-gray-900': idx !== highlightedIndex,
                    'font-medium': option.value === modelValue,
                }"
            >
                <div>{{ option.label }}</div>
                <div
                    v-if="option.sublabel"
                    class="text-xs"
                    :class="idx === highlightedIndex ? 'text-indigo-200' : 'text-gray-500'"
                >
                    {{ option.sublabel }}
                </div>
            </div>
        </div>
    </div>
</template>
