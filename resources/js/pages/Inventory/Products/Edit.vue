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
import { Plus, Trash2, ChevronsUpDown } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface AttributeValue {
    id: number;
    value: string;
}

interface ProductAttribute {
    id: number;
    name: string;
    values: AttributeValue[];
}

interface Category {
    id: number;
    name: string;
}

interface Unit {
    id: number;
    name: string;
}

interface AttributeLine {
    id?: number;
    attribute_id: number;
    values: AttributeValue[];
}

interface ProductTemplate {
    id: number;
    name: string;
    description: string | null;
    category_id: number | null;
    unit_id: number;
    product_type: string;
    sale_price: number;
    can_be_sold: boolean;
    can_be_stocked: boolean;
    attribute_lines: AttributeLine[];
}

const props = defineProps<{
    template: ProductTemplate;
    attributes: ProductAttribute[];
    categories: Category[];
    units: Unit[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Inventario', href: '/inventory/products' },
    { title: 'Editar', href: `/inventory/products/${props.template.id}/edit` },
];

interface AttributeLineForm {
    attribute_id: string;
    value_ids: number[];
    showDropdown?: boolean;
}

// Transform existing attribute lines to form format
const initialAttributeLines: AttributeLineForm[] = props.template.attribute_lines?.map(line => ({
    attribute_id: line.attribute_id.toString(),
    value_ids: line.values?.map(v => v.id) || [],
    showDropdown: false,
})) || [];

const form = useForm({
    name: props.template.name,
    description: props.template.description || '',
    category_id: props.template.category_id?.toString() || '',
    unit_id: props.template.unit_id.toString(),
    product_type: props.template.product_type,
    sale_price: props.template.sale_price,
    can_be_sold: props.template.can_be_sold,
    can_be_stocked: props.template.can_be_stocked,
    attribute_lines: initialAttributeLines,
});

const addAttributeLine = () => {
    form.attribute_lines.push({ attribute_id: '', value_ids: [], showDropdown: false });
};

const removeAttributeLine = (index: number) => {
    form.attribute_lines.splice(index, 1);
};

const getAttributeValues = (attributeId: string) => {
    const attr = props.attributes.find(a => a.id.toString() === attributeId);
    return attr ? attr.values : [];
};

const toggleValue = (line: AttributeLineForm, valueId: number) => {
    const index = line.value_ids.indexOf(valueId);
    if (index > -1) {
        line.value_ids.splice(index, 1);
    } else {
        line.value_ids.push(valueId);
    }
};

const submit = () => {
    form.put(`/inventory/products/${props.template.id}`);
};
</script>

<template>
    <Head title="Editar Producto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    📦 Editar Producto: {{ template.name }}
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Información General -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Información General</CardTitle>
                            <CardDescription>Datos básicos del producto</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Nombre *</Label>
                                <Input id="name" v-model="form.name" required />
                                <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="category">Categoría *</Label>
                                    <Select v-model="form.category_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar categoría" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                                                {{ cat.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
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
                                <Label for="product_type">Tipo de Producto</Label>
                                <Select v-model="form.product_type">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="storable">Almacenable (con stock)</SelectItem>
                                        <SelectItem value="consumable">Consumible (sin seguimiento)</SelectItem>
                                        <SelectItem value="service">Servicio</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Descripción</Label>
                                <Textarea id="description" v-model="form.description" rows="2" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Configuración de Venta -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Configuración de Venta</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="can_be_sold" v-model:checked="form.can_be_sold" />
                                <Label for="can_be_sold" class="cursor-pointer">
                                    También se vende en menú
                                </Label>
                            </div>

                            <div v-if="form.can_be_sold" class="grid gap-2 pl-6 border-l-2 border-blue-200">
                                <Label for="sale_price">Precio de Venta</Label>
                                <Input 
                                    id="sale_price" 
                                    v-model.number="form.sale_price" 
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                />
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="can_be_stocked" v-model:checked="form.can_be_stocked" />
                                <Label for="can_be_stocked" class="cursor-pointer">
                                    Gestionar stock
                                </Label>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Atributos y Variantes -->
                    <Card v-if="form.can_be_stocked">
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Atributos y Variantes</CardTitle>
                                <CardDescription>Define atributos para generar variantes</CardDescription>
                            </div>
                            <Button type="button" variant="outline" size="sm" @click="addAttributeLine">
                                <Plus class="w-4 h-4 mr-2" />
                                Agregar
                            </Button>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-for="(line, index) in form.attribute_lines" :key="index" class="p-4 border rounded-lg bg-gray-50 relative">
                                <Button type="button" variant="ghost" size="icon" class="absolute top-2 right-2 text-red-500" @click="removeAttributeLine(index)">
                                    <Trash2 class="w-4 h-4" />
                                </Button>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label>Atributo</Label>
                                        <Select v-model="line.attribute_id">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Seleccionar" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="attr in attributes" :key="attr.id" :value="attr.id.toString()">
                                                    {{ attr.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="grid gap-2" v-if="line.attribute_id">
                                        <Label>Valores</Label>
                                        <div class="relative">
                                            <Button 
                                                type="button"
                                                variant="outline" 
                                                class="w-full justify-between font-normal"
                                                @click="line.showDropdown = !line.showDropdown"
                                            >
                                                <span v-if="line.value_ids.length > 0" class="truncate">
                                                    {{ getAttributeValues(line.attribute_id).filter(v => line.value_ids.includes(v.id)).map(v => v.value).join(', ') }}
                                                </span>
                                                <span v-else class="text-muted-foreground">Seleccionar...</span>
                                                <ChevronsUpDown class="ml-2 h-4 w-4 opacity-50" />
                                            </Button>
                                            <div 
                                                v-if="line.showDropdown" 
                                                class="absolute z-50 mt-1 w-full rounded-md border bg-popover shadow-md"
                                            >
                                                <div class="p-2 max-h-60 overflow-auto">
                                                    <div 
                                                        v-for="val in getAttributeValues(line.attribute_id)" 
                                                        :key="val.id"
                                                        class="flex items-center space-x-2 rounded-sm px-2 py-1.5 hover:bg-accent cursor-pointer"
                                                        @click.stop="toggleValue(line, val.id)"
                                                    >
                                                        <Checkbox :checked="line.value_ids.includes(val.id)" @click.stop />
                                                        <span class="text-sm">{{ val.value }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="form.attribute_lines.length === 0" class="text-center py-8 text-gray-500">
                                Sin atributos. El producto tendrá una sola variante por defecto.
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4">
                        <Link href="/inventory/products">
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
