<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { update, index } from '@/routes/companies';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

interface Company {
    id: number;
    parent_id?: number | null;
    name: string;
    business_name: string;
    trade_name: string;
    tax_id: string;
    code?: string;
    phone?: string;
    email?: string;
    address?: string;
    website?: string;
    logo?: string;
    is_active: boolean;
}

interface Props {
    company: {
        data: Company;
    };
    matrices?: {
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
        title: 'Companies',
        href: index.url(),
    },
    {
        title: 'Edit',
        href: `/companies/${props.company.data.id}/edit`,
    },
];

const form = useForm({
    parent_id: props.company.data.parent_id || null,
    name: props.company.data.name,
    business_name: props.company.data.business_name,
    trade_name: props.company.data.trade_name,
    tax_id: props.company.data.tax_id,
    code: props.company.data.code || '',
    phone: props.company.data.phone || '',
    email: props.company.data.email || '',
    address: props.company.data.address || '',
    website: props.company.data.website || '',
    logo: props.company.data.logo || '',
    is_active: Boolean(props.company.data.is_active),
});

const isBranch = computed(() => !!form.parent_id);

const submit = () => {
    form.put(update.url(props.company.data.id));
};
</script>

<template>
    <Head title="Edit Company" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Edit {{ isBranch ? 'Branch' : 'Company' }}
                </h2>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Type Info (read-only) -->
                        <div class="bg-gray-50 p-3 rounded border">
                            <span class="text-sm font-medium">Type: </span>
                            <span class="text-sm">{{ isBranch ? 'Sucursal (Branch)' : 'Matriz (Parent Company)' }}</span>
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
                                <Input id="name" v-model="form.name" required />
                                <div v-if="form.errors.name" class="text-red-500 text-sm">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Business Name -->
                            <div class="space-y-2">
                                <Label for="business_name">Business Name *</Label>
                                <Input id="business_name" v-model="form.business_name" required />
                                <div v-if="form.errors.business_name" class="text-red-500 text-sm">
                                    {{ form.errors.business_name }}
                                </div>
                            </div>

                            <!-- Trade Name -->
                            <div class="space-y-2">
                                <Label for="trade_name">Trade Name *</Label>
                                <Input id="trade_name" v-model="form.trade_name" required />
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

                        <!-- Address -->
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
                                {{ form.processing ? 'Updating...' : (isBranch ? 'Update Branch' : 'Update Company') }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
