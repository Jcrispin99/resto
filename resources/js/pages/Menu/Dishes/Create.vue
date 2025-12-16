<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Category {
    id: number;
    name: string;
}

interface Unit {
    id: number;
    name: string;
}

const props = defineProps<{
    menuCategories: Category[];
    inventoryCategories: Category[];
    units: Unit[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Menú', href: '/menu/dishes' },
    { title: 'Nuevo Plato', href: '/menu/dishes/create' },
];

const form = useForm({
    name: '',
    description: '',
    menu_category_id: '' as string,
    category_id: null as string | null,
    unit_id: '' as string,
    product_type: 'consumable',
    sale_price: 0,
    can_be_purchased: false,
    can_be_stocked: false,
    attribute_lines: [] as any[],
    menu_settings: {
        enabled: false,
        menu_name: '',
        menu_description: '',
        preparation_time_minutes: 0,
        is_featured: false,
        is_spicy: false,
        is_vegetarian: false,
        is_vegan: false,
        is_gluten_free: false,
    },
});

const submit = () => {
    form.post('/menu/dishes');
};
</script>

<template>
    <Head title="Nuevo Plato" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    🍽️ Nuevo Plato del Menú
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Información General -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Plato</CardTitle>
                            <CardDescription>Datos básicos para el menú</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Nombre *</Label>
                                <Input id="name" v-model="form.name" placeholder="ej: Lomo Saltado, Ceviche" required />
                                <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="menu_category">Categoría de Menú *</Label>
                                    <Select v-model="form.menu_category_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar categoría" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="cat in menuCategories" :key="cat.id" :value="cat.id.toString()">
                                                {{ cat.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span v-if="form.errors.menu_category_id" class="text-sm text-red-500">{{ form.errors.menu_category_id }}</span>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="unit">Unidad *</Label>
                                    <Select v-model="form.unit_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar unidad" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="unit in units" :key="unit.id" :value="unit.id.toString()">
                                                {{ unit.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="sale_price">Precio de Venta *</Label>
                                <Input 
                                    id="sale_price" 
                                    v-model.number="form.sale_price" 
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    required
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Descripción</Label>
                                <Textarea id="description" v-model="form.description" placeholder="Descripción del plato" rows="2" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Configuración de Inventario -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Configuración de Inventario</CardTitle>
                            <CardDescription>¿Este plato también es un producto de inventario?</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="can_be_purchased" v-model:checked="form.can_be_purchased" />
                                <Label for="can_be_purchased" class="cursor-pointer">
                                    Se compra a proveedores (ej: bebidas)
                                </Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="can_be_stocked" v-model:checked="form.can_be_stocked" />
                                <Label for="can_be_stocked" class="cursor-pointer">
                                    Gestionar stock
                                </Label>
                            </div>

                            <div v-if="form.can_be_purchased" class="grid gap-2 pl-6 border-l-2 border-blue-200">
                                <Label for="category">Categoría de Inventario</Label>
                                <Select v-model="form.category_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Opcional" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="cat in inventoryCategories" :key="cat.id" :value="cat.id.toString()">
                                            {{ cat.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Menu Settings -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Configuración de Menú Digital</CardTitle>
                                    <CardDescription>Información adicional para el menú digital</CardDescription>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="menu_enabled" v-model:checked="form.menu_settings.enabled" />
                                    <Label for="menu_enabled" class="cursor-pointer">Habilitar</Label>
                                </div>
                            </div>
                        </CardHeader>
                        
                        <CardContent v-if="form.menu_settings.enabled" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="prep_time">Tiempo de preparación (min)</Label>
                                    <Input id="prep_time" v-model.number="form.menu_settings.preparation_time_minutes" type="number" min="0" />
                                </div>
                            </div>

                            <div>
                                <Label class="text-sm">Información dietética</Label>
                                <div class="grid grid-cols-2 gap-3 mt-2">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="is_spicy" v-model:checked="form.menu_settings.is_spicy" />
                                        <Label for="is_spicy" class="cursor-pointer text-sm">🌶️ Picante</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="is_vegetarian" v-model:checked="form.menu_settings.is_vegetarian" />
                                        <Label for="is_vegetarian" class="cursor-pointer text-sm">🥗 Vegetariano</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="is_vegan" v-model:checked="form.menu_settings.is_vegan" />
                                        <Label for="is_vegan" class="cursor-pointer text-sm">🌱 Vegano</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox id="is_gluten_free" v-model:checked="form.menu_settings.is_gluten_free" />
                                        <Label for="is_gluten_free" class="cursor-pointer text-sm">🌾 Sin Gluten</Label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_featured" v-model:checked="form.menu_settings.is_featured" />
                                <Label for="is_featured" class="cursor-pointer">⭐ Plato destacado</Label>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4">
                        <Link href="/menu/dishes">
                            <Button type="button" variant="outline">Cancelar</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creando...' : 'Crear Plato' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
