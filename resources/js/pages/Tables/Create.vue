<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Branch {
    id: number;
    name: string;
}

interface Area {
    id: number;
    name: string;
}

defineProps<{
    branches: Branch[];
    areas: Area[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Tables', href: '/tables' },
    { title: 'Create', href: '/tables/create' },
];

const form = useForm({
    branch_id: '',
    area_id: '',
    number: '',
    capacity: 4,
    is_active: true,
});

const submit = () => {
    form.post('/tables');
};
</script>

<template>
    <Head title="Create Table" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Table
                </h2>

                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Table Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="area">Area *</Label>
                                <Select v-model="form.area_id">
                                    <SelectTrigger id="area">
                                        <SelectValue placeholder="Select area" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.area_id" class="text-sm text-red-500">
                                    {{ form.errors.area_id }}
                                </span>
                            </div>

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
                                <Label for="number">Table Number *</Label>
                                <Input 
                                    id="number"
                                    v-model="form.number" 
                                    placeholder="M1, T1, B1"
                                    required
                                />
                                <span v-if="form.errors.number" class="text-sm text-red-500">
                                    {{ form.errors.number }}
                                </span>
                            </div>

                            <div class="grid gap-2">
                                <Label for="capacity">Capacity (persons) *</Label>
                                <Input 
                                    id="capacity"
                                    v-model.number="form.capacity" 
                                    type="number"
                                    min="1"
                                    max="20"
                                    required
                                />
                                <span v-if="form.errors.capacity" class="text-sm text-red-500">
                                    {{ form.errors.capacity }}
                                </span>
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
                        <Link href="/tables">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Table' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
