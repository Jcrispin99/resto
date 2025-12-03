<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { update, index } from '@/routes/units';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Unit {
    id: number;
    code: string;
    name: string;
    abbreviation: string;
    type: string;
    is_active: boolean;
}

interface Props {
    unit: {
        data: Unit;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Units',
        href: index.url(),
    },
    {
        title: 'Edit',
        href: `/units/${props.unit.data.id}/edit`,
    },
];

const unitTypes = [
    { value: 'weight', label: 'Weight (kg, g, lb, oz)' },
    { value: 'volume', label: 'Volume (L, ml, gal)' },
    { value: 'length', label: 'Length (m, cm, ft, in)' },
    { value: 'unit', label: 'Unit (piece, box, dozen)' },
];

const form = useForm({
    code: props.unit.data.code,
    name: props.unit.data.name,
    abbreviation: props.unit.data.abbreviation,
    type: props.unit.data.type,
    is_active: Boolean(props.unit.data.is_active),
});

const submit = () => {
    form.put(update.url(props.unit.data.id));
};
</script>

<template>
    <Head title="Edit Unit" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Unit
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Code -->
                            <div class="space-y-2">
                                <Label for="code">Code *</Label>
                                <Input id="code" v-model="form.code" required maxlength="10" />
                                <div v-if="form.errors.code" class="text-red-500 text-sm">
                                    {{ form.errors.code }}
                                </div>
                            </div>

                            <!-- Abbreviation -->
                            <div class="space-y-2">
                                <Label for="abbreviation">Abbreviation *</Label>
                                <Input id="abbreviation" v-model="form.abbreviation" required maxlength="10" />
                                <div v-if="form.errors.abbreviation" class="text-red-500 text-sm">
                                    {{ form.errors.abbreviation }}
                                </div>
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

                        <!-- Type -->
                        <div class="space-y-2">
                            <Label for="type">Type *</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="type in unitTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.type" class="text-red-500 text-sm">
                                {{ form.errors.type }}
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
                                {{ form.processing ? 'Updating...' : 'Update Unit' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
