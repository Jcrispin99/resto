<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavGroups from '@/components/NavGroups.vue';
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
import productAttributes from '@/routes/product-attributes';
import recipes from '@/routes/recipes';
import combos from '@/routes/combos';
import paymentMethods from '@/routes/payment-methods';
import posTerminals from '@/routes/pos-terminals';
import cashRegisters from '@/routes/cash-registers';
import tableAreas from '@/routes/table-areas';
import tables from '@/routes/tables';
import reservations from '@/routes/reservations';
import { type NavItem } from '@/types';
import { type NavGroup } from '@/components/NavGroups.vue';
import { Link } from '@inertiajs/vue3';
import { 
    BookOpen, Folder, LayoutGrid, Tag, Package, Building2, Warehouse, 
    Ruler, Users, ShoppingCart, TrendingUp, ArrowRightLeft, ChefHat, 
    Gift, CreditCard, Monitor, Wallet, UtensilsCrossed, Calendar,
    Boxes
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Dashboard - siempre visible
const dashboardItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

// Grupos del sidebar
const navGroups: NavGroup[] = [
    {
        title: '📦 Inventario',
        defaultOpen: true,
        items: [
            {
                title: 'Productos',
                href: '/inventory/products',
                icon: Package,
            },
            {
                title: 'Categorías',
                href: '/inventory/categories',
                icon: Tag,
            },
            {
                title: 'Almacenes',
                href: warehouses.index.url(),
                icon: Warehouse,
            },
            {
                title: 'Unidades',
                href: units.index.url(),
                icon: Ruler,
            },
            {
                title: 'Transferencias',
                href: stockTransfers.index.url(),
                icon: ArrowRightLeft,
            },
        ],
    },
    {
        title: '🛒 Compras',
        defaultOpen: true,
        items: [
            {
                title: 'Proveedores',
                href: '/partners?filter=suppliers',
                icon: Users,
            },
            {
                title: 'Órdenes de Compra',
                href: purchaseOrders.index.url(),
                icon: ShoppingCart,
            },
        ],
    },
    {
        title: '🍽️ Menú',
        defaultOpen: true,
        items: [
            {
                title: 'Platos',
                href: '/menu/dishes',
                icon: UtensilsCrossed,
            },
            {
                title: 'Categorías',
                href: '/menu/categories',
                icon: Tag,
            },
            {
                title: 'Recetas',
                href: recipes.index.url(),
                icon: ChefHat,
            },
            {
                title: 'Combos',
                href: combos.index.url(),
                icon: Gift,
            },
            {
                title: 'Atributos',
                href: productAttributes.index.url(),
                icon: Boxes,
            },
        ],
    },
    {
        title: '💰 Ventas',
        defaultOpen: true,
        items: [
            {
                title: 'Clientes',
                href: '/partners?filter=customers',
                icon: Users,
            },
            {
                title: 'Órdenes de Venta',
                href: saleOrders.index.url(),
                icon: TrendingUp,
            },
        ],
    },
    {
        title: '👥 Partners',
        defaultOpen: false,
        items: [
            {
                title: 'Clientes/Proveedores',
                href: partners.index.url(),
                icon: Users,
            },
        ],
    },
    {
        title: '🏪 POS Config',
        defaultOpen: false,
        items: [
            {
                title: 'Áreas',
                href: tableAreas.index.url(),
                icon: LayoutGrid,
            },
            {
                title: 'Mesas',
                href: tables.index.url(),
                icon: UtensilsCrossed,
            },
            {
                title: 'Reservas',
                href: reservations.index.url(),
                icon: Calendar,
            },
            {
                title: 'Terminales',
                href: posTerminals.index.url(),
                icon: Monitor,
            },
            {
                title: 'Cajas',
                href: cashRegisters.index.url(),
                icon: Wallet,
            },
            {
                title: 'Métodos de Pago',
                href: paymentMethods.index.url(),
                icon: CreditCard,
            },
        ],
    },
    {
        title: '⚙️ Configuración',
        defaultOpen: false,
        items: [
            {
                title: 'Empresas',
                href: companies.index.url(),
                icon: Building2,
            },
        ],
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
            <NavMain :items="dashboardItems" />
            <NavGroups :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
