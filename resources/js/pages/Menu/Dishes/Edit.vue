<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

interface MenuSettings {
    enabled?: boolean;
    menu_name?: string;
    menu_description?: string;
    preparation_time_minutes?: number;
    is_featured?: boolean;
    is_spicy?: boolean;
    is_vegetarian?: boolean;
    is_vegan?: boolean;
    is_gluten_free?: boolean;
}

interface ProductTemplate {
    id: number;
    name: string;
    description: string | null;
    menu_category_id: number | null;
    category_id: number | null;
    unit_id: number;
    product_type: string;
    sale_price: number;
    can_be_purchased: boolean;
    can_be_stocked: boolean;
    menu_settings: MenuSettings | null;
}

const props = defineProps<{
    template: ProductTemplate;
    menuCategories: Category[];
    inventoryCategories: Category[];
    units: Unit[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Menú', href: '/menu/dishes' },
    { title: 'Editar', href: `/menu/dishes/${props.template.id}/edit` },
];

const form = useForm({
    name: props.template.name,
    description: props.template.description || '',
    menu_category_id: props.template.menu_category_id?.toString() || '',
    category_id: props.template.category_id?.toString() || null,
    unit_id: props.template.unit_id.toString(),
    product_type: props.template.product_type,
    sale_price: props.template.sale_price,
    can_be_purchased: props.template.can_be_purchased,
    can_be_stocked: props.template.can_be_stocked,
    attribute_lines: [] as any[],
    menu_settings: {
        enabled: !!props.template.menu_settings,
        menu_name: props.template.menu_settings?.menu_name || '',
        menu_description: props.template.menu_settings?.menu_description || '',
        preparation_time_minutes: props.template.menu_settings?.preparation_time_minutes || 0,
        is_featured: props.template.menu_settings?.is_featured || false,
        is_spicy: props.template.menu_settings?.is_spicy || false,
        is_vegetarian: props.template.menu_settings?.is_vegetarian || false,
        is_vegan: props.template.menu_settings?.is_vegan || false,
        is_gluten_free: props.template.menu_settings?.is_gluten_free || false,
    },
});

const submit = () => {
    form.put(`/menu/dishes/${props.template.id}`);
};
</script>

<template>
    <Head title="Editar Plato" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    🍽️ Editar: {{ template.name }}
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Plato</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Nombre *</Label>
                                <Input id="name" v-model="form.name" required />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="menu_category">Categoría de Menú *</Label>
                                    <Select v-model="form.menu_category_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="cat in menuCategories" :key="cat.id" :value="cat.id.toString()">
                                                {{ cat.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="unit">Unidad *</Label>
                                    <Select v-model="form.unit_id">
                                        <SelectTrigger>
                                            <SelectValue />
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
                                <Input id="sale_price" v-model.number="form.sale_price" type="number" step="0.01" min="0" required />
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Descripción</Label>
                                <Textarea id="description" v-model="form.description" rows="2" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Configuración de Inventario</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="can_be_purchased" v-model:checked="form.can_be_purchased" />
                                <Label for="can_be_purchased" class="cursor-pointer">
                                    Se compra a proveedores
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

                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Configuración de Menú Digital</CardTitle>
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="menu_enabled" v-model:checked="form.menu_settings.enabled" />
                                    <Label for="menu_enabled" class="cursor-pointer">Habilitar</Label>
                                </div>
                            </div>
                        </CardHeader>
                        
                        <CardContent v-if="form.menu_settings.enabled" class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="prep_time">Tiempo de preparación (min)</Label>
                                <Input id="prep_time" v-model.number="form.menu_settings.preparation_time_minutes" type="number" min="0" />
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
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
