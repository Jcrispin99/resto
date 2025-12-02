<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
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

interface TemplateAttributeLine {
    id: number;
    attribute_id: number;
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

interface ProductTemplate {
    id: number;
    name: string;
    category_id: number | null;
    unit_id: number;
    attribute_lines: TemplateAttributeLine[];
}

const props = defineProps<{
    template: ProductTemplate;
    attributes: ProductAttribute[];
    categories: Category[];
    units: Unit[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Products',
        href: '/product-templates',
    },
    {
        title: 'Edit',
        href: `/product-templates/${props.template.id}/edit`,
    },
];

interface AttributeLineForm {
    id?: number;
    attribute_id: string;
    value_ids: number[];
    showDropdown?: boolean;
}

const form = useForm({
    name: props.template.name,
    category_id: props.template.category_id ? props.template.category_id.toString() : null,
    unit_id: props.template.unit_id.toString(),
    attribute_lines: props.template.attribute_lines.map(line => ({
        id: line.id,
        attribute_id: line.attribute_id.toString(),
        value_ids: line.values.map(v => v.id),
        showDropdown: false
    })) as AttributeLineForm[],
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
    form.put(`/product-templates/${props.template.id}`);
};
</script>

<template>
    <Head title="Edit Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Product
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>General Information</CardTitle>
                            <CardDescription>Basic product details.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Product Name</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g., T-Shirt, Pizza" required />
                                <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="category">Category</Label>
                                    <Select v-model="form.category_id">
                                        <SelectTrigger id="category">
                                            <SelectValue placeholder="Select Category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()">
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span v-if="form.errors.category_id" class="text-sm text-red-500">{{ form.errors.category_id }}</span>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="unit">Unit</Label>
                                    <Select v-model="form.unit_id">
                                        <SelectTrigger id="unit">
                                            <SelectValue placeholder="Select Unit" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="unit in units" :key="unit.id" :value="unit.id.toString()">
                                                {{ unit.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span v-if="form.errors.unit_id" class="text-sm text-red-500">{{ form.errors.unit_id }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attributes & Variants -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Attributes & Variants</CardTitle>
                                <CardDescription>Define attributes to generate variants (e.g., Size, Color).</CardDescription>
                            </div>
                            <Button type="button" variant="outline" size="sm" @click="addAttributeLine">
                                <Plus class="w-4 h-4 mr-2" />
                                Add Attribute
                            </Button>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="form.errors.attribute_lines" class="text-sm text-red-500 mb-2">{{ form.errors.attribute_lines }}</div>
                            
                            <div v-for="(line, index) in form.attribute_lines" :key="index" class="p-4 border rounded-lg bg-gray-50 relative">
                                <Button type="button" variant="ghost" size="icon" class="absolute top-2 right-2 text-red-500 hover:text-red-700" @click="removeAttributeLine(index)">
                                    <Trash2 class="w-4 h-4" />
                                </Button>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label :for="`attr-${index}`">Attribute</Label>
                                        <Select v-model="line.attribute_id">
                                            <SelectTrigger :id="`attr-${index}`">
                                                <SelectValue placeholder="Select Attribute" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="attr in attributes" :key="attr.id" :value="attr.id.toString()">
                                                    {{ attr.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <span v-if="form.errors[`attribute_lines.${index}.attribute_id`]" class="text-sm text-red-500">
                                            {{ form.errors[`attribute_lines.${index}.attribute_id`] }}
                                        </span>
                                    </div>

                                    <div class="grid gap-2" v-if="line.attribute_id">
                                        <Label>Values</Label>
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
                                                <span v-else class="text-muted-foreground">Select values...</span>
                                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                            </Button>
                                            <div 
                                                v-if="line.showDropdown" 
                                                class="absolute z-50 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md outline-none"
                                            >
                                                <div class="p-2 max-h-60 overflow-auto">
                                                    <div 
                                                        v-for="val in getAttributeValues(line.attribute_id)" 
                                                        :key="val.id"
                                                        class="flex items-center space-x-2 rounded-sm px-2 py-1.5 hover:bg-accent cursor-pointer"
                                                        @click.stop="toggleValue(line, val.id)"
                                                    >
                                                        <Checkbox 
                                                            :checked="line.value_ids.includes(val.id)"
                                                            @click.stop
                                                        />
                                                        <span class="text-sm">{{ val.value }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <span v-if="form.errors[`attribute_lines.${index}.value_ids`]" class="text-sm text-red-500">
                                            {{ form.errors[`attribute_lines.${index}.value_ids`] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="form.attribute_lines.length === 0" class="text-center py-8 text-gray-500">
                                No attributes added. This product will have a single default variant.
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4">
                        <Link href="/product-templates">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Product' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
