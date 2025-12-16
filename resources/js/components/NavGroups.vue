<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroupContent,
} from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

export interface NavGroup {
    title: string;
    items: NavItem[];
    defaultOpen?: boolean;
}

defineProps<{
    groups: NavGroup[];
}>();

const page = usePage();
</script>

<template>
    <template v-for="group in groups" :key="group.title">
        <Collapsible 
            :default-open="group.defaultOpen ?? true" 
            class="group/collapsible"
            as-child
        >
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel as-child>
                    <CollapsibleTrigger class="flex w-full items-center justify-between">
                        <span>{{ group.title }}</span>
                        <ChevronRight 
                            class="h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-90" 
                        />
                    </CollapsibleTrigger>
                </SidebarGroupLabel>
                <CollapsibleContent>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in group.items" :key="item.title">
                                <SidebarMenuButton
                                    as-child
                                    :is-active="urlIsActive(item.href, page.url)"
                                    :tooltip="item.title"
                                >
                                    <Link :href="item.href">
                                        <component :is="item.icon" />
                                        <span>{{ item.title }}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </CollapsibleContent>
            </SidebarGroup>
        </Collapsible>
    </template>
</template>
