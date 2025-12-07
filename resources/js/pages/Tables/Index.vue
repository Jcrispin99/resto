<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Edit, Trash2, UtensilsCrossed } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';

interface Area {
    id: number;
    name: string;
}

interface Branch {
    id: number;
    name: string;
}

interface Table {
    id: number;
    number: string;
    capacity: number;
    is_active: boolean;
    branch: Branch | null;
    area: Area;
}

interface Filters {
    area_id?: string;
}

const props = defineProps<{
    tables: Table[];
    areas: Area[];
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Tables', href: '/tables' },
];

const areaFilter = ref(props.filters.area_id || '');

watch(areaFilter, (newValue) => {
    router.get('/tables', { area_id: newValue }, {
        preserveState: true,
        replace: true,
    });
});

const deleteTable = (id: number) => {
    if (confirm('Are you sure you want to delete this table?')) {
        router.delete(`/tables/${id}`);
    }
};
</script>

<template>
    <Head title="Tables" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Tables
                    </h2>
                    <Link href="/tables/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            New Table
                        </Button>
                    </Link>
                </div>

                <!-- Filters -->
                <Card class="mb-6">
                    <CardContent class="pt-6">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium mb-2 block">Filter by Area</label>
                                <Select v-model="areaFilter">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Areas" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="undefined">All Areas</SelectItem>
                                        <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div v-if="tables.length > 0" class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
                    <Card v-for="table in tables" :key="table.id" class="hover:shadow-lg transition-shadow">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <UtensilsCrossed class="w-5 h-5 text-green-600" />
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900">{{ table.number }}</h3>
                                        <p class="text-sm text-gray-500">{{ table.area.name }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Link :href="`/tables/${table.id}/edit`">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button 
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                        @click="deleteTable(table.id)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Capacity:</span>
                                    <Badge variant="outline">{{ table.capacity }} persons</Badge>
                                </div>

                                <div class="mt-3">
                                    <Badge v-if="table.is_active" variant="default" class="bg-green-500">
                                        Active
                                    </Badge>
                                    <Badge v-else variant="secondary">
                                        Inactive
                                    </Badge>
                                </div>

                                <p v-if="table.branch" class="text-xs text-gray-500 mt-2">
                                    {{ table.branch.name }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <UtensilsCrossed class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No tables found</p>
                        <Link href="/tables/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Create First Table
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
