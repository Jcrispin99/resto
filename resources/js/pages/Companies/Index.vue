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
import { create, edit, destroy, index } from '@/routes/companies';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Company {
    id: number;
    parent_id?: number | null;
    name: string;
    business_name: string;
    trade_name: string;
    tax_id: string;
    code?: string;
    email?: string;
    phone?: string;
    website?: string;
    logo?: string;
    is_active: boolean;
    children_count?: number;
}

defineProps<{
    companies: {
        data: Company[];
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
        title: 'Companies',
        href: index.url(),
    },
];

const deleteCompany = (id: number) => {
    if (confirm('Are you sure you want to delete this company?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Companies" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Companies
                    </h2>
                    <Link :href="create.url()">
                        <Button>Create Company</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Business Name</TableHead>
                                <TableHead>Trade Name</TableHead>
                                <TableHead>Tax ID</TableHead>
                                <TableHead>Branches</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="company in companies.data" :key="company.id">
                                <TableCell class="font-medium">{{ company.business_name }}</TableCell>
                                <TableCell>{{ company.trade_name }}</TableCell>
                                <TableCell>{{ company.tax_id }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ company.children_count || 0 }} branches</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="company.is_active ? 'default' : 'secondary'">
                                        {{ company.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="edit.url(company.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteCompany(company.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="companies.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                    No companies found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2" v-if="companies.meta.last_page > 1">
                        <Link v-for="link in companies.meta.links" 
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
