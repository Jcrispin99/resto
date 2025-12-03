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
import { create, edit, destroy, index } from '@/routes/units';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Unit {
    id: number;
    code: string;
    name: string;
    abbreviation: string;
    type: string;
    is_active: boolean;
}

defineProps<{
    units: {
        data: Unit[];
        meta: any;
        links: any;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Units',
        href: index.url(),
    },
];

const deleteUnit = (id: number) => {
    if (confirm('Are you sure you want to delete this unit?')) {
        router.delete(destroy.url(id));
    }
};

const getTypeLabel = (type: string) => {
    const types: Record<string, string> = {
        'weight': 'Weight',
        'volume': 'Volume',
        'length': 'Length',
        'unit': 'Unit',
    };
    return types[type] || type;
};
</script>

<template>
    <Head title="Units" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Units
                    </h2>
                    <Link :href="create.url()">
                        <Button>Create Unit</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Abbreviation</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="unit in units.data" :key="unit.id">
                                <TableCell class="font-medium">{{ unit.code }}</TableCell>
                                <TableCell>{{ unit.name }}</TableCell>
                                <TableCell>{{ unit.abbreviation }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ getTypeLabel(unit.type) }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="unit.is_active ? 'default' : 'secondary'">
                                        {{ unit.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="edit.url(unit.id)">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteUnit(unit.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="units.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                    No units found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center space-x-2" v-if="units.meta.last_page > 1">
                        <Link v-for="link in units.meta.links" 
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
