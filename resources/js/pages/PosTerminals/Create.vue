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

const props = defineProps<{
    branches: Branch[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'POS Terminals', href: '/pos-terminals' },
    { title: 'Create', href: '/pos-terminals/create' },
];

const form = useForm({
    branch_id: '',
    code: '',
    name: '',
    ip_address: '',
    printer_ip: '',
    is_active: true,
});

const submit = () => {
    form.post('/pos-terminals');
};
</script>

<template>
    <Head title="Create POS Terminal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create POS Terminal
                </h2>

                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Terminal Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="code">Code *</Label>
                                    <Input 
                                        id="code"
                                        v-model="form.code" 
                                        placeholder="POS-001"
                                        required
                                    />
                                    <span v-if="form.errors.code" class="text-sm text-red-500">
                                        {{ form.errors.code }}
                                    </span>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="name">Name *</Label>
                                    <Input 
                                        id="name"
                                        v-model="form.name" 
                                        placeholder="Caja Principal"
                                        required
                                    />
                                    <span v-if="form.errors.name" class="text-sm text-red-500">
                                        {{ form.errors.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="branch">Branch</Label>
                                <Select v-model="form.branch_id">
                                    <SelectTrigger id="branch">
                                        <SelectValue placeholder="Select branch (optional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="company in companies" :key="company.id" :value="company.id.toString()">
                                            {{ company.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="ip_address">Terminal IP</Label>
                                    <Input 
                                        id="ip_address"
                                        v-model="form.ip_address" 
                                        placeholder="192.168.1.100"
                                    />
                                    <span v-if="form.errors.ip_address" class="text-sm text-red-500">
                                        {{ form.errors.ip_address }}
                                    </span>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="printer_ip">Printer IP</Label>
                                    <Input 
                                        id="printer_ip"
                                        v-model="form.printer_ip" 
                                        placeholder="192.168.1.200"
                                    />
                                    <span v-if="form.errors.printer_ip" class="text-sm text-red-500">
                                        {{ form.errors.printer_ip }}
                                    </span>
                                </div>
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
                        <Link href="/pos-terminals">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Terminal' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
