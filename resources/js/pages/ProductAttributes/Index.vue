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

interface AttributeValue {
    id: number;
    value: string;
}

interface ProductAttribute {
    id: number;
    name: string;
    values: AttributeValue[];
}

defineProps<{
    attributes: ProductAttribute[];
}>();

import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Product Attributes',
        href: '/product-attributes',
    },
];

const deleteAttribute = (id: number) => {
    if (confirm('Are you sure you want to delete this attribute?')) {
        router.delete(`/product-attributes/${id}`);
    }
};
</script>

<template>
    <Head title="Product Attributes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Product Attributes
                    </h2>
                    <Link href="/product-attributes/create">
                        <Button>Create Attribute</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Values</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="attribute in attributes" :key="attribute.id">
                                <TableCell class="font-medium">{{ attribute.name }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="val in attribute.values" :key="val.id" variant="secondary" class="text-xs">
                                            {{ val.value }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="`/product-attributes/${attribute.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteAttribute(attribute.id)">
                                        Delete
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="attributes.length === 0">
                                <TableCell colspan="3" class="text-center py-8 text-gray-500">
                                    No attributes found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
