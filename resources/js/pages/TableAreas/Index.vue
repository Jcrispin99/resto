<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Plus, Edit, Trash2, LayoutGrid } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Branch {
    id: number;
    name: string;
}

interface TableArea {
    id: number;
    name: string;
    description: string | null;
    order: number;
    is_active: boolean;
    branch: Branch | null;
    tables_count: number;
}

const props = defineProps<{
    areas: TableArea[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Table Areas', href: '/table-areas' },
];

const deleteArea = (id: number) => {
    if (confirm('Are you sure you want to delete this area?')) {
        router.delete(`/table-areas/${id}`);
    }
};
</script>

<template>
    <Head title="Table Areas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Table Areas
                    </h2>
                    <Link href="/table-areas/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            New Area
                        </Button>
                    </Link>
                </div>

                <div v-if="areas.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="area in areas" :key="area.id">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <LayoutGrid class="w-5 h-5 text-blue-600" />
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ area.name }}</h3>
                                        <p v-if="area.description" class="text-sm text-gray-500">{{ area.description }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Link :href="`/table-areas/${area.id}/edit`">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button 
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                        @click="deleteArea(area.id)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="flex gap-2 mt-3">
                                <Badge v-if="area.is_active" variant="default" class="bg-green-500">
                                    Active
                                </Badge>
                                <Badge v-else variant="secondary">
                                    Inactive
                                </Badge>
                                <Badge variant="outline">
                                    {{ area.tables_count }} tables
                                </Badge>
                            </div>

                            <p v-if="area.branch" class="text-xs text-gray-500 mt-2">
                                Branch: {{ area.branch.name }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <LayoutGrid class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No table areas found</p>
                        <Link href="/table-areas/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Create First Area
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
