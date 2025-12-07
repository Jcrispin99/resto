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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Payment Methods', href: '/payment-methods' },
    { title: 'Create', href: '/payment-methods/create' },
];

const form = useForm({
    code: '',
    name: '',
    type: 'cash',
    requires_reference: false,
    is_active: true,
});

const submit = () => {
    form.post('/payment-methods');
};
</script>

<template>
    <Head title="Create Payment Method" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Payment Method
                </h2>

                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Payment Method Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="code">Code *</Label>
                                    <Input 
                                        id="code"
                                        v-model="form.code" 
                                        placeholder="cash, visa, yape"
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
                                        placeholder="Efectivo, Tarjeta Visa"
                                        required
                                    />
                                    <span v-if="form.errors.name" class="text-sm text-red-500">
                                        {{ form.errors.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="type">Type *</Label>
                                <Select v-model="form.type" required>
                                    <SelectTrigger id="type">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="cash">Cash (Efectivo)</SelectItem>
                                        <SelectItem value="card">Card (Tarjeta)</SelectItem>
                                        <SelectItem value="transfer">Transfer (Transferencia)</SelectItem>
                                        <SelectItem value="wallet">Wallet (Billetera Digital)</SelectItem>
                                        <SelectItem value="other">Other (Otro)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.type" class="text-sm text-red-500">
                                    {{ form.errors.type }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <Checkbox id="requires_reference" v-model:checked="form.requires_reference" />
                                <Label for="requires_reference" class="cursor-pointer">
                                    Requires reference/operation number
                                </Label>
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
                        <Link href="/payment-methods">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Payment Method' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
