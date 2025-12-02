<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { update, index } from '@/routes/product-categories';

const props = defineProps<{
    category: { 
        data: {
            id: number; 
            name: string; 
            parent_id: number | null; 
            is_active: boolean; 
        }
    };
    parents: { data: Array<{ id: number; name: string }> };
}>();

const form = useForm({
    name: props.category.data.name,
    parent_id: props.category.data.parent_id ? String(props.category.data.parent_id) : null,
    is_active: Boolean(props.category.data.is_active),
});

import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Product Categories',
        href: index.url(),
    },
    {
        title: 'Edit',
        href: `/product-categories/${props.category.data.id}/edit`,
    },
];

const submit = () => {
    form.put(update.url(props.category.data.id));
};
</script>

<template>
    <Head title="Edit Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Product Category
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 max-w-lg mx-auto">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label for="parent">Parent Category</Label>
                            <Select v-model="form.parent_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a parent category (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">None</SelectItem>
                                    <SelectItem v-for="parent in parents.data" :key="parent.id" :value="String(parent.id)">
                                        {{ parent.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.parent_id" class="text-red-500 text-sm">{{ form.errors.parent_id }}</div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" :checked="form.is_active" @update:checked="form.is_active = $event" />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <Link :href="index.url()">
                                <Button variant="outline" type="button">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Category' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
