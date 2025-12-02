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

interface ProductTemplate {
    id: number;
    name: string;
    category?: { name: string };
    unit?: { name: string };
    is_active: boolean;
}

defineProps<{
    templates: {
        data: ProductTemplate[];
        links: any[];
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
        title: 'Products',
        href: '/product-templates',
    },
];

const deleteTemplate = (id: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(`/product-templates/${id}`);
    }
};
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Products
                    </h2>
                    <Link href="/product-templates/create">
                        <Button>Create Product</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Unit</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="template in templates.data" :key="template.id">
                                <TableCell class="font-medium">{{ template.name }}</TableCell>
                                <TableCell>{{ template.category?.name || '-' }}</TableCell>
                                <TableCell>{{ template.unit?.name || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="template.is_active ? 'default' : 'secondary'">
                                        {{ template.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="`/product-templates/${template.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteTemplate(template.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="templates.data.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-gray-500">
                                    No products found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
