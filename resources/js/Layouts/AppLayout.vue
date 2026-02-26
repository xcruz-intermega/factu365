<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import FlashMessage from '@/Components/FlashMessage.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const sidebarOpen = ref(false);
const collapsed = ref(false);
const collapsedSections = ref(new Set<number>());

const toggleSection = (idx: number) => {
    const next = new Set(collapsedSections.value);
    if (next.has(idx)) {
        next.delete(idx);
    } else {
        next.add(idx);
    }
    collapsedSections.value = next;
};

onMounted(() => {
    const stored = localStorage.getItem('sidebar-collapsed');
    if (stored === 'true') collapsed.value = true;
});

const toggleCollapsed = () => {
    collapsed.value = !collapsed.value;
    localStorage.setItem('sidebar-collapsed', String(collapsed.value));
};

const page = usePage();
const user = page.props.auth?.user;
const can = (page.props.auth as any)?.can as { manage_settings?: boolean; manage_users?: boolean; create_edit?: boolean } | undefined;
const overdueCount = computed(() => (page.props.overdue_count as number) || 0);
const appVersion = page.props.app_version as string;

interface NavItem {
    name: string;
    href: string;
    routeParams?: Record<string, string>;
    queryParams?: Record<string, string>;
    routeMatch: string;
    icon: string;
    badge?: () => number;
    activeUrls?: string[];
}

interface NavSection {
    title?: string;
    items: NavItem[];
    hidden?: boolean;
}

const sections = computed<NavSection[]>(() => [
    {
        items: [
            { name: trans('nav.dashboard'), href: 'dashboard', routeMatch: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        ],
    },
    {
        title: trans('nav.sales'),
        items: [
            { name: trans('nav.invoices'), href: 'documents.index', routeParams: { type: 'invoice' }, routeMatch: 'documents.*', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', badge: () => overdueCount.value },
            { name: trans('nav.quotes'), href: 'documents.index', routeParams: { type: 'quote' }, routeMatch: 'documents.*', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
            { name: trans('nav.delivery_notes'), href: 'documents.index', routeParams: { type: 'delivery_note' }, routeMatch: 'documents.*', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
            { name: trans('nav.rectificatives'), href: 'documents.index', routeParams: { type: 'rectificative' }, routeMatch: 'documents.*', icon: 'M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z' },
        ],
    },
    {
        title: trans('nav.purchases'),
        items: [
            { name: trans('nav.purchase_invoices'), href: 'documents.index', routeParams: { type: 'purchase_invoice' }, routeMatch: 'documents.*', icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2' },
            { name: trans('nav.expenses'), href: 'expenses.index', routeMatch: 'expenses.*', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
            { name: trans('nav.expense_categories'), href: 'expense-categories.index', routeMatch: 'expense-categories.*', icon: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z' },
        ],
    },
    {
        title: trans('nav.reports'),
        items: [
            { name: trans('nav.sales_by_client'), href: 'reports.sales.by-client', routeMatch: 'reports.sales.by-client', icon: 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { name: trans('nav.sales_by_product'), href: 'reports.sales.by-product', routeMatch: 'reports.sales.by-product', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
            { name: trans('nav.sales_by_period'), href: 'reports.sales.by-period', routeMatch: 'reports.sales.by-period', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { name: trans('nav.modelo_303'), href: 'reports.fiscal.modelo-303', routeMatch: 'reports.fiscal.modelo-303', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
            { name: trans('nav.modelo_130'), href: 'reports.fiscal.modelo-130', routeMatch: 'reports.fiscal.modelo-130', icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z' },
            { name: trans('nav.modelo_390'), href: 'reports.fiscal.modelo-390', routeMatch: 'reports.fiscal.modelo-390', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z' },
        ],
    },
    {
        title: trans('nav.management'),
        items: [
            { name: trans('nav.clients'), href: 'clients.index', queryParams: { type: 'customer' }, routeMatch: 'clients.*', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
            { name: trans('nav.suppliers'), href: 'clients.index', queryParams: { type: 'supplier' }, routeMatch: 'clients.*', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
            { name: trans('nav.products'), href: 'products.index', routeMatch: 'products.*', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
            { name: trans('nav.product_families'), href: 'product-families.index', routeMatch: 'product-families.*', icon: 'M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-8.25zM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-2.25z' },
        ],
    },
    {
        title: trans('nav.billing'),
        hidden: !can?.manage_settings,
        items: [
            { name: trans('nav.series'), href: 'settings.series', routeMatch: 'settings.series*', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { name: trans('nav.payment_templates'), href: 'settings.payment-templates', routeMatch: 'settings.payment-templates*', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { name: trans('nav.pdf_templates'), href: 'settings.pdf-templates', routeMatch: 'settings.pdf-templates*', icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' },
        ],
    },
    {
        title: trans('nav.company_settings'),
        hidden: !can?.manage_settings,
        items: [
            { name: trans('nav.company'), href: 'settings.company', routeMatch: 'settings.company', icon: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21' },
            { name: trans('nav.certificates'), href: 'settings.certificates', routeMatch: 'settings.certificates*', icon: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z' },
            { name: trans('nav.users'), href: 'settings.users', routeMatch: 'settings.users*', icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
            { name: trans('nav.backups'), href: 'settings.backups', routeMatch: 'settings.backups*', icon: 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125' },
            { name: trans('nav.audit_logs'), href: 'settings.audit-logs', routeMatch: 'settings.audit-logs*', icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' },
        ],
    },
]);

function isActive(item: NavItem): boolean {
    if (item.activeUrls) {
        return item.activeUrls.some(url => page.url.includes(url));
    }
    if (item.routeParams?.type) {
        return page.url.includes(`/documents/${item.routeParams.type}`);
    }
    if (item.queryParams) {
        if (!route().current(item.routeMatch)) return false;
        return Object.entries(item.queryParams).every(
            ([k, v]) => page.url.includes(`${k}=${v}`)
        );
    }
    return route().current(item.routeMatch) ?? false;
}

function itemHref(item: NavItem): string {
    const base = item.routeParams ? route(item.href, item.routeParams) : route(item.href);
    if (item.queryParams) {
        const qs = new URLSearchParams(item.queryParams).toString();
        return `${base}?${qs}`;
    }
    return base;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <FlashMessage />

        <!-- Mobile sidebar overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-gray-600/75 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <div
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                collapsed ? 'lg:w-20' : 'lg:w-64',
                'fixed inset-y-0 z-50 flex w-64 flex-col transition-all duration-300 ease-in-out lg:translate-x-0',
            ]"
        >
            <div class="flex grow flex-col overflow-y-auto border-r border-gray-200 bg-white">
                <!-- Logo bar (blue) -->
                <div class="flex h-16 shrink-0 items-center gap-2 bg-indigo-600" :class="collapsed ? 'justify-center px-3' : 'px-6'">
                    <img src="/images/logo.svg" alt="Factu365" class="h-10 w-10 object-contain" />
                    <span v-if="!collapsed" class="font-brand text-[30px] font-extrabold text-white">Factu365</span>
                    <!-- Mobile close button -->
                    <button
                        type="button"
                        class="ml-auto -mr-2 p-1.5 text-white/80 hover:text-white lg:hidden"
                        @click="sidebarOpen = false"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex flex-1 flex-col gap-y-1 pb-4" :class="collapsed ? 'px-3' : 'px-4'" style="padding-top: 0.75rem;">
                    <div class="flex flex-1 flex-col gap-y-1">
                        <div v-for="(section, sIdx) in sections.filter(s => !s.hidden)" :key="sIdx">
                            <!-- Section header (collapsable) -->
                            <button
                                v-if="section.title && !collapsed"
                                @click="toggleSection(sIdx)"
                                class="mt-3 mb-1 flex w-full items-center gap-x-1 px-2 text-xs font-semibold tracking-wider text-gray-900 hover:text-black"
                            >
                                <svg
                                    class="h-3.5 w-3.5 shrink-0 transition-transform duration-200"
                                    :class="collapsedSections.has(sIdx) ? '-rotate-90' : ''"
                                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                                {{ section.title }}
                            </button>
                            <div v-else-if="section.title && collapsed" class="mt-2 mb-1 border-t border-gray-200"></div>

                            <!-- Section items -->
                            <ul v-show="!section.title || !collapsedSections.has(sIdx)" class="flex flex-col gap-y-0.5">
                                <li v-for="item in section.items" :key="item.name + (item.routeParams?.type || '') + (item.queryParams?.type || '')">
                                    <Link
                                        :href="itemHref(item)"
                                        class="group relative flex items-center gap-x-3 rounded-md p-2 text-sm font-medium leading-6 text-gray-700 hover:bg-gray-50 hover:text-gray-900"
                                        :class="[
                                            isActive(item) ? 'bg-gray-100 text-gray-900 font-semibold' : '',
                                            collapsed ? 'justify-center' : 'pl-8',
                                            !section.title ? 'pl-2' : '',
                                        ]"
                                        :title="collapsed ? item.name : undefined"
                                    >
                                        <svg
                                            class="h-6 w-6 shrink-0"
                                            :class="isActive(item) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500'"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                                        </svg>
                                        <span v-if="!collapsed">{{ item.name }}</span>
                                        <!-- Badge (overdue count) -->
                                        <span
                                            v-if="item.badge && item.badge() > 0"
                                            class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white"
                                            :class="collapsed ? 'absolute -right-1 -top-1' : 'ml-auto'"
                                        >
                                            {{ item.badge() }}
                                        </span>
                                        <!-- Collapsed tooltip -->
                                        <span
                                            v-if="collapsed"
                                            class="pointer-events-none absolute left-full z-50 ml-3 hidden whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-xs text-white group-hover:block"
                                        >
                                            {{ item.name }}
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Collapse toggle (desktop only) -->
                    <div class="hidden lg:block">
                        <button
                            @click="toggleCollapsed"
                            class="flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                            :class="collapsed ? 'justify-center' : ''"
                        >
                            <svg class="h-6 w-6 shrink-0 transition-transform" :class="collapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                            </svg>
                            <span v-if="!collapsed">{{ $t('common.collapse') }}</span>
                        </button>
                    </div>

                    <!-- User info at bottom -->
                    <div v-if="user" class="mt-auto border-t border-gray-200 pt-4">
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-gray-900"
                            :class="collapsed ? 'justify-center' : ''"
                        >
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div v-if="!collapsed" class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ user.name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ user.email }}</p>
                            </div>
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="mt-1 flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                            :class="collapsed ? 'justify-center' : ''"
                        >
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span v-if="!collapsed">{{ $t('common.logout') }}</span>
                        </Link>
                    </div>

                    <!-- Version -->
                    <p class="mt-2 text-center text-xs text-gray-400/60">v{{ appVersion }}</p>
                </nav>
            </div>
        </div>

        <!-- Main content -->
        <div :class="collapsed ? 'lg:pl-20' : 'lg:pl-64'" class="transition-all duration-300">
            <!-- Top bar -->
            <div class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button
                    type="button"
                    class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <div class="flex flex-1 items-center gap-x-4 self-stretch lg:gap-x-6">
                    <slot name="header">
                        <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
                    </slot>
                    <div class="ml-auto flex items-center gap-3">
                        <GlobalSearch />
                        <LanguageSwitcher />
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="py-6">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
