<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { create, edit, destroy } from '@/routes/product-categories';

defineProps<{
    categories: {
        data: Array<{
            id: number;
            name: string;
            full_name: string;
            parent?: { name: string };
            is_active: boolean;
        }>;
        meta: any;
        links: any;
    };
}>();

import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Product Categories',
        href: '/product-categories',
    },
];

const deleteCategory = (id: number) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Product Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Product Categories
                    </h2>
                    <Link :href="create.url()">
                        <Button>Create Category</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Parent Category</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="category in categories.data" :key="category.id">
                                <TableCell class="font-medium">{{ category.full_name }}</TableCell>
                                <TableCell>{{ category.parent?.name || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="category.is_active ? 'default' : 'secondary'">
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="edit.url(category.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteCategory(category.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    
                    <!-- Pagination (Simple implementation) -->
                    <div class="mt-4 flex justify-center space-x-2" v-if="categories.meta.last_page > 1">
                        <Link v-for="link in categories.meta.links" 
                              :key="link.label" 
                              :href="link.url || '#'" 
                              class="px-3 py-1 border rounded"
                              :class="{ 'bg-gray-200': link.active, 'opacity-50': !link.url }"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
