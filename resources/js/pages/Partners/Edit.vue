<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { update, index } from '@/routes/partners';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Partner {
    id: number;
    code: string;
    partner_type: string;
    name: string;
    trade_name?: string;
    tax_id: string;
    email: string;
    phone: string;
    address?: string;
    ubigeo_code?: string;
    is_customer: boolean;
    is_supplier: boolean;
    payment_terms_days: number;
    notes?: string;
    is_active: boolean;
}

interface Props {
    partner: {
        data: Partner;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Partners',
        href: index.url(),
    },
    {
        title: 'Edit',
        href: `/partners/${props.partner.data.id}/edit`,
    },
];

const partnerTypes = [
    { value: 'company', label: 'Company (Empresa)' },
    { value: 'individual', label: 'Individual (Persona Natural)' },
];

const form = useForm({
    code: props.partner.data.code,
    partner_type: props.partner.data.partner_type,
    name: props.partner.data.name,
    trade_name: props.partner.data.trade_name || '',
    tax_id: props.partner.data.tax_id,
    email: props.partner.data.email,
    phone: props.partner.data.phone,
    address: props.partner.data.address || '',
    ubigeo_code: props.partner.data.ubigeo_code || '',
    is_customer: Boolean(props.partner.data.is_customer),
    is_supplier: Boolean(props.partner.data.is_supplier),
    payment_terms_days: props.partner.data.payment_terms_days,
    notes: props.partner.data.notes || '',
    is_active: Boolean(props.partner.data.is_active),
});

const submit = () => {
    form.put(update.url(props.partner.data.id));
};
</script>

<template>
    <Head title="Edit Partner" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit Partner
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Partner Role Selection -->
                        <div class="bg-blue-50 p-4 rounded border border-blue-200">
                            <Label class="text-base font-semibold mb-3 block">Partner Role *</Label>
                            <div class="flex gap-6">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           id="is_customer"
                                           v-model="form.is_customer"
                                           class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
                                    <Label for="is_customer" class="font-normal cursor-pointer">Customer (Cliente)</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           id="is_supplier"
                                           v-model="form.is_supplier"
                                           class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
                                    <Label for="is_supplier" class="font-normal cursor-pointer">Supplier (Proveedor)</Label>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">Select at least one role</p>
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

                            <!-- Partner Type -->
                            <div class="space-y-2">
                                <Label for="partner_type">Type *</Label>
                                <Select v-model="form.partner_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in partnerTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.partner_type" class="text-red-500 text-sm">
                                    {{ form.errors.partner_type }}
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name">Name / Business Name *</Label>
                                <Input id="name" v-model="form.name" required maxlength="200" />
                                <div v-if="form.errors.name" class="text-red-500 text-sm">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Trade Name -->
                            <div class="space-y-2">
                                <Label for="trade_name">Trade Name</Label>
                                <Input id="trade_name" v-model="form.trade_name" maxlength="200" />
                                <div v-if="form.errors.trade_name" class="text-red-500 text-sm">
                                    {{ form.errors.trade_name }}
                                </div>
                            </div>

                            <!-- Tax ID -->
                            <div class="space-y-2">
                                <Label for="tax_id">Tax ID (RUC/DNI) *</Label>
                                <Input id="tax_id" v-model="form.tax_id" required maxlength="20" />
                                <div v-if="form.errors.tax_id" class="text-red-500 text-sm">
                                    {{ form.errors.tax_id }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email">Email *</Label>
                                <Input id="email" type="email" v-model="form.email" required maxlength="150" />
                                <div v-if="form.errors.email" class="text-red-500 text-sm">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <Label for="phone">Phone *</Label>
                                <Input id="phone" v-model="form.phone" required maxlength="20" />
                                <div v-if="form.errors.phone" class="text-red-500 text-sm">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <!-- Payment Terms -->
                            <div class="space-y-2">
                                <Label for="payment_terms_days">Payment Terms (days)</Label>
                                <Input id="payment_terms_days" type="number" v-model.number="form.payment_terms_days" min="0" />
                                <div v-if="form.errors.payment_terms_days" class="text-red-500 text-sm">
                                    {{ form.errors.payment_terms_days }}
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Input id="address" v-model="form.address" maxlength="255" />
                            <div v-if="form.errors.address" class="text-red-500 text-sm">
                                {{ form.errors.address }}
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Input id="notes" v-model="form.notes" placeholder="Additional notes..." />
                            <div v-if="form.errors.notes" class="text-red-500 text-sm">
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <!-- Active Checkbox -->
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" 
                                   id="is_active"
                                   v-model="form.is_active"
                                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
                            <Label for="is_active">Active</Label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-2 pt-4">
                            <Link :href="index.url()">
                                <Button variant="outline" type="button">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Partner' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
