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
import { create, edit, destroy, index } from '@/routes/sale-orders';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface SaleOrder {
    id: number;
    order_number: string;
    partner_name?: string;
    warehouse_name?: string;
    order_date: string;
    delivery_date?: string;
    status: string;
    total: number;
}

interface Props {
    orders: {
        data: SaleOrder[];
        meta: any;
        links: any;
    };
    statusFilter?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Sale Orders',
        href: index.url(),
    },
];

const deleteOrder = (id: number) => {
    if (confirm('Are you sure you want to delete this sale order?')) {
        router.delete(destroy.url(id));
    }
};

const filterByStatus = (status: string | null) => {
    router.get(index.url(), status ? { status } : {});
};

const getStatusBadge = (status: string) => {
    const badges: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' }> = {
        'quote': { label: 'Quote', variant: 'outline' },
        'quote_sent': { label: 'Quote Sent', variant: 'secondary' },
        'approved': { label: 'Approved', variant: 'default' },
        'processing': { label: 'Processing', variant: 'default' },
        'delivered': { label: 'Delivered', variant: 'default' },
        'paid': { label: 'Paid', variant: 'default' },
        'cancelled': { label: 'Cancelled', variant: 'destructive' },
    };
    return badges[status] || { label: status, variant: 'outline' };
};
</script>

<template>
    <Head title="Sale Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Sale Orders
                    </h2>
                    <Link :href="create.url()">
                        <Button>Create Sale Order</Button>
                    </Link>
                </div>

                <!-- Status Filters -->
                <div class="mb-4 flex gap-2 flex-wrap">
                    <Button 
                        :variant="!statusFilter ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus(null)">
                        All
                    </Button>
                    <Button 
                        :variant="statusFilter === 'quote' ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus('quote')">
                        Quotes
                    </Button>
                    <Button 
                        :variant="statusFilter === 'approved' ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus('approved')">
                        Approved
                    </Button>
                    <Button 
                        :variant="statusFilter === 'delivered' ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus('delivered')">
                        Delivered
                    </Button>
                    <Button 
                        :variant="statusFilter === 'paid' ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus('paid')">
                        Paid
                    </Button>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Order #</TableHead>
                                <TableHead>Customer</TableHead>
                                <TableHead>Warehouse</TableHead>
                                <TableHead>Order Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="order in orders.data" :key="order.id">
                                <TableCell class="font-medium">{{ order.order_number }}</TableCell>
                                <TableCell>{{ order.partner_name || 'N/A' }}</TableCell>
                                <TableCell>{{ order.warehouse_name || 'N/A' }}</TableCell>
                                <TableCell>{{ order.order_date }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadge(order.status).variant">
                                        {{ getStatusBadge(order.status).label }}
                                    </Badge>
                                </TableCell>
                                <TableCell>S/. {{ Number(order.total).toFixed(2) }}</TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="edit.url(order.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteOrder(order.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="orders.data.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                    No sale orders found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2" v-if="orders.meta.last_page > 1">
                        <Link v-for="link in orders.meta.links" 
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
