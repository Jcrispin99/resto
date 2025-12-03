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
import { create, edit, destroy, index } from '@/routes/stock-transfers';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface StockTransfer {
    id: number;
    transfer_number: string;
    from_warehouse_name?: string;
    to_warehouse_name?: string;
    transfer_date: string;
    status: string;
}

interface Props {
    transfers: {
        data: StockTransfer[];
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
        title: 'Stock Transfers',
        href: index.url(),
    },
];

const deleteTransfer = (id: number) => {
    if (confirm('Are you sure you want to delete this stock transfer?')) {
        router.delete(destroy.url(id));
    }
};

const filterByStatus = (status: string | null) => {
    router.get(index.url(), status ? { status } : {});
};

const getStatusBadge = (status: string) => {
    const badges: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' }> = {
        'pending': { label: 'Pending', variant: 'outline' },
        'in_transit': { label: 'In Transit', variant: 'secondary' },
        'received': { label: 'Received', variant: 'default' },
        'cancelled': { label: 'Cancelled', variant: 'destructive' },
    };
    return badges[status] || { label: status, variant: 'outline' };
};
</script>

<template>
    <Head title="Stock Transfers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Stock Transfers
                    </h2>
                    <Link :href="create.url()">
                        <Button>Create Transfer</Button>
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
                        :variant="statusFilter === 'pending' ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus('pending')">
                        Pending
                    </Button>
                    <Button 
                        :variant="statusFilter === 'in_transit' ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus('in_transit')">
                        In Transit
                    </Button>
                    <Button 
                        :variant="statusFilter === 'received' ? 'default' : 'outline'" 
                        size="sm"
                        @click="filterByStatus('received')">
                        Received
                    </Button>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Transfer #</TableHead>
                                <TableHead>From Warehouse</TableHead>
                                <TableHead>To Warehouse</TableHead>
                                <TableHead>Transfer Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="transfer in transfers.data" :key="transfer.id">
                                <TableCell class="font-medium">{{ transfer.transfer_number }}</TableCell>
                                <TableCell>{{ transfer.from_warehouse_name || 'N/A' }}</TableCell>
                                <TableCell>{{ transfer.to_warehouse_name || 'N/A' }}</TableCell>
                                <TableCell>{{ transfer.transfer_date }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadge(transfer.status).variant">
                                        {{ getStatusBadge(transfer.status).label }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="edit.url(transfer.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteTransfer(transfer.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="transfers.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                    No stock transfers found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2" v-if="transfers.meta.last_page > 1">
                        <Link v-for="link in transfers.meta.links" 
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
