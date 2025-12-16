<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {Badge } from '@/components/ui/badge';
import { Plus, Edit, Trash2, ChefHat } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Branch {
    id: number;
    name: string;
}

interface KitchenStation {
    id: number;
    name: string;
    description: string | null;
    branch: Branch;
    printer_ip: string | null;
    is_active: boolean;
}

const props = defineProps<{
    stations: {
        data: KitchenStation[];
        links: any;
        meta: any;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Estaciones de Cocina', href: '/kitchen-stations' },
];

const deleteStation = (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta estación de cocina?')) {
        router.delete(`/kitchen-stations/${id}`);
    }
};
</script>

<template>
    <Head title="Estaciones de Cocina" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Estaciones de Cocina
                    </h2>
                    <Link href="/kitchen-stations/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            Nueva Estación
                        </Button>
                    </Link>
                </div>

                <div v-if="stations.data.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="station in stations.data" :key="station.id">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <ChefHat class="w-5 h-5 text-gray-600" />
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ station.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ station.branch.name }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Link :href="`/kitchen-stations/${station.id}/edit`">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button 
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                        @click="deleteStation(station.id)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                            
                            <p v-if="station.description" class="text-sm text-gray-600 mb-3">
                                {{ station.description }}
                            </p>
                            
                            <div class="flex gap-2 mt-3 flex-wrap">
                                <Badge v-if="station.is_active" variant="default" class="bg-green-500">
                                    Activa
                                </Badge>
                                <Badge v-else variant="secondary">
                                    Inactiva
                                </Badge>
                                <Badge v-if="station.printer_ip" variant="outline">
                                    {{ station.printer_ip }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <ChefHat class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No hay estaciones de cocina configuradas</p>
                        <Link href="/kitchen-stations/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Crear Primera Estación
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
