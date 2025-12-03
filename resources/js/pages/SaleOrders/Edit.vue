<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ProductAutocomplete from '@/components/ProductAutocomplete.vue';
import PartnerAutocomplete from '@/components/PartnerAutocomplete.vue';
import { update, index } from '@/routes/sale-orders';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { computed, watch } from 'vue';

interface Product {
    id: number;
    name: string;
    sku?: string;
    sale_price?: number;
}

interface Partner {
    id: number;
    name: string;
}

interface OrderItem {
    product_id: number | null;
    product_name: string;
    quantity: number;
    unit_price: number;
    discount: number;
    tax_amount: number;
    total: number;
}

interface Props {
    order: {
        data: {
            id: number;
            order_number: string;
            branch_id: number;
            warehouse_id: number;
            partner_id: number;
            order_date: string;
            quote_valid_until?: string;
            delivery_date?: string;
            delivery_address?: string;
            delivery_contact?: string;
            delivery_phone?: string;
            status: string;
            notes?: string;
            items?: any[];
        };
    };
    branches: { data: any[] };
    warehouses: { data: any[] };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Sale Orders',
        href: index.url(),
    },
    {
        title: 'Edit',
        href: `/sale-orders/${props.order.data.id}/edit`,
    },
];

const statuses = [
    { value: 'quote', label: 'Quote' },
    { value: 'quote_sent', label: 'Quote Sent' },
    { value: 'approved', label: 'Approved' },
    { value: 'processing', label: 'Processing' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'paid', label: 'Paid' },
];

const form = useForm({
    order_number: props.order.data.order_number,
    branch_id: props.order.data.branch_id,
    warehouse_id: props.order.data.warehouse_id,
    partner_id: props.order.data.partner_id,
    order_date: props.order.data.order_date,
    quote_valid_until: props.order.data.quote_valid_until || '',
    delivery_date: props.order.data.delivery_date || '',
    delivery_address: props.order.data.delivery_address || '',
    delivery_contact: props.order.data.delivery_contact || '',
    delivery_phone: props.order.data.delivery_phone || '',
    status: props.order.data.status,
    notes: props.order.data.notes || '',
    items: (props.order.data.items || []).map(item => ({
        product_id: item.product_id,
        product_name: item.product_name || '',
        quantity: Number(item.quantity),
        unit_price: Number(item.unit_price),
        discount: Number(item.discount || 0),
        tax_amount: Number(item.tax_amount || 0),
        total: Number(item.total),
    })) as OrderItem[],
});

if (form.items.length === 0) {
    form.items.push({
        product_id: null,
        product_name: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        tax_amount: 0,
        total: 0,
    });
}

const addItem = () => {
    form.items.push({
        product_id: null,
        product_name: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        tax_amount: 0,
        total: 0,
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
    form.items[index].unit_price = product.sale_price || 0;
    calculateItemTotal(index);
};

const calculateItemTotal = (index: number) => {
    const item = form.items[index];
    const subtotal = item.quantity * item.unit_price;
    const afterDiscount = subtotal - item.discount;
    item.total = afterDiscount + item.tax_amount;
};

form.items.forEach((_, index) => {
    watch(
        () => [form.items[index].quantity, form.items[index].unit_price, form.items[index].discount, form.items[index].tax_amount],
        () => calculateItemTotal(index)
    );
});

const subtotal = computed(() => 
    form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
);

const totalDiscount = computed(() => 
    form.items.reduce((sum, item) => sum + item.discount, 0)
);

const totalTax = computed(() => 
    form.items.reduce((sum, item) => sum + item.tax_amount, 0)
);

const grandTotal = computed(() => subtotal.value - totalDiscount.value + totalTax.value);

const submit = () => {
    form.put(update.url(props.order.data.id));
};
</script>

<template>
    <Head title="Edit Sale Order" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Sale Order #{{ form.order_number }}
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Header Section -->
                        <div class="border-b pb-4">
                            <h3 class="font-semibold text-lg mb-4">Order Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Order Number -->
                                <div class="space-y-2">
                                    <Label for="order_number">Order Number *</Label>
                                    <Input id="order_number" v-model="form.order_number" required maxlength="20" />
                                    <div v-if="form.errors.order_number" class="text-red-500 text-sm">
                                        {{ form.errors.order_number }}
                                    </div>
                                </div>

                                <!-- Customer -->
                                <div class="space-y-2">
                                    <Label for="partner_id">Customer *</Label>
                                    <PartnerAutocomplete 
                                        v-model="form.partner_id"
                                        type="customers"
                                        placeholder="Search customer..."
                                        @select="(partner: Partner) => form.partner_id = partner.id"
                                    />
                                    <div v-if="form.errors.partner_id" class="text-red-500 text-sm">
                                        {{ form.errors.partner_id }}
                                    </div>
                                </div>

                                <!-- Warehouse -->
                                <div class="space-y-2">
                                    <Label for="warehouse_id">Warehouse *</Label>
                                    <Select v-model="form.warehouse_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select warehouse" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="warehouse in warehouses.data" :key="warehouse.id" :value="warehouse.id">
                                                {{ warehouse.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.warehouse_id" class="text-red-500 text-sm">
                                        {{ form.errors.warehouse_id }}
                                    </div>
                                </div>

                                <!-- Branch -->
                                <div class="space-y-2">
                                    <Label for="branch_id">Branch *</Label>
                                    <Select v-model="form.branch_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select branch" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="branch in branches.data" :key="branch.id" :value="branch.id">
                                                {{ branch.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.branch_id" class="text-red-500 text-sm">
                                        {{ form.errors.branch_id }}
                                    </div>
                                </div>

                                <!-- Order Date -->
                                <div class="space-y-2">
                                    <Label for="order_date">Order Date *</Label>
                                    <Input id="order_date" type="date" v-model="form.order_date" required />
                                    <div v-if="form.errors.order_date" class="text-red-500 text-sm">
                                        {{ form.errors.order_date }}
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

                        <!-- Delivery Section -->
                        <div class="border-b pb-4">
                            <h3 class="font-semibold text-lg mb-4">Delivery Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="delivery_date">Delivery Date</Label>
                                    <Input id="delivery_date" type="date" v-model="form.delivery_date" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="delivery_contact">Contact Person</Label>
                                    <Input id="delivery_contact" v-model="form.delivery_contact" maxlength="100" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="delivery_phone">Contact Phone</Label>
                                    <Input id="delivery_phone" v-model="form.delivery_phone" maxlength="20" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="delivery_address">Delivery Address</Label>
                                    <Input id="delivery_address" v-model="form.delivery_address" maxlength="255" />
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-semibold text-lg">Order Items</h3>
                                <Button type="button" variant="outline" size="sm" @click="addItem">
                                    + Add Product
                                </Button>
                            </div>

                            <div class="space-y-3">
                                <div v-for="(item, index) in form.items" :key="index" 
                                     class="grid grid-cols-12 gap-2 p-3 border rounded bg-gray-50">
                                    <!-- Product -->
                                    <div class="col-span-3">
                                        <Label class="text-xs">Product *</Label>
                                        <ProductAutocomplete
                                            v-model="item.product_id"
                                            @select="(product: Product) => updateItemProduct(index, product)"
                                            placeholder="Search..."
                                        />
                                    </div>

                                    <!-- Quantity -->
                                    <div class="col-span-2">
                                        <Label class="text-xs">Qty *</Label>
                                        <Input type="number" v-model.number="item.quantity" 
                                               class="h-9" step="0.01" min="0.01" />
                                    </div>

                                    <!-- Unit Price -->
                                    <div class="col-span-2">
                                        <Label class="text-xs">Price *</Label>
                                        <Input type="number" v-model.number="item.unit_price" 
                                               class="h-9" step="0.01" min="0" />
                                    </div>

                                    <!-- Discount -->
                                    <div class="col-span-2">
                                        <Label class="text-xs">Discount</Label>
                                        <Input type="number" v-model.number="item.discount" 
                                               class="h-9" step="0.01" min="0" />
                                    </div>

                                    <!-- Tax -->
                                    <div class="col-span-2">
                                        <Label class="text-xs">Tax</Label>
                                        <Input type="number" v-model.number="item.tax_amount" 
                                               class="h-9" step="0.01" min="0" />
                                    </div>

                                    <!-- Total -->
                                    <div class="col-span-2">
                                        <Label class="text-xs">Total</Label>
                                        <Input :value="item.total.toFixed(2)" readonly class="h-9 bg-gray-100" />
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

                        <!-- Totals Section -->
                        <div class="border-t pt-4">
                            <div class="flex justify-end">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Subtotal:</span>
                                        <span class="font-medium">S/. {{ subtotal.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Discount:</span>
                                        <span class="font-medium text-red-600">- S/. {{ totalDiscount.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Tax:</span>
                                        <span class="font-medium">S/. {{ totalTax.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                                        <span>TOTAL:</span>
                                        <span>S/. {{ grandTotal.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Input id="notes" v-model="form.notes" placeholder="Additional notes..." />
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-2 pt-4">
                            <Link :href="index.url()">
                                <Button variant="outline" type="button">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing || form.items.length === 0">
                                {{ form.processing ? 'Updating...' : 'Update Sale Order' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
