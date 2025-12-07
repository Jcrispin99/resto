<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Plus, Edit, Trash2, Monitor } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Branch {
    id: number;
    name: string;
}

interface Terminal {
    id: number;
    code: string;
    name: string;
    ip_address: string | null;
    printer_ip: string | null;
    is_active: boolean;
    branch: Branch | null;
}

const props = defineProps<{
    terminals: Terminal[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'POS Terminals', href: '/pos-terminals' },
];

const deleteTerminal = (id: number) => {
    if (confirm('Are you sure you want to delete this terminal?')) {
        router.delete(`/pos-terminals/${id}`);
    }
};
</script>

<template>
    <Head title="POS Terminals" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        POS Terminals
                    </h2>
                    <Link href="/pos-terminals/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            New Terminal
                        </Button>
                    </Link>
                </div>

                <div v-if="terminals.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="terminal in terminals" :key="terminal.id">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <Monitor class="w-5 h-5 text-blue-600" />
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ terminal.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ terminal.code }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Link :href="`/pos-terminals/${terminal.id}/edit`">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button 
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                        @click="deleteTerminal(terminal.id)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <p v-if="terminal.branch" class="text-gray-600">
                                    <span class="font-medium">Branch:</span> {{ terminal.branch.name }}
                                </p>
                                <p v-if="terminal.ip_address" class="text-gray-600">
                                    <span class="font-medium">IP:</span> {{ terminal.ip_address }}
                                </p>
                                <p v-if="terminal.printer_ip" class="text-gray-600">
                                    <span class="font-medium">Printer:</span> {{ terminal.printer_ip }}
                                </p>
                            </div>

                            <div class="mt-3">
                                <Badge v-if="terminal.is_active" variant="default" class="bg-green-500">
                                    Active
                                </Badge>
                                <Badge v-else variant="secondary">
                                    Inactive
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <Monitor class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No terminals found</p>
                        <Link href="/pos-terminals/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Create First Terminal
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
