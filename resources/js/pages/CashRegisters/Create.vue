<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DollarSign } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Terminal {
    id: number;
    code: string;
    name: string;
}

const props = defineProps<{
    terminals: Terminal[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Cash Registers', href: '/cash-registers' },
    { title: 'Open', href: '/cash-registers/create' },
];

const form = useForm({
    terminal_id: '',
    opening_balance: 0,
    notes: '',
});

const submit = () => {
    form.post('/cash-registers');
};
</script>

<template>
    <Head title="Open Cash Register" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 mb-6">
                    <DollarSign class="w-8 h-8 text-green-600" />
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Open Cash Register
                    </h2>
                </div>

                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Opening Information</CardTitle>
                            <CardDescription>Select terminal and set initial balance</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="terminal">Terminal *</Label>
                                <Select v-model="form.terminal_id" required>
                                    <SelectTrigger id="terminal">
                                        <SelectValue placeholder="Select a terminal" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="terminal in terminals" :key="terminal.id" :value="terminal.id.toString()">
                                            {{ terminal.name }} ({{ terminal.code }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.terminal_id" class="text-sm text-red-500">
                                    {{ form.errors.terminal_id }}
                                </span>
                            </div>

                            <div class="grid gap-2">
                                <Label for="opening_balance">Opening Balance (S/) *</Label>
                                <Input 
                                    id="opening_balance"
                                    v-model.number="form.opening_balance" 
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="200.00"
                                    required
                                />
                                <p class="text-xs text-gray-500">Initial cash amount in the register</p>
                                <span v-if="form.errors.opening_balance" class="text-sm text-red-500">
                                    {{ form.errors.opening_balance }}
                                </span>
                            </div>

                            <div class="grid gap-2">
                                <Label for="notes">Notes</Label>
                                <Textarea 
                                    id="notes"
                                    v-model="form.notes" 
                                    rows="3"
                                    placeholder="e.g., Morning shift - Monday"
                                />
                            </div>

                            <div v-if="form.opening_balance > 0" class="p-4 bg-green-50 border border-green-200 rounded">
                                <p class="text-sm font-medium text-green-900">Opening Balance</p>
                                <p class="text-2xl font-bold text-green-600">S/ {{ Number(form.opening_balance || 0).toFixed(2) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-4 mt-6">
                        <Link href="/cash-registers">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing" class="bg-green-600 hover:bg-green-700">
                            {{ form.processing ? 'Opening...' : 'Open Cash Register' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
