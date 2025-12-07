<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Eye, DollarSign, TrendingUp, TrendingDown } from 'lucide-vue-next';
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
    terminal: Terminal;
    opened_by: User;
    closed_by: User | null;
}

const props = defineProps<{
    cashRegisters: {
        data: CashRegister[];
        links: any;
        meta: any;
    };
    terminals: Terminal[];
    filters: {
        status?: string;
        terminal_id?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Cash Registers', href: '/cash-registers' },
];

const statusFilter = ref(props.filters.status || 'all');
const terminalFilter = ref(props.filters.terminal_id || 'all');

const applyFilters = () => {
    const params: any = {};
    if (statusFilter.value && statusFilter.value !== 'all') {
        params.status = statusFilter.value;
    }
    if (terminalFilter.value && terminalFilter.value !== 'all') {
        params.terminal_id = terminalFilter.value;
    }
    router.get('/cash-registers', params);
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
</script>

<template>
    <Head title="Cash Registers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Cash Registers
                    </h2>
                    <Link href="/cash-registers/create">
                        <Button class="bg-green-600 hover:bg-green-700">
                            <Plus class="w-4 h-4 mr-2" />
                            Open Cash Register
                        </Button>
                    </Link>
                </div>

                <!-- Filters -->
                <Card class="mb-6">
                    <CardContent class="pt-6">
                        <div class="flex gap-4 items-end">
                            <div class="w-48">
                                <label class="text-sm font-medium mb-2 block">Status</label>
                                <Select v-model="statusFilter">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        <SelectItem value="open">Open</SelectItem>
                                        <SelectItem value="closed">Closed</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex-1">
                                <label class="text-sm font-medium mb-2 block">Terminal</label>
                                <Select v-model="terminalFilter">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Terminals" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Terminals</SelectItem>
                                        <SelectItem v-for="terminal in terminals" :key="terminal.id" :value="terminal.id.toString()">
                                            {{ terminal.name }} ({{ terminal.code }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <Button @click="applyFilters">Apply Filters</Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Cash Registers List -->
                <div v-if="cashRegisters.data.length > 0" class="space-y-4">
                    <Card v-for="register in cashRegisters.data" :key="register.id">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <DollarSign class="w-5 h-5 text-green-600" />
                                        <h3 class="font-semibold text-lg">{{ register.terminal.name }}</h3>
                                        <Badge v-if="register.status === 'open'" variant="default" class="bg-green-500">
                                            Open
                                        </Badge>
                                        <Badge v-else variant="secondary">
                                            Closed
                                        </Badge>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Opening</p>
                                            <p class="font-medium">S/ {{ Number(register.opening_balance).toFixed(2) }}</p>
                                        </div>

                                        <div v-if="register.status === 'closed'">
                                            <p class="text-sm text-gray-500">Closing</p>
                                            <p class="font-medium">S/ {{ Number(register.closing_balance || 0).toFixed(2) }}</p>
                                        </div>

                                        <div v-if="register.status === 'closed'">
                                            <p class="text-sm text-gray-500">Expected</p>
                                            <p class="font-medium">S/ {{ Number(register.expected_balance || 0).toFixed(2) }}</p>
                                        </div>

                                        <div v-if="register.status === 'closed' && register.difference !== null">
                                            <p class="text-sm text-gray-500">Difference</p>
                                            <p class="font-medium flex items-center gap-1" :class="{
                                                'text-green-600': register.difference > 0,
                                                'text-red-600': register.difference < 0,
                                            }">
                                                <TrendingUp v-if="register.difference > 0" class="w-4 h-4" />
                                                <TrendingDown v-if="register.difference < 0" class="w-4 h-4" />
                                                S/ {{ Number(Math.abs(register.difference)).toFixed(2) }}
                                                <span v-if="register.difference > 0">(surplus)</span>
                                                <span v-if="register.difference < 0">(shortage)</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-sm text-gray-600">
                                        <p><span class="font-medium">Opened:</span> {{ formatDate(register.opened_at) }} by {{ register.opened_by.name }}</p>
                                        <p v-if="register.closed_at">
                                            <span class="font-medium">Closed:</span> {{ formatDate(register.closed_at) }} 
                                            <span v-if="register.closed_by">by {{ register.closed_by.name }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <Link :href="`/cash-registers/${register.id}`">
                                        <Button variant="outline" size="sm">
                                            <Eye class="w-4 h-4 mr-1" />
                                            View
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <DollarSign class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No cash registers found</p>
                        <Link href="/cash-registers/create">
                            <Button class="bg-green-600 hover:bg-green-700">
                                <Plus class="w-4 h-4 mr-2" />
                                Open First Cash Register
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
