<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { Plus, Trash2, ChevronsUpDown } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface AttributeValue {
    id: number;
    value: string;
}

interface ProductAttribute {
    id: number;
    name: string;
    values: AttributeValue[];
}

interface Category {
    id: number;
    name: string;
}

interface Unit {
    id: number;
    name: string;
}

const props = defineProps<{
    attributes: ProductAttribute[];
    categories: Category[];
    menuCategories: Category[];
    units: Unit[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Products',
        href: '/product-templates',
    },
    {
        title: 'Create',
        href: '/product-templates/create',
    },
];

interface AttributeLineForm {
    attribute_id: string;
    value_ids: number[];
    showDropdown?: boolean;
}

const form = useForm({
    name: '',
    description: '',
    unit_id: '',
    
    // PRINCIPAL: Menu/POS
    menu_category_id: null as string | null,
    sale_price: 0,
    can_be_sold: true,        // TRUE por defecto
    can_be_stocked: true,
    can_be_purchased: true,
    
    // OPCIONAL: Inventory category
    has_inventory_category: false,
    category_id: null as string | null,
    
    product_type: 'storable',
    attribute_lines: [] as AttributeLineForm[],
    
    // Menu Settings (opcional)
    menu_settings: {
        enabled: false,
        menu_name: '',
        menu_description: '',
        preparation_time_minutes: 0,
        is_featured: false,
        calories: undefined as number | undefined,
        is_spicy: false,
        is_vegetarian: false,
        is_vegan: false,
        is_gluten_free: false,
        allergens: [] as string[],
        available_for_dine_in: true,
        available_for_takeout: true,
        available_for_delivery: true,
    },
});

const addAttributeLine = () => {
    form.attribute_lines.push({ attribute_id: '', value_ids: [], showDropdown: false });
};

const removeAttributeLine = (index: number) => {
    form.attribute_lines.splice(index, 1);
};

const getAttributeValues = (attributeId: string) => {
    const attr = props.attributes.find(a => a.id.toString() === attributeId);
    return attr ? attr.values : [];
};

const toggleValue = (line: AttributeLineForm, valueId: number) => {
    const index = line.value_ids.indexOf(valueId);
    if (index > -1) {
        line.value_ids.splice(index, 1);
    } else {
        line.value_ids.push(valueId);
    }
};

const submit = () => {
    form.post('/product-templates');
};
</script>

<template>
    <Head title="Create Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Product
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <Tabs default-value="info" class="space-y-4">
                        <TabsList class="grid w-full grid-cols-2">
                            <TabsTrigger value="info">Product Info</TabsTrigger>
                            <TabsTrigger value="inventory" :disabled="!form.can_be_stocked">Inventory</TabsTrigger>
                        </TabsList>

                        <!-- TAB 1: Product Info -->
                        <TabsContent value="info" class="space-y-4">
                            <!-- General Info -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>General Information</CardTitle>
                                    <CardDescription>Basic product details</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid gap-2">
                                        <Label for="name">Product Name *</Label>
                                        <Input id="name" v-model="form.name" placeholder="e.g., Classic Burger, Inca Kola" required />
                                        <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="unit">Unit *</Label>
                                        <Select v-model="form.unit_id">
                                            <SelectTrigger id="unit">
                                                <SelectValue placeholder="Select Unit" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="unit in units" :key="unit.id" :value="unit.id.toString()">
                                                    {{ unit.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <span v-if="form.errors.unit_id" class="text-sm text-red-500">{{ form.errors.unit_id }}</span>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="description">Description</Label>
                                        <Textarea id="description" v-model="form.description" placeholder="Internal description" rows="2" />
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Menu/POS Settings -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Menu & POS Settings</CardTitle>
                                    <CardDescription>Product configuration for sale in POS/Menu</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid gap-2">
                                        <Label for="menu_category">Menu Category *</Label>
                                        <p class="text-sm text-muted-foreground">Category to display this product in the POS/Menu</p>
                                        <Select v-model="form.menu_category_id">
                                            <SelectTrigger id="menu_category">
                                                <SelectValue placeholder="e.g., Beverages, Entrees, Main Dishes" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="cat in menuCategories" :key="cat.id" :value="cat.id.toString()">
                                                    {{ cat.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="sale_price">Sale Price *</Label>
                                        <Input 
                                            id="sale_price" 
                                            v-model.number="form.sale_price" 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            placeholder="0.00"
                                            required
                                        />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex items-center space-x-2">
                                            <Checkbox id="can_be_sold" v-model:checked="form.can_be_sold" />
                                            <Label for="can_be_sold" class="cursor-pointer">Can be sold</Label>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Checkbox id="can_be_stocked" v-model:checked="form.can_be_stocked" />
                                            <Label for="can_be_stocked" class="cursor-pointer">Manage stock</Label>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="can_be_purchased" v-model:checked="form.can_be_purchased" />
                                        <Label for="can_be_purchased" class="cursor-pointer">Can be purchased from suppliers</Label>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Optional: Inventory Category -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Inventory Category (Optional)</CardTitle>
                                    <CardDescription>For internal tracking only</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="has_inventory_cat" v-model:checked="form.has_inventory_category" />
                                        <Label for="has_inventory_cat" class="cursor-pointer">Assign inventory category</Label>
                                    </div>

                                    <div v-if="form.has_inventory_category" class="grid gap-2 pl-6 border-l-2 border-blue-200">
                                        <Label for="category">Inventory Category</Label>
                                        <p class="text-sm text-muted-foreground">For internal tracking (e.g., Raw Materials, Supplies)</p>
                                        <Select v-model="form.category_id">
                                            <SelectTrigger id="category">
                                                <SelectValue placeholder="Select Inventory Category" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()">
                                                    {{ category.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Optional: Menu Settings -->
                            <Card>
                                <CardHeader>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <CardTitle>Digital Menu Settings (Optional)</CardTitle>
                                            <CardDescription>Additional info for digital menu display</CardDescription>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Checkbox id="show_in_menu" v-model:checked="form.menu_settings.enabled" />
                                            <Label for="show_in_menu" class="cursor-pointer">Enable</Label>
                                        </div>
                                    </div>
                                </CardHeader>
                                
                                <CardContent v-if="form.menu_settings.enabled" class="space-y-4">
                                    <div class="grid gap-2">
                                        <Label for="menu_name">Menu Display Name (optional)</Label>
                                        <Input 
                                            id="menu_name" 
                                            v-model="form.menu_settings.menu_name" 
                                            placeholder="Override product name for menu"
                                        />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="menu_description">Customer Description</Label>
                                        <Textarea 
                                            id="menu_description" 
                                            v-model="form.menu_settings.menu_description" 
                                            placeholder="Appealing description for customers"
                                            rows="2"
                                        />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="grid gap-2">
                                            <Label for="prep_time">Prep Time (min)</Label>
                                            <Input id="prep_time" v-model.number="form.menu_settings.preparation_time_minutes" type="number" min="0" />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label for="calories">Calories</Label>
                                            <Input id="calories" v-model.number="form.menu_settings.calories" type="number" min="0" />
                                        </div>
                                    </div>

                                    <div>
                                        <Label class="text-sm">Dietary Info</Label>
                                        <div class="grid grid-cols-2 gap-3 mt-2">
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="is_spicy" v-model:checked="form.menu_settings.is_spicy" />
                                                <Label for="is_spicy" class="cursor-pointer text-sm">🌶️ Spicy</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="is_vegetarian" v-model:checked="form.menu_settings.is_vegetarian" />
                                                <Label for="is_vegetarian" class="cursor-pointer text-sm">🥗 Vegetarian</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="is_vegan" v-model:checked="form.menu_settings.is_vegan" />
                                                <Label for="is_vegan" class="cursor-pointer text-sm">🌱 Vegan</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="is_gluten_free" v-model:checked="form.menu_settings.is_gluten_free" />
                                                <Label for="is_gluten_free" class="cursor-pointer text-sm">🌾 Gluten Free</Label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="is_featured" v-model:checked="form.menu_settings.is_featured" />
                                        <Label for="is_featured" class="cursor-pointer">⭐ Featured Item</Label>
                                    </div>

                                    <div>
                                        <Label class="text-sm">Available For</Label>
                                        <div class="grid grid-cols-3 gap-3 mt-2">
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="dine_in" v-model:checked="form.menu_settings.available_for_dine_in" />
                                                <Label for="dine_in" class="cursor-pointer text-sm">Dine In</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="takeout" v-model:checked="form.menu_settings.available_for_takeout" />
                                                <Label for="takeout" class="cursor-pointer text-sm">Takeout</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Checkbox id="delivery" v-model:checked="form.menu_settings.available_for_delivery" />
                                                <Label for="delivery" class="cursor-pointer text-sm">Delivery</Label>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <!-- TAB 2: Inventory -->
                        <TabsContent value="inventory" class="space-y-4">
                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between">
                                    <div>
                                        <CardTitle>Attributes & Variants</CardTitle>
                                        <CardDescription>Define attributes to generate variants (e.g., Size, Color)</CardDescription>
                                    </div>
                                    <Button type="button" variant="outline" size="sm" @click="addAttributeLine">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Attribute
                                    </Button>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div v-if="form.errors.attribute_lines" class="text-sm text-red-500 mb-2">{{ form.errors.attribute_lines }}</div>
                                    
                                    <div v-for="(line, index) in form.attribute_lines" :key="index" class="p-4 border rounded-lg bg-gray-50 relative">
                                        <Button type="button" variant="ghost" size="icon" class="absolute top-2 right-2 text-red-500 hover:text-red-700" @click="removeAttributeLine(index)">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>

                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="grid gap-2">
                                                <Label :for="`attr-${index}`">Attribute</Label>
                                                <Select v-model="line.attribute_id">
                                                    <SelectTrigger :id="`attr-${index}`">
                                                        <SelectValue placeholder="Select Attribute" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="attr in attributes" :key="attr.id" :value="attr.id.toString()">
                                                            {{ attr.name }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <span v-if="form.errors[`attribute_lines.${index}.attribute_id`]" class="text-sm text-red-500">
                                                    {{ form.errors[`attribute_lines.${index}.attribute_id`] }}
                                                </span>
                                            </div>

                                            <div class="grid gap-2" v-if="line.attribute_id">
                                                <Label>Values</Label>
                                                <div class="relative">
                                                    <Button 
                                                        type="button"
                                                        variant="outline" 
                                                        class="w-full justify-between font-normal"
                                                        @click="line.showDropdown = !line.showDropdown"
                                                    >
                                                        <span v-if="line.value_ids.length > 0" class="truncate">
                                                            {{ getAttributeValues(line.attribute_id).filter(v => line.value_ids.includes(v.id)).map(v => v.value).join(', ') }}
                                                        </span>
                                                        <span v-else class="text-muted-foreground">Select values...</span>
                                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                    <div 
                                                        v-if="line.showDropdown" 
                                                        class="absolute z-50 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md outline-none"
                                                    >
                                                        <div class="p-2 max-h-60 overflow-auto">
                                                            <div 
                                                                v-for="val in getAttributeValues(line.attribute_id)" 
                                                                :key="val.id"
                                                                class="flex items-center space-x-2 rounded-sm px-2 py-1.5 hover:bg-accent cursor-pointer"
                                                                @click.stop="toggleValue(line, val.id)"
                                                            >
                                                                <Checkbox 
                                                                    :checked="line.value_ids.includes(val.id)"
                                                                    @click.stop
                                                                />
                                                                <span class="text-sm">{{ val.value }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span v-if="form.errors[`attribute_lines.${index}.value_ids`]" class="text-sm text-red-500">
                                                    {{ form.errors[`attribute_lines.${index}.value_ids`] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="form.attribute_lines.length === 0" class="text-center py-8 text-gray-500">
                                        No attributes added. This product will have a single default variant.
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>

                    <div class="flex justify-end gap-4">
                        <Link href="/product-templates">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Product' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
