<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DollarSign, Lock, TrendingUp, TrendingDown, User, Calendar } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

interface Terminal {
    id: number;
    code: string;
    name: string;
}

interface User {
    id: number;
    name: string;
}

interface Movement {
    id: number;
    type: string;
    concept: string;
    amount: number;
    payment_method: string | null;
    reference: string | null;
    created_at: string;
    user: User;
}

interface CashRegister {
    id: number;
    terminal_id: number;
    opening_balance: number;
    closing_balance: number | null;
    expected_balance: number | null;
    difference: number | null;
    opened_at: string;
    closed_at: string | null;
    status: string;
    notes: string | null;
    terminal: Terminal;
    opened_by: User;
    closed_by: User | null;
    movements: Movement[];
}

const props = defineProps<{
    cashRegister: CashRegister;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Cash Registers', href: '/cash-registers' },
    { title: `Register #${props.cashRegister.id}`, href: `/cash-registers/${props.cashRegister.id}` },
];

const closeDialogOpen = ref(false);
const closeForm = useForm({
    closing_balance: 0,
    notes: '',
});

const submitClose = () => {
    closeForm.post(`/cash-registers/${props.cashRegister.id}/close`, {        
        onSuccess: () => {
            closeDialogOpen.value = false;
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        opening: 'bg-blue-500',
        income: 'bg-green-500',
        expense: 'bg-red-500',
        deposit: 'bg-purple-500',
        withdrawal: 'bg-orange-500',
        closing: 'bg-gray-500',
    };
    return colors[type] || 'bg-gray-500';
};

const calculateRunningBalance = (index: number) => {
    let balance = 0;
    for (let i = 0; i <= index; i++) {
        balance += Number(props.cashRegister.movements[i].amount);
    }
    return balance;
};
</script>

<template>
    <Head :title="`Cash Register #${cashRegister.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-3">
                        <DollarSign class="w-8 h-8 text-green-600" />
                        <div>
                            <h2 class="font-semibold text-xl text-gray-800">
                                Cash Register #{{ cashRegister.id }}
                            </h2>
                            <p class="text-sm text-gray-600">{{ cashRegister.terminal.name }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Badge v-if="cashRegister.status === 'open'" variant="default" class="bg-green-500 text-lg px-4 py-2">
                            Open
                        </Badge>
                        <Badge v-else variant="secondary" class="text-lg px-4 py-2">
                            Closed
                        </Badge>

                        <Dialog v-if="cashRegister.status === 'open'" v-model:open="closeDialogOpen">
                            <DialogTrigger as-child>
                                <Button variant="destructive">
                                    <Lock class="w-4 h-4 mr-2" />
                                    Close Register
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Close Cash Register</DialogTitle>
                                    <DialogDescription>
                                        Count the cash and enter the final balance
                                    </DialogDescription>
                                </DialogHeader>
                                <form @submit.prevent="submitClose">
                                    <div class="space-y-4 py-4">
                                        <div class="grid gap-2">
                                            <Label for="closing_balance">Closing Balance (S/) *</Label>
                                            <Input 
                                                id="closing_balance"
                                                v-model.number="closeForm.closing_balance" 
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                            />
                                            <span v-if="closeForm.errors.closing_balance" class="text-sm text-red-500">
                                                {{ closeForm.errors.closing_balance }}
                                            </span>
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="notes">Closing Notes</Label>
                                            <Textarea 
                                                id="notes"
                                                v-model="closeForm.notes" 
                                                rows="3"
                                            />
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button type="button" variant="outline" @click="closeDialogOpen = false">
                                            Cancel
                                        </Button>
                                        <Button type="submit" variant="destructive" :disabled="closeForm.processing">
                                            {{ closeForm.processing ? 'Closing...' : 'Close Register' }}
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid gap-4 md:grid-cols-4 mb-6">
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-medium text-gray-500">Opening Balance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-bold">S/ {{ Number(cashRegister.opening_balance).toFixed(2) }}</p>
                        </CardContent>
                    </Card>

                    <Card v-if="cashRegister.closing_balance !== null">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-medium text-gray-500">Closing Balance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-bold">S/ {{ Number(cashRegister.closing_balance).toFixed(2) }}</p>
                        </CardContent>
                    </Card>

                    <Card v-if="cashRegister.expected_balance !== null">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-medium text-gray-500">Expected Balance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-bold">S/ {{ Number(cashRegister.expected_balance).toFixed(2) }}</p>
                        </CardContent>
                    </Card>

                    <Card v-if="cashRegister.difference !== null">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-medium text-gray-500">Difference</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-bold flex items-center gap-2" :class="{
                                'text-green-600': cashRegister.difference > 0,
                                'text-red-600': cashRegister.difference < 0,
                            }">
                                <TrendingUp v-if="cashRegister.difference > 0" class="w-5 h-5" />
                                <TrendingDown v-if="cashRegister.difference < 0" class="w-5 h-5" />
                                S/ {{ Number(Math.abs(cashRegister.difference)).toFixed(2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <span v-if="cashRegister.difference > 0">Surplus</span>
                                <span v-else-if="cashRegister.difference < 0">Shortage</span>
                                <span v-else>Balanced</span>
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Info Card -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Session Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center gap-2 text-sm">
                            <User class="w-4 h-4 text-gray-500" />
                            <span class="font-medium">Opened by:</span>
                            <span>{{ cashRegister.opened_by.name }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <Calendar class="w-4 h-4 text-gray-500" />
                            <span class="font-medium">Opened at:</span>
                            <span>{{ formatDate(cashRegister.opened_at) }}</span>
                        </div>
                        <div v-if="cashRegister.closed_at" class="flex items-center gap-2 text-sm">
                            <User class="w-4 h-4 text-gray-500" />
                            <span class="font-medium">Closed by:</span>
                            <span>{{ cashRegister.closed_by?.name }}</span>
                        </div>
                        <div v-if="cashRegister.closed_at" class="flex items-center gap-2 text-sm">
                            <Calendar class="w-4 h-4 text-gray-500" />
                            <span class="font-medium">Closed at:</span>
                            <span>{{ formatDate(cashRegister.closed_at) }}</span>
                        </div>
                        <div v-if="cashRegister.notes" class="pt-2 border-t">
                            <p class="text-sm font-medium mb-1">Notes:</p>
                            <p class="text-sm text-gray-600 whitespace-pre-line">{{ cashRegister.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Movements -->
                <Card>
                    <CardHeader>
                        <CardTitle>Cash Movements</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="cashRegister.movements.length > 0" class="space-y-2">
                            <div v-for="(movement, index) in cashRegister.movements" :key="movement.id" 
                                class="flex items-center justify-between p-3 rounded-lg border hover:bg-gray-50">
                                <div class="flex items-center gap-3">
                                    <Badge :class="getTypeColor(movement.type)" class="text-white w-24 justify-center">
                                        {{ movement.type }}
                                    </Badge>
                                    <div>
                                        <p class="font-medium">{{ movement.concept }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(movement.created_at) }} · {{ movement.user.name }}
                                            <span v-if="movement.payment_method">· {{ movement.payment_method }}</span>
                                            <span v-if="movement.reference">· Ref: {{ movement.reference }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg" :class="{
                                        'text-green-600': movement.amount > 0,
                                        'text-red-600': movement.amount < 0,
                                    }">
                                        {{ movement.amount > 0 ? '+' : '' }}S/ {{ Number(movement.amount).toFixed(2) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Balance: S/ {{ calculateRunningBalance(index).toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No movements recorded
                        </div>
                    </CardContent>
                </Card>

                <div class="mt-6">
                    <Link href="/cash-registers">
                        <Button variant="outline">← Back to Registers</Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
