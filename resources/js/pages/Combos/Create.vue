<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Trash2 } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

interface ProductTemplate {
    id: number;
    name: string;
}

interface ComboItemForm {
    product_template_id: string;
    quantity: number;
    allow_substitution: boolean;
}

const props = defineProps<{
    products: ProductTemplate[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Combos', href: '/combos' },
    { title: 'Create', href: '/combos/create' },
];

const form = useForm({
    name: '',
    description: '',
    price: 0,
    regular_price: 0,
    image: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    is_active: true,
    items: [] as ComboItemForm[],
});

const discountPercentage = computed(() => {
    if (form.regular_price <= 0) return 0;
    return ((form.regular_price - form.price) / form.regular_price) * 100;
});

const savings = computed(() => {
    return form.regular_price - form.price;
});

const addItem = () => {
    form.items.push({
        product_template_id: '',
        quantity: 1,
        allow_substitution: false,
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.post('/combos');
};

// Add first item by default
if (form.items.length === 0) {
    addItem();
}
</script>

<template>
    <Head title="Create Combo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Combo / Promotion
                </h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Set up your combo details</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Name -->
                            <div class="grid gap-2">
                                <Label for="name">Combo Name *</Label>
                                <Input 
                                    id="name"
                                    v-model="form.name" 
                                    placeholder="e.g., Combo Ejecutivo"
                                    required
                                />
                                <span v-if="form.errors.name" class="text-sm text-red-500">
                                    {{ form.errors.name }}
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="grid gap-2">
                                <Label for="description">Description</Label>
                                <Textarea 
                                    id="description"
                                    v-model="form.description" 
                                    placeholder="Brief description of the combo"
                                    rows="2"
                                />
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="start_date">Start Date *</Label>
                                    <Input 
                                        id="start_date"
                                        v-model="form.start_date" 
                                        type="date"
                                        required
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="end_date">End Date <span class="text-sm font-normal text-gray-500">(optional)</span></Label>
                                    <Input 
                                        id="end_date"
                                        v-model="form.end_date" 
                                        type="date"
                                    />
                                    <p class="text-xs text-gray-500">Leave empty for no expiration</p>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center gap-2">
                                <Checkbox id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active" class="cursor-pointer">Active (available for sale)</Label>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pricing -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing</CardTitle>
                            <CardDescription>Set combo and regular prices</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="regular_price">Regular Price (S/) *</Label>
                                    <Input 
                                        id="regular_price"
                                        v-model.number="form.regular_price" 
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        required
                                    />
                                    <p class="text-xs text-gray-500">Sum of individual prices</p>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="price">Combo Price (S/) *</Label>
                                    <Input 
                                        id="price"
                                        v-model.number="form.price" 
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        required
                                    />
                                    <p class="text-xs text-gray-500">Discounted price</p>
                                </div>
                            </div>

                            <!-- Discount Info -->
                            <div v-if="form.regular_price > 0 && form.price > 0" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-green-900">Customer Savings</p>
                                        <p class="text-2xl font-bold text-green-600">S/ {{ savings.toFixed(2) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-green-900">Discount</p>
                                        <p class="text-2xl font-bold text-green-600">{{ discountPercentage.toFixed(1) }}%</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Combo Items -->
                    <Card>
                        <CardHeader>
                            <div class="flex justify-between items-center">
                                <div>
                                    <CardTitle>Combo Items</CardTitle>
                                    <CardDescription>Products included in this combo</CardDescription>
                                </div>
                                <Button type="button" variant="outline" size="sm" @click="addItem">
                                    <Plus class="w-4 h-4 mr-1" />
                                    Add Item
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
                                No items added yet. Click "Add Item" to start.
                            </div>

                            <div v-for="(item, index) in form.items" :key="index" class="p-4 border rounded-lg">
                                <div class="flex gap-4">
                                    <div class="flex-1 space-y-3">
                                        <!-- Product -->
                                        <div class="grid gap-2">
                                            <Label>Product *</Label>
                                            <Select v-model="item.product_template_id" required>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a product" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="product in products" :key="product.id" :value="product.id.toString()">
                                                        {{ product.name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <!-- Quantity & Substitution -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="grid gap-2">
                                                <Label>Quantity *</Label>
                                                <Input 
                                                    v-model.number="item.quantity" 
                                                    type="number"
                                                    min="1"
                                                    required
                                                />
                                            </div>
                                            <div class="flex items-end">
                                                <div class="flex items-center gap-2">
                                                    <Checkbox 
                                                        :id="`substitution-${index}`"
                                                        v-model:checked="item.allow_substitution" 
                                                    />
                                                    <Label :for="`substitution-${index}`" class="cursor-pointer text-sm">
                                                        Allow substitution
                                                    </Label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="flex items-start">
                                        <Button 
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="text-red-600 hover:text-red-700"
                                            @click="removeItem(index)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <span v-if="form.errors.items" class="text-sm text-red-500">
                                {{ form.errors.items }}
                            </span>
                        </CardContent>
                    </Card>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-4">
                        <Link href="/combos">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing || form.items.length === 0">
                            {{ form.processing ? 'Creating...' : 'Create Combo' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
