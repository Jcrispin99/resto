<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Edit, Trash2, Package } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

interface ComboItem {
    id: number;
    product_template_id: number;
    quantity: number;
    allow_substitution: boolean;
    product: {
        name: string;
    };
}

interface Combo {
    id: number;
    name: string;
    description?: string;
    price: number;
    regular_price: number;
    discount_percentage: number;
    start_date: string;
    end_date?: string;
    is_active: boolean;
    items: ComboItem[];
}

const props = defineProps<{
    combos: {
        data: Combo[];
        links: any;
        meta: any;
    };
    filters: {
        active?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Combos', href: '/combos' },
];

const activeFilter = ref(props.filters.active || 'all');

const filterByActive = () => {
    const params: any = {};
    if (activeFilter.value && activeFilter.value !== 'all') {
        params.active = activeFilter.value;
    }
    router.get('/combos', params);
};

const deleteCombo = (id: number) => {
    if (confirm('Are you sure you want to delete this combo?')) {
        router.delete(`/combos/${id}`);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Combos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Combos & Promotions
                    </h2>
                    <Link href="/combos/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            New Combo
                        </Button>
                    </Link>
                </div>

                <!-- Filter -->
                <Card class="mb-6">
                    <CardContent class="pt-6">
                        <div class="flex gap-4 items-end">
                            <div class="w-48">
                                <label class="text-sm font-medium mb-2 block">Status</label>
                                <Select v-model="activeFilter">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        <SelectItem value="1">Active</SelectItem>
                                        <SelectItem value="0">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button @click="filterByActive">Apply Filter</Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Combos List -->
                <div v-if="combos.data.length > 0" class="grid gap-4 md:grid-cols-2">
                    <Card v-for="combo in combos.data" :key="combo.id" class="overflow-hidden">
                        <CardHeader class="pb-3">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <CardTitle class="text-lg">{{ combo.name }}</CardTitle>
                                        <Badge v-if="combo.is_active" variant="default" class="bg-green-500">Active</Badge>
                                        <Badge v-else variant="secondary">Inactive</Badge>
                                    </div>
                                    <p v-if="combo.description" class="text-sm text-gray-600 mt-1">
                                        {{ combo.description }}
                                    </p>
                                </div>
                                
                                <div class="flex gap-1">
                                    <Link :href="`/combos/${combo.id}/edit`">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button 
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                        @click="deleteCombo(combo.id)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        
                        <CardContent class="space-y-3">
                            <!-- Pricing -->
                            <div class="flex items-baseline gap-3">
                                <div class="text-2xl font-bold text-green-600">
                                    S/ {{ Number(combo.price).toFixed(2) }}
                                </div>
                                <div class="text-sm text-gray-500 line-through">
                                    S/ {{ Number(combo.regular_price).toFixed(2) }}
                                </div>
                                <Badge variant="outline" class="text-orange-600 border-orange-300">
                                    -{{ Number(combo.discount_percentage).toFixed(0) }}%
                                </Badge>
                            </div>

                            <!-- Validity -->
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">Valid:</span> 
                                {{ formatDate(combo.start_date) }}
                                <span v-if="combo.end_date"> - {{ formatDate(combo.end_date) }}</span>
                                <span v-else> onwards</span>
                            </div>

                            <!-- Items -->
                            <div class="pt-2 border-t">
                                <div class="text-sm font-medium text-gray-700 mb-2">
                                    Includes ({{ combo.items.length }} items):
                                </div>
                                <ul class="space-y-1 text-sm text-gray-600">
                                    <li v-for="item in combo.items" :key="item.id" class="flex items-center gap-2">
                                        <Package class="w-3 h-3 text-gray-400" />
                                        <span>{{ item.quantity }}x {{ item.product.name }}</span>
                                        <Badge v-if="item.allow_substitution" variant="outline" class="text-xs">
                                            Can substitute
                                        </Badge>
                                    </li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <Package class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No combos found</p>
                        <Link href="/combos/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Create First Combo
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
