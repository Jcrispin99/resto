<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ProductAutocomplete from '@/components/ProductAutocomplete.vue';
import { store, index } from '@/routes/stock-transfers';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Product {
    id: number;
    name: string;
}

interface TransferItem {
    product_id: number | null;
    product_name: string;
    quantity: number;
    notes: string;
}

interface Journal {
    id: number;
    code: string;
    name: string;
    type: string;
}

interface Props {
    warehouses: { data: any[] };
    journals?: Journal[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Stock Transfers',
        href: index.url(),
    },
    {
        title: 'Create',
        href: '/stock-transfers/create',
    },
];

const statuses = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_transit', label: 'In Transit' },
    { value: 'received', label: 'Received' },
];

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    journal_id: null as number | null,
    from_warehouse_id: null as number | null,
    to_warehouse_id: null as number | null,
    transfer_date: today,
    status: 'pending' as string,
    notes: '',
    items: [] as TransferItem[],
});

// Add initial item
if (form.items.length === 0) {
    form.items.push({
        product_id: null,
        product_name: '',
        quantity: 1,
        notes: '',
    });
}

const addItem = () => {
    form.items.push({
        product_id: null,
        product_name: '',
        quantity: 1,
        notes: '',
    });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const updateItemProduct = (index: number, product: Product) => {
    form.items[index].product_id = product.id;
    form.items[index].product_name = product.name;
};

const availableToWarehouses = (fromId: number | null) => {
    if (!fromId) return props.warehouses.data;
    return props.warehouses.data.filter(w => w.id !== fromId);
};

const submit = () => {
    form.post(store.url());
};
</script>

<template>
    <Head title="Create Stock Transfer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Stock Transfer
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Header Section -->
                        <div class="border-b pb-4">
                            <h3 class="font-semibold text-lg mb-4">Transfer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Journal (Serie) -->
                                <div class="space-y-2">
                                    <Label for="journal_id">Serie *</Label>
                                    <Select v-model="form.journal_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar serie" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="journal in journals" :key="journal.id" :value="journal.id">
                                                {{ journal.code }} - {{ journal.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.journal_id" class="text-red-500 text-sm">
                                        {{ form.errors.journal_id }}
                                    </div>
                                    <p class="text-xs text-blue-600 mt-1">
                                        📝 El número de traslado se generará automáticamente al guardar
                                    </p>
                                </div>

                                <!-- From Warehouse -->
                                <div class="space-y-2">
                                    <Label for="from_warehouse_id">From Warehouse *</Label>
                                    <Select v-model="form.from_warehouse_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select origin" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="warehouse in warehouses.data" :key="warehouse.id" :value="warehouse.id">
                                                {{ warehouse.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.from_warehouse_id" class="text-red-500 text-sm">
                                        {{ form.errors.from_warehouse_id }}
                                    </div>
                                </div>

                                <!-- To Warehouse -->
                                <div class="space-y-2">
                                    <Label for="to_warehouse_id">To Warehouse *</Label>
                                    <Select v-model="form.to_warehouse_id" :disabled="!form.from_warehouse_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select destination" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem 
                                                v-for="warehouse in availableToWarehouses(form.from_warehouse_id)" 
                                                :key="warehouse.id" 
                                                :value="warehouse.id">
                                                {{ warehouse.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.to_warehouse_id" class="text-red-500 text-sm">
                                        {{ form.errors.to_warehouse_id }}
                                    </div>
                                </div>

                                <!-- Transfer Date -->
                                <div class="space-y-2">
                                    <Label for="transfer_date">Transfer Date *</Label>
                                    <Input id="transfer_date" type="date" v-model="form.transfer_date" required />
                                    <div v-if="form.errors.transfer_date" class="text-red-500 text-sm">
                                        {{ form.errors.transfer_date }}
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label for="status">Status *</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="status in statuses" :key="status.value" :value="status.value">
                                                {{ status.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.status" class="text-red-500 text-sm">
                                        {{ form.errors.status }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-semibold text-lg">Transfer Items</h3>
                                <Button type="button" variant="outline" size="sm" @click="addItem">
                                    + Add Product
                                </Button>
                            </div>

                            <div class="space-y-3">
                                <div v-for="(item, index) in form.items" :key="index" 
                                     class="grid grid-cols-12 gap-2 p-3 border rounded bg-gray-50">
                                    <!-- Product -->
                                    <div class="col-span-6">
                                        <Label class="text-xs">Product *</Label>
                                        <ProductAutocomplete
                                            v-model="item.product_id"
                                            @select="(product: Product) => updateItemProduct(index, product)"
                                            placeholder="Search product..."
                                        />
                                    </div>

                                    <!-- Quantity -->
                                    <div class="col-span-2">
                                        <Label class="text-xs">Quantity *</Label>
                                        <Input type="number" v-model.number="item.quantity" 
                                               class="h-9" step="0.01" min="0.01" />
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-span-3">
                                        <Label class="text-xs">Notes</Label>
                                        <Input v-model="item.notes" class="h-9" placeholder="Optional notes" />
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="col-span-1 flex items-end">
                                        <Button type="button" variant="destructive" size="sm" 
                                                @click="removeItem(index)" :disabled="form.items.length === 1"
                                                class="h-9">
                                            ✕
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Transfer Notes</Label>
                            <Input id="notes" v-model="form.notes" placeholder="Additional notes..." />
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-2 pt-4">
                            <Link :href="index.url()">
                                <Button variant="outline" type="button">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing || form.items.length === 0">
                                {{ form.processing ? 'Creating...' : 'Create Transfer' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
