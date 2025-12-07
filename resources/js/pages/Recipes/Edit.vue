<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

interface ProductTemplate {
    id: number;
    name: string;
}

interface ProductProduct {
    id: number;
    sku: string;
    template: {
        name: string;
    };
}

interface Unit {
    id: number;
    name: string;
    abbreviation: string;
}

interface Recipe {
    id: number;
    product_template_id: number;
    ingredient_id: number;
    quantity: number;
    unit_id: number;
    waste_percentage: number;
    notes?: string;
}

const props = defineProps<{
    recipe: Recipe;
    productTemplates: ProductTemplate[];
    ingredients: ProductProduct[];
    units: Unit[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Recipes', href: '/recipes' },
    { title: 'Edit', href: `/recipes/${props.recipe.id}/edit` },
];

const form = useForm({
    product_template_id: props.recipe.product_template_id.toString(),
    ingredient_id: props.recipe.ingredient_id.toString(),
    quantity: props.recipe.quantity,
    unit_id: props.recipe.unit_id.toString(),
    waste_percentage: props.recipe.waste_percentage || 0,
    notes: props.recipe.notes || '',
});

const selectedUnit = computed(() => {
    return props.units.find(u => u.id.toString() === form.unit_id);
});

const conversionInfo = computed(() => {
    if (!selectedUnit.value || !form.quantity) return null;
    
    const abbr = selectedUnit.value.abbreviation.toLowerCase();
    
    if (abbr === 'g' && form.quantity >= 1000) {
        return `= ${(form.quantity / 1000).toFixed(2)} kg`;
    } else if (abbr === 'kg') {
        return `= ${(form.quantity * 1000).toFixed(0)} g`;
    } else if (abbr === 'ml' && form.quantity >= 1000) {
        return `= ${(form.quantity / 1000).toFixed(2)} L`;
    } else if (abbr === 'l') {
        return `= ${(form.quantity * 1000).toFixed(0)} ml`;
    }
    
    return null;
});

const totalWithWaste = computed(() => {
    if (!form.quantity || !form.waste_percentage) return form.quantity;
    return form.quantity * (1 + form.waste_percentage / 100);
});

const submit = () => {
    form.put(`/recipes/${props.recipe.id}`);
};
</script>

<template>
    <Head title="Edit Recipe" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Recipe
                </h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Recipe Information</CardTitle>
                            <CardDescription>Update the ingredients needed for this dish</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Product Template (Dish) -->
                            <div class="grid gap-2">
                                <Label for="product_template">Dish / Product *</Label>
                                <Select v-model="form.product_template_id" required>
                                    <SelectTrigger id="product_template">
                                        <SelectValue placeholder="Select a product" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="product in productTemplates" :key="product.id" :value="product.id.toString()">
                                            {{ product.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.product_template_id" class="text-sm text-red-500">
                                    {{ form.errors.product_template_id }}
                                </span>
                            </div>

                            <!-- Ingredient -->
                            <div class="grid gap-2">
                                <Label for="ingredient">Ingredient *</Label>
                                <Select v-model="form.ingredient_id" required>
                                    <SelectTrigger id="ingredient">
                                        <SelectValue placeholder="Select an ingredient" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="ingredient in ingredients" :key="ingredient.id" :value="ingredient.id.toString()">
                                            {{ ingredient.template.name }} ({{ ingredient.sku }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.ingredient_id" class="text-sm text-red-500">
                                    {{ form.errors.ingredient_id }}
                                </span>
                            </div>

                            <!-- Quantity & Unit -->
                            <div class="grid gap-2">
                                <Label>Quantity *</Label>
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <Input 
                                            v-model.number="form.quantity" 
                                            type="number" 
                                            step="0.001"
                                            min="0.001"
                                            required
                                        />
                                    </div>
                                    <div class="w-32">
                                        <Select v-model="form.unit_id" required>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Unit" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="unit in units" :key="unit.id" :value="unit.id.toString()">
                                                    {{ unit.abbreviation }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                                
                                <div v-if="conversionInfo" class="mt-1 p-2 bg-blue-50 rounded text-sm text-blue-700">
                                    📊 {{ form.quantity }} {{ selectedUnit?.abbreviation }} {{ conversionInfo }}
                                </div>
                                
                                <span v-if="form.errors.quantity" class="text-sm text-red-500">
                                    {{ form.errors.quantity }}
                                </span>
                            </div>

                            <!-- Waste Percentage -->
                            <div class="grid gap-2">
                                <Label for="waste">Waste / Shrinkage (%)</Label>
                                <Input 
                                    id="waste"
                                    v-model.number="form.waste_percentage" 
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    max="100"
                                />
                                <p class="text-xs text-gray-500">
                                    Expected loss during preparation
                                </p>
                                
                                <div v-if="form.waste_percentage > 0" class="mt-1 p-3 bg-orange-50 border border-orange-200 rounded">
                                    <div class="text-sm space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">Base:</span>
                                            <span class="font-medium">{{ Number(form.quantity || 0).toFixed(3) }} {{ selectedUnit?.abbreviation }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">Waste:</span>
                                            <span class="font-medium text-orange-600">+{{ (Number(form.quantity || 0) * form.waste_percentage / 100).toFixed(3) }} {{ selectedUnit?.abbreviation }}</span>
                                        </div>
                                        <div class="flex justify-between pt-1 border-t border-orange-300">
                                            <span class="font-medium">Total:</span>
                                            <span class="font-bold text-orange-700">{{ Number(totalWithWaste || 0).toFixed(3) }} {{ selectedUnit?.abbreviation }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="grid gap-2">
                                <Label for="notes">Notes</Label>
                                <Textarea 
                                    id="notes"
                                    v-model="form.notes" 
                                    rows="3"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4">
                        <Link href="/recipes">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Recipe' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
