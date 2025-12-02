<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Plus, Trash2 } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface AttributeValue {
    id?: number;
    value: string;
}

interface ProductAttribute {
    id: number;
    name: string;
    values: AttributeValue[];
}

const props = defineProps<{
    attribute: ProductAttribute;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Product Attributes',
        href: '/product-attributes',
    },
    {
        title: 'Edit',
        href: `/product-attributes/${props.attribute.id}/edit`,
    },
];

const form = useForm({
    name: props.attribute.name,
    values: props.attribute.values.map(v => ({
        id: v.id,
        value: v.value,
    }))
});

const addValue = () => {
    form.values.push({ id: undefined, value: '' });
};

const removeValue = (index: number) => {
    form.values.splice(index, 1);
};

const submit = () => {
    form.put(`/product-attributes/${props.attribute.id}`);
};
</script>

<template>
    <Head title="Edit Attribute" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Product Attribute
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>General Information</CardTitle>
                            <CardDescription>Modify the attribute name.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Attribute Name</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g., Color, Size, Material" required />
                                <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Values -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Attribute Values</CardTitle>
                                <CardDescription>Manage possible values for this attribute.</CardDescription>
                            </div>
                            <Button type="button" variant="outline" size="sm" @click="addValue">
                                <Plus class="w-4 h-4 mr-2" />
                                Add Value
                            </Button>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="form.errors.values" class="text-sm text-red-500 mb-2">{{ form.errors.values }}</div>
                            
                            <div v-for="(value, index) in form.values" :key="index" class="flex items-start gap-4 p-4 border rounded-lg bg-gray-50">
                                <div class="grid gap-4 flex-1">
                                    <div class="grid gap-2">
                                        <Label :for="`value-name-${index}`">Value</Label>
                                        <Input :id="`value-name-${index}`" v-model="value.value" placeholder="e.g., Red, XL" required />
                                        <span v-if="form.errors[`values.${index}.value`]" class="text-sm text-red-500">{{ form.errors[`values.${index}.value`] }}</span>
                                    </div>
                                </div>

                                <Button type="button" variant="ghost" size="icon" class="text-red-500 hover:text-red-700 mt-8" @click="removeValue(index)" :disabled="form.values.length === 1">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                            
                            <div v-if="form.values.length === 0" class="text-center py-8 text-gray-500">
                                No values added. Click "Add Value" to start.
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4">
                        <Link href="/product-attributes">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Attribute' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
