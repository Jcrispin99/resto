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
    menu_category?: { name: string };
    unit?: { name: string };
    sale_price: number;
    can_be_purchased: boolean;
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
    { title: 'Menú', href: '/menu/dishes' },
    { title: 'Platos', href: '/menu/dishes' },
];

const deleteDish = (id: number) => {
    if (confirm('¿Estás seguro de eliminar este plato?')) {
        router.delete(`/menu/dishes/${id}`);
    }
};
</script>

<template>
    <Head title="Platos del Menú" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        🍽️ Platos del Menú
                    </h2>
                    <Link href="/menu/dishes/create">
                        <Button>+ Nuevo Plato</Button>
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Categoría</TableHead>
                                <TableHead>Precio</TableHead>
                                <TableHead>Comprable</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="template in templates.data" :key="template.id">
                                <TableCell class="font-medium">{{ template.name }}</TableCell>
                                <TableCell>{{ template.menu_category?.name || '-' }}</TableCell>
                                <TableCell>S/. {{ Number(template.sale_price).toFixed(2) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="template.can_be_purchased ? 'default' : 'secondary'">
                                        {{ template.can_be_purchased ? 'Sí' : 'No' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="template.is_active ? 'default' : 'secondary'">
                                        {{ template.is_active ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="`/menu/dishes/${template.id}/edit`">
                                        <Button variant="outline" size="sm">Editar</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="deleteDish(template.id)">
                                        Eliminar
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="templates.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                    No se encontraron platos en el menú.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
