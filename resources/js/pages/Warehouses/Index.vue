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
import { create, edit, destroy, index } from '@/routes/warehouses';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Warehouse {
    id: number;
    branch_id: number;
    branch_name?: string;
    code: string;
    name: string;
    is_active: boolean;
}

defineProps<{
    warehouses: {
        data: Warehouse[];
        meta: any;
        links: any;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Warehouses',
        href: index.url(),
    },
];

const deleteWarehouse = (id: number) => {
    if (confirm('Are you sure you want to delete this warehouse?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Warehouses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Warehouses
                    </h2>
                    <Link :href="create.url()">
                        <Button>Create Warehouse</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Branch</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="warehouse in warehouses.data" :key="warehouse.id">
                                <TableCell class="font-medium">{{ warehouse.code }}</TableCell>
                                <TableCell>{{ warehouse.name }}</TableCell>
                                <TableCell>{{ warehouse.branch_name || 'N/A' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="warehouse.is_active ? 'default' : 'secondary'">
                                        {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="edit.url(warehouse.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteWarehouse(warehouse.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="warehouses.data.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-gray-500">
                                    No warehouses found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2" v-if="warehouses.meta.last_page > 1">
                        <Link v-for="link in warehouses.meta.links" 
                              :key="link.label" 
                              :href="link.url || '#'" 
                              class="px-3 py-1 border rounded"
                              :class="{ 'bg-gray-200': link.active, 'opacity-50': !link.url }">
                            <span v-html="link.label"></span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
