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
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface ProductTemplate {
    id: number;
    name: string;
    category?: { name: string };
    unit?: { name: string };
    product_type: string;
    can_be_sold: boolean;
    is_active: boolean;
}

defineProps<{
    templates: {
        data: ProductTemplate[];
        links: any[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Inventario', href: '/inventory/products' },
    { title: 'Productos', href: '/inventory/products' },
];

const deleteProduct = (id: number) => {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        router.delete(`/inventory/products/${id}`);
    }
};

const getProductTypeBadge = (type: string) => {
    const types: Record<string, string> = {
        storable: 'Almacenable',
        consumable: 'Consumible',
        service: 'Servicio',
    };
    return types[type] || type;
};
</script>

<template>
    <Head title="Productos de Inventario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        📦 Productos de Inventario
                    </h2>
                    <Link href="/inventory/products/create">
                        <Button>+ Nuevo Producto</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Categoría</TableHead>
                                <TableHead>Unidad</TableHead>
                                <TableHead>Tipo</TableHead>
                                <TableHead>Vendible</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="template in templates.data" :key="template.id">
                                <TableCell class="font-medium">{{ template.name }}</TableCell>
                                <TableCell>{{ template.category?.name || '-' }}</TableCell>
                                <TableCell>{{ template.unit?.name || '-' }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ getProductTypeBadge(template.product_type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="template.can_be_sold ? 'default' : 'secondary'">
                                        {{ template.can_be_sold ? 'Sí' : 'No' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="template.is_active ? 'default' : 'secondary'">
                                        {{ template.is_active ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="`/inventory/products/${template.id}/edit`">
                                        <Button variant="outline" size="sm">Editar</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteProduct(template.id)">
                                        Eliminar
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="templates.data.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-gray-500">
                                    No se encontraron productos de inventario.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
