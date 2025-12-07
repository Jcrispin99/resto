<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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

defineProps<{
    branches: Branch[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Table Areas', href: '/table-areas' },
    { title: 'Create', href: '/table-areas/create' },
];

const form = useForm({
    branch_id: '',
    name: '',
    description: '',
    order: 0,
    is_active: true,
});

const submit = () => {
    form.post('/table-areas');
};
</script>

<template>
    <Head title="Create Table Area" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Table Area
                </h2>

                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Area Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="branch">Branch</Label>
                                <Select v-model="form.branch_id">
                                    <SelectTrigger id="branch">
                                        <SelectValue placeholder="Select branch (optional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="branch in branches" :key="branch.id" :value="branch.id.toString()">
                                            {{ branch.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid gap-2">
                                <Label for="name">Name *</Label>
                                <Input 
                                    id="name"
                                    v-model="form.name" 
                                    placeholder="Salón Principal, Terraza, Bar"
                                    required
                                />
                                <span v-if="form.errors.name" class="text-sm text-red-500">
                                    {{ form.errors.name }}
                                </span>
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Description</Label>
                                <Textarea 
                                    id="description"
                                    v-model="form.description" 
                                    rows="3"
                                    placeholder="Describe this area..."
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="order">Display Order</Label>
                                <Input 
                                    id="order"
                                    v-model.number="form.order" 
                                    type="number"
                                    min="0"
                                />
                                <p class="text-xs text-gray-500">Lower numbers appear first</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Checkbox id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active" class="cursor-pointer">
                                    Active
                                </Label>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4 mt-6">
                        <Link href="/table-areas">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Area' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
