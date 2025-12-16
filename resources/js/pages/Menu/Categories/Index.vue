<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

defineProps<{
    categories: {
        data: Array<{
            id: number;
            name: string;
            parent?: { name: string };
            is_active: boolean;
        }>;
        links: any[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Menú', href: '/menu/dishes' },
    { title: 'Categorías', href: '/menu/categories' },
];

const deleteCategory = (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta categoría?')) {
        router.delete(`/menu/categories/${id}`);
    }
};
</script>

<template>
    <Head title="Categorías de Menú" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        🍽️ Categorías de Menú
                    </h2>
                    <Link href="/menu/categories/create">
                        <Button>+ Nueva Categoría</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Categoría Padre</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="category in categories.data" :key="category.id">
                                <TableCell class="font-medium">{{ category.name }}</TableCell>
                                <TableCell>{{ category.parent?.name || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="category.is_active ? 'default' : 'secondary'">
                                        {{ category.is_active ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="`/menu/categories/${category.id}/edit`">
                                        <Button variant="outline" size="sm">Editar</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteCategory(category.id)">
                                        Eliminar
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="categories.data.length === 0">
                                <TableCell colspan="4" class="text-center py-8 text-gray-500">
                                    No hay categorías de menú.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
