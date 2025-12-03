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
import productCategories from '@/routes/product-categories';
import productAttributes from '@/routes/product-attributes';
import productTemplates from '@/routes/product-templates';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Tag, Package, Building2, Warehouse, Ruler, Users } from 'lucide-vue-next';
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
        icon: Tag,
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
