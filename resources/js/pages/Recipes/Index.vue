<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Edit, Trash2, Calculator } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

interface Recipe {
    id: number;
    product_template_id: number;
    ingredient_id: number;
    quantity: number;
    waste_percentage: number;
    notes?: string;
    product_template: {
        id: number;
        name: string;
    };
    ingredient: {
        id: number;
        template: {
            name: string;
        };
    };
    unit: {
        id: number;
        name: string;
        abbreviation: string;
    };
}

interface ProductTemplate {
    id: number;
    name: string;
}

const props = defineProps<{
    recipes: {
        data: Recipe[];
        links: any;
        meta: any;
    };
    productTemplates: ProductTemplate[];
    filters: {
        product_template_id?: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Recipes', href: '/recipes' },
];

const selectedProduct = ref(props.filters.product_template_id?.toString() || 'all');

const filterByProduct = () => {
    if (selectedProduct.value && selectedProduct.value !== 'all') {
        router.get('/recipes', { product_template_id: selectedProduct.value });
    } else {
        router.get('/recipes');
    }
};

const deleteRecipe = (id: number) => {
    if (confirm('Are you sure you want to delete this recipe?')) {
        router.delete(`/recipes/${id}`);
    }
};
</script>

<template>
    <Head title="Recipes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Recipes (Recetas)
                    </h2>
                    <Link href="/recipes/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            New Recipe
                        </Button>
                    </Link>
                </div>

                <!-- Filter -->
                <Card class="mb-6">
                    <CardContent class="pt-6">
                        <div class="flex gap-4 items-end">
                            <div class="flex-1">
                                <label class="text-sm font-medium mb-2 block">Filter by Product</label>
                                <Select v-model="selectedProduct">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Products" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Products</SelectItem>
                                        <SelectItem v-for="product in productTemplates" :key="product.id" :value="product.id.toString()">
                                            {{ product.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button @click="filterByProduct">Apply Filter</Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recipes List -->
                <div v-if="recipes.data.length > 0" class="space-y-4">
                    <Card v-for="recipe in recipes.data" :key="recipe.id">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-lg text-gray-900">
                                                {{ recipe.product_template.name }}
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <span class="font-medium">Ingredient:</span> 
                                                {{ recipe.ingredient.template.name }}
                                            </p>
                                            <div class="mt-2 flex gap-4 text-sm">
                                                <span class="text-gray-700">
                                                    <span class="font-medium">Quantity:</span> 
                                                    {{ recipe.quantity }} {{ recipe.unit.abbreviation }}
                                                </span>
                                                <span v-if="recipe.waste_percentage > 0" class="text-orange-600">
                                                    <span class="font-medium">Waste:</span> 
                                                    {{ recipe.waste_percentage }}%
                                                </span>
                                            </div>
                                            <p v-if="recipe.notes" class="text-sm text-gray-500 mt-2 italic">
                                                {{ recipe.notes }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2">
                                    <Link :href="`/recipes/${recipe.id}/edit`">
                                        <Button variant="outline" size="sm">
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button 
                                        variant="outline" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                        @click="deleteRecipe(recipe.id)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <Calculator class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No recipes found</p>
                        <Link href="/recipes/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Create First Recipe
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
