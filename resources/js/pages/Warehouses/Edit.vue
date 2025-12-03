<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { update, index } from '@/routes/warehouses';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Warehouse {
    id: number;
    branch_id: number;
    code: string;
    name: string;
    is_active: boolean;
}

interface Company {
    id: number;
    name: string;
    code?: string;
}

interface Props {
    warehouse: {
        data: Warehouse;
    };
    branches?: {
        data: Company[];
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Warehouses',
        href: index.url(),
    },
    {
        title: 'Edit',
        href: `/warehouses/${props.warehouse.data.id}/edit`,
    },
];

const form = useForm({
    branch_id: props.warehouse.data.branch_id,
    code: props.warehouse.data.code,
    name: props.warehouse.data.name,
    is_active: Boolean(props.warehouse.data.is_active),
});

const submit = () => {
    form.put(update.url(props.warehouse.data.id));
};
</script>

<template>
    <Head title="Edit Warehouse" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Warehouse
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Branch Selector -->
                        <div class="space-y-2">
                            <Label for="branch_id">Branch *</Label>
                            <Select v-model="form.branch_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select branch" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="branch in props.branches?.data" 
                                        :key="branch.id" 
                                        :value="branch.id">
                                        {{ branch.name }} {{ branch.code ? `(${branch.code})` : '' }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.branch_id" class="text-red-500 text-sm">
                                {{ form.errors.branch_id }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Code -->
                            <div class="space-y-2">
                                <Label for="code">Code *</Label>
                                <Input id="code" v-model="form.code" required maxlength="20" />
                                <div v-if="form.errors.code" class="text-red-500 text-sm">
                                    {{ form.errors.code }}
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name">Name *</Label>
                                <Input id="name" v-model="form.name" required maxlength="100" />
                                <div v-if="form.errors.name" class="text-red-500 text-sm">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                        </div>

                        <!-- Active Checkbox -->
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" 
                                      :checked="form.is_active" 
                                      @update:checked="form.is_active = $event" />
                            <Label for="is_active">Active</Label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-2 pt-4">
                            <Link :href="index.url()">
                                <Button variant="outline" type="button">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Warehouse' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
