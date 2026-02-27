import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { i18nVue } from 'laravel-vue-i18n';
import { initSurchargeMap } from '@/composables/useTaxCalculator';
import { router } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'Factu01';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const initialLocale = (props.initialPage.props.locale as string) || 'es';

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                },
            })
            .use(i18nVue, {
                lang: initialLocale,
                resolve: async (lang: string) => {
                    const langs = import.meta.glob('../../lang/*.json');
                    return await langs[`../../lang/php_${lang}.json`]();
                },
            })
            .mount(el);

        // Initialize surcharge map from shared vatRates prop
        const vatRates = props.initialPage.props.vatRates as { rate: number; surcharge_rate: number }[] | undefined;
        if (vatRates) initSurchargeMap(vatRates);

        // Re-initialize on navigation (vatRates may change)
        router.on('navigate', (event) => {
            const pageVatRates = (event.detail.page.props as any).vatRates as { rate: number; surcharge_rate: number }[] | undefined;
            if (pageVatRates) initSurchargeMap(pageVatRates);
        });
    },
    progress: {
        color: '#4f46e5',
    },
});
