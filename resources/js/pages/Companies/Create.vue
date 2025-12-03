<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { store, index } from '@/routes/companies';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

interface Company {
    id: number;
    name: string;
    business_name: string;
    trade_name: string;
}

interface Props {
    matrices?: {
        data: Company[];
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href:  dashboard().url,
    },
    {
        title: 'Companies',
        href: index.url(),
    },
    {
        title: 'Create',
        href: '/companies/create',
    },
];

const companyType = ref<'matriz' | 'branch'>('matriz');

const form = useForm({
    parent_id: null as number | null,
    name: '',
    business_name: '',
    trade_name: '',
    tax_id: '',
    code: '',
    phone: '',
    email: '',
    address: '',
    website: '',
    logo: '',
    is_active: true,
});

const isBranch = computed(() => companyType.value === 'branch');

const submit = () => {
    form.post(store.url());
};
</script>

<template>
    <Head title="Create Company" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Company / Branch
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Type Selector -->
                        <div class="space-y-2">
                            <Label for="type">Type *</Label>
                            <Select v-model="companyType">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="matriz">Matriz (Parent Company)</SelectItem>
                                    <SelectItem value="branch">Sucursal (Branch)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Parent Company Selector (only for branches) -->
                        <div v-if="isBranch" class="space-y-2">
                            <Label for="parent_id">Parent Company *</Label>
                            <Select v-model="form.parent_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select parent company" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="matriz in props.matrices?.data" 
                                        :key="matriz.id" 
                                        :value="matriz.id">
                                        {{ matriz.name }} ({{ matriz.tax_id }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.parent_id" class="text-red-500 text-sm">
                                {{ form.errors.parent_id }}
                            </div>
                        </div>

                        <!-- Branch Code (only for branches) -->
                        <div v-if="isBranch" class="space-y-2">
                            <Label for="code">Branch Code *</Label>
                            <Input id="code" v-model="form.code" placeholder="e.g. MIR-001" required />
                            <div v-if="form.errors.code" class="text-red-500 text-sm">
                                {{ form.errors.code }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name">Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g. RestoPeru SAC" required />
                                <div v-if="form.errors.name" class="text-red-500 text-sm">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Business Name -->
                            <div class="space-y-2">
                                <Label for="business_name">Business Name *</Label>
                                <Input id="business_name" v-model="form.business_name" placeholder="Razón Social" required />
                                <div v-if="form.errors.business_name" class="text-red-500 text-sm">
                                    {{ form.errors.business_name }}
                                </div>
                            </div>

                            <!-- Trade Name -->
                            <div class="space-y-2">
                                <Label for="trade_name">Trade Name *</Label>
                                <Input id="trade_name" v-model="form.trade_name" placeholder="Nombre Comercial" required />
                                <div v-if="form.errors.trade_name" class="text-red-500 text-sm">
                                    {{ form.errors.trade_name }}
                                </div>
                            </div>

                            <!-- Tax ID -->
                            <div class="space-y-2">
                                <Label for="tax_id">Tax ID (RUC) *</Label>
                                <Input id="tax_id" v-model="form.tax_id" required maxlength="20" />
                                <div v-if="form.errors.tax_id" class="text-red-500 text-sm">
                                    {{ form.errors.tax_id }}
                                </div>
                            </div>
                        </div>

                        <!-- Address (required for branches) -->
                        <div class="space-y-2">
                            <Label for="address">Address {{ isBranch ? '*' : '' }}</Label>
                            <Input id="address" v-model="form.address" :required="isBranch" />
                            <div v-if="form.errors.address" class="text-red-500 text-sm">
                                {{ form.errors.address }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Phone -->
                            <div class="space-y-2">
                                <Label for="phone">Phone {{ isBranch ? '*' : '' }}</Label>
                                <Input id="phone" v-model="form.phone" :required="isBranch" />
                                <div v-if="form.errors.phone" class="text-red-500 text-sm">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email">Email {{ isBranch ? '*' : '' }}</Label>
                                <Input id="email" type="email" v-model="form.email" :required="isBranch" />
                                <div v-if="form.errors.email" class="text-red-500 text-sm">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Website -->
                            <div class="space-y-2">
                                <Label for="website">Website</Label>
                                <Input id="website" type="url" v-model="form.website" placeholder="https://" />
                                <div v-if="form.errors.website" class="text-red-500 text-sm">
                                    {{ form.errors.website }}
                                </div>
                            </div>

                            <!-- Logo URL -->
                            <div class="space-y-2">
                                <Label for="logo">Logo URL</Label>
                                <Input id="logo" v-model="form.logo" placeholder="https://example.com/logo.png" />
                                <div v-if="form.errors.logo" class="text-red-500 text-sm">
                                    {{ form.errors.logo }}
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
                                {{ form.processing ? 'Creating...' : (isBranch ? 'Create Branch' : 'Create Company') }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
