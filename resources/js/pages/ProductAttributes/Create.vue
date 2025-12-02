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
        title: 'Create',
        href: '/product-attributes/create',
    },
];

interface AttributeValueForm {
    value: string;
}

const form = useForm({
    name: '',
    values: [
        { value: '' }
    ] as AttributeValueForm[]
});

const addValue = () => {
    form.values.push({ value: '' });
};

const removeValue = (index: number) => {
    form.values.splice(index, 1);
};

const submit = () => {
    form.post('/product-attributes');
};
</script>

<template>
    <Head title="Create Attribute" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Product Attribute
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- General Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>General Information</CardTitle>
                            <CardDescription>Define the attribute name.</CardDescription>
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
                                <CardDescription>Add possible values for this attribute.</CardDescription>
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
                            {{ form.processing ? 'Creating...' : 'Create Attribute' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
