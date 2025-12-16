<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Branch {
    id: number;
    name: string;
}

const props = defineProps<{
    branches: Branch[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Estaciones de Cocina', href: '/kitchen-stations' },
    { title: 'Crear', href: '/kitchen-stations/create' },
];

const form = useForm({
    name: '',
    branch_id: props.branches[0]?.id || null,
    description: '',
    printer_ip: '',
    order: 0,
    is_active: true,
});

const submit = () => {
    form.post('/kitchen-stations');
};
</script>

<template>
    <Head title="Crear Estación de Cocina" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Nueva Estación de Cocina</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="name">Nombre *</Label>
                                <Input 
                                    id="name"
                                    v-model="form.name" 
                                    type="text"
                                    placeholder="ej: Cocina Caliente"
                                    required
                                />
                                <p v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="branch_id">Sucursal *</Label>
                                <Select v-model="form.branch_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona una sucursal" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="branch in branches" :key="branch.id" :value="branch.id.toString()">
                                            {{ branch.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.branch_id" class="text-sm text-red-600">
                                    {{ form.errors.branch_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Descripción</Label>
                                <Textarea 
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Descripción opcional de la estación"
                                    rows="3"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="printer_ip">IP de Impresora</Label>
                                <Input 
                                    id="printer_ip"
                                    v-model="form.printer_ip"
                                    type="text"
                                    placeholder="ej: 192.168.1.10"
                                />
                                <p v-if="form.errors.printer_ip" class="text-sm text-red-600">
                                    {{ form.errors.printer_ip }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="order">Orden de Visualización</Label>
                                <Input 
                                    id="order"
                                    v-model.number="form.order"
                                    type="number"
                                    min="0"
                                />
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox 
                                    id="is_active" 
                                    :checked="form.is_active"
                                    @update:checked="form.is_active = $event"
                                />
                                <Label for="is_active" class="cursor-pointer">
                                    Estación activa
                                </Label>
                            </div>

                            <div class="flex gap-2">
                                <Button type="submit" :disabled="form.processing">
                                    Crear Estación
                                </Button>
                                <Button 
                                    type="button" 
                                    variant="outline"
                                    @click="$inertia.visit('/kitchen-stations')"
                                >
                                    Cancelar
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
