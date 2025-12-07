<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Branch {
    id: number;
    name: string;
}

interface Table {
    id: number;
    number: string;
    area: {
        name: string;
    };
}

defineProps<{
    branches: Branch[];
    tables: Table[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Reservations', href: '/reservations' },
    { title: 'Create', href: '/reservations/create' },
];

const form = useForm({
    branch_id: '',
    partner_id: '',
    table_id: '',
    reservation_date: '',
    reservation_time: '19:00',
    guests_count: 2,
    special_requests: '',
});

const submit = () => {
    form.post('/reservations');
};
</script>

<template>
    <Head title="Create Reservation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Create Reservation
                </h2>

                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Reservation Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="branch">Branch *</Label>
                                <Select v-model="form.branch_id">
                                    <SelectTrigger id="branch">
                                        <SelectValue placeholder="Select branch" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="branch in branches" :key="branch.id" :value="branch.id.toString()">
                                            {{ branch.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.branch_id" class="text-sm text-red-500">
                                    {{ form.errors.branch_id }}
                                </span>
                            </div>

                            <div class="grid gap-2">
                                <Label for="partner_id">Customer ID *</Label>
                                <Input 
                                    id="partner_id"
                                    v-model.number="form.partner_id" 
                                    type="number"
                                    placeholder="Enter customer ID"
                                    required
                                />
                                <span v-if="form.errors.partner_id" class="text-sm text-red-500">
                                    {{ form.errors.partner_id }}
                                </span>
                                <p class="text-xs text-gray-500">TODO: Add autocomplete search</p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="table">Table</Label>
                                <Select v-model="form.table_id">
                                    <SelectTrigger id="table">
                                        <SelectValue placeholder="Select table (optional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="table in tables" :key="table.id" :value="table.id.toString()">
                                            {{ table.number }} - {{ table.area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="reservation_date">Date *</Label>
                                    <Input 
                                        id="reservation_date"
                                        v-model="form.reservation_date" 
                                        type="date"
                                        :min="new Date().toISOString().split('T')[0]"
                                        required
                                    />
                                    <span v-if="form.errors.reservation_date" class="text-sm text-red-500">
                                        {{ form.errors.reservation_date }}
                                    </span>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="reservation_time">Time *</Label>
                                    <Input 
                                        id="reservation_time"
                                        v-model="form.reservation_time" 
                                        type="time"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="guests_count">Number of Guests *</Label>
                                <Input 
                                    id="guests_count"
                                    v-model.number="form.guests_count" 
                                    type="number"
                                    min="1"
                                    max="20"
                                    required
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="special_requests">Special Requests</Label>
                                <Textarea 
                                    id="special_requests"
                                    v-model="form.special_requests" 
                                    rows="3"
                                    placeholder="Any special requests or notes..."
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4 mt-6">
                        <Link href="/reservations">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Reservation' }}
                        </Button>
                    </div>
                 </form>
            </div>
        </div>
    </AppLayout>
</template>
