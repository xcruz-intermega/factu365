import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const localeMap: Record<string, string> = {
    es: 'es-ES',
    en: 'en-GB',
    ca: 'ca-ES',
};

export function useFormatters() {
    const page = usePage();
    const intlLocale = computed(() => localeMap[page.props.locale as string] || 'es-ES');

    function formatCurrency(value: number | null | undefined): string {
        if (value == null) return '';
        return new Intl.NumberFormat(intlLocale.value, {
            style: 'currency',
            currency: 'EUR',
        }).format(value);
    }

    function formatDate(value: string | null | undefined): string {
        if (!value) return '';
        return new Intl.DateTimeFormat(intlLocale.value, {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).format(new Date(value));
    }

    function formatNumber(value: number | null | undefined, options?: Intl.NumberFormatOptions): string {
        if (value == null) return '';
        return new Intl.NumberFormat(intlLocale.value, options).format(value);
    }

    return { formatCurrency, formatDate, formatNumber, intlLocale };
}
