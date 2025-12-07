<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import companies from '@/routes/companies';
import warehouses from '@/routes/warehouses';
import units from '@/routes/units';
import partners from '@/routes/partners';
import purchaseOrders from '@/routes/purchase-orders';
import saleOrders from '@/routes/sale-orders';
import stockTransfers from '@/routes/stock-transfers';
import productCategories from '@/routes/product-categories';
import productAttributes from '@/routes/product-attributes';
import productTemplates from '@/routes/product-templates';
import recipes from '@/routes/recipes';
import combos from '@/routes/combos';
import paymentMethods from '@/routes/payment-methods';
import posTerminals from '@/routes/pos-terminals';
import cashRegisters from '@/routes/cash-registers';
import tableAreas from '@/routes/table-areas';
import tables from '@/routes/tables';
import reservations from '@/routes/reservations';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Tag, Package, Building2, Warehouse, Ruler, Users, ShoppingCart, TrendingUp, ArrowRightLeft, ChefHat, Gift, CreditCard, Monitor, Wallet, UtensilsCrossed, Calendar } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Companies',
        href: companies.index.url(),
        icon: Building2,
    },
    {
        title: 'Warehouses',
        href: warehouses.index.url(),
        icon: Warehouse,
    },
    {
        title: 'Units',
        href: units.index.url(),
        icon: Ruler,
    },
    {
        title: 'Partners',
        href: partners.index.url(),
        icon: Users,
    },
    {
        title: 'Purchase Orders',
        href: purchaseOrders.index.url(),
        icon: ShoppingCart,
    },
    {
        title: 'Sale Orders',
        href: saleOrders.index.url(),
        icon: TrendingUp,
    },
    {
        title: 'Stock Transfers',
        href: stockTransfers.index.url(),
        icon: ArrowRightLeft,
    },
    {
        title: 'Products',
        href: productTemplates.index.url(),
        icon: Package,
    },
    {
        title: 'Product Categories',
        href: productCategories.index.url(),
        icon: Tag,
    },
    {
        title: 'Product Attributes',
        href: productAttributes.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Recipes',
        href: recipes.index.url(),
        icon: ChefHat,
    },
    {
        title: 'Combos',
        href: combos.index.url(),
        icon: Gift,
    },
    {
        title: 'Payment Methods',
        href: paymentMethods.index.url(),
        icon: CreditCard,
    },
    {
        title: 'POS Terminals',
        href: posTerminals.index.url(),
        icon: Monitor,
    },
    {
        title: 'Cash Registers',
        href: cashRegisters.index.url(),
        icon: Wallet,
    },
    {
        title: 'Table Areas',
        href: tableAreas.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Tables',
        href: tables.index.url(),
        icon: UtensilsCrossed,
    },
    {
        title: 'Reservations',
        href: reservations.index.url(),
        icon: Calendar,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
