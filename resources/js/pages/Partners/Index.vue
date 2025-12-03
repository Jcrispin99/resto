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
import { create, edit, destroy, index } from '@/routes/partners';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Partner {
    id: number;
    code: string;
    partner_type: string;
    name: string;
    trade_name?: string;
    tax_id: string;
    email: string;
    phone: string;
    is_customer: boolean;
    is_supplier: boolean;
    roles: string;
    payment_terms_days: number;
    is_active: boolean;
}

interface Props {
    partners: {
        data: Partner[];
        meta: any;
        links: any;
    };
    filter?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Partners',
        href: index.url(),
    },
];

const deletePartner = (id: number) => {
    if (confirm('Are you sure you want to delete this partner?')) {
        router.delete(destroy.url(id));
    }
};

const filterPartners = (filter: string | null) => {
    router.get(index.url(), filter ? { filter } : {});
};

const getTypeLabel = (type: string) => {
    return type === 'individual' ? 'Individual' : 'Company';
};
</script>

<template>
    <Head title="Partners" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Partners (Customers & Suppliers)
                    </h2>
                    <Link :href="create.url()">
                        <Button>Create Partner</Button>
                    </Link>
                </div>

                <!-- Filter Buttons -->
                <div class="mb-4 flex gap-2">
                    <Button 
                        :variant="!filter ? 'default' : 'outline'" 
                        @click="filterPartners(null)">
                        All Partners
                    </Button>
                    <Button 
                        :variant="filter === 'customers' ? 'default' : 'outline'" 
                        @click="filterPartners('customers')">
                        Customers Only
                    </Button>
                    <Button 
                        :variant="filter === 'suppliers' ? 'default' : 'outline'" 
                        @click="filterPartners('suppliers')">
                        Suppliers Only
                    </Button>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Tax ID</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Payment Terms</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="partner in partners.data" :key="partner.id">
                                <TableCell class="font-medium">{{ partner.code }}</TableCell>
                                <TableCell>
                                    <div>{{ partner.name }}</div>
                                    <div v-if="partner.trade_name" class="text-sm text-gray-500">{{ partner.trade_name }}</div>
                                </TableCell>
                                <TableCell>{{ partner.tax_id }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ getTypeLabel(partner.partner_type) }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="default">{{ partner.roles }}</Badge>
                                </TableCell>
                                <TableCell>{{ partner.payment_terms_days }} days</TableCell>
                                <TableCell>
                                    <Badge :variant="partner.is_active ? 'default' : 'secondary'">
                                        {{ partner.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="edit.url(partner.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deletePartner(partner.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="partners.data.length === 0">
                                <TableCell colspan="8" class="text-center py-8 text-gray-500">
                                    No partners found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2" v-if="partners.meta.last_page > 1">
                        <Link v-for="link in partners.meta.links" 
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
