<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Plus, Edit, Trash2, CreditCard } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface PaymentMethod {
    id: number;
    code: string;
    name: string;
    type: string;
    requires_reference: boolean;
    is_active: boolean;
}

const props = defineProps<{
    paymentMethods: PaymentMethod[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Payment Methods', href: '/payment-methods' },
];

const deleteMethod = (id: number) => {
    if (confirm('Are you sure you want to delete this payment method?')) {
        router.delete(`/payment-methods/${id}`);
    }
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        cash: 'bg-green-500',
        card: 'bg-blue-500',
        transfer: 'bg-purple-500',
        wallet: 'bg-orange-500',
        other: 'bg-gray-500',
    };
    return colors[type] || 'bg-gray-500';
};
</script>

<template>
    <Head title="Payment Methods" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Payment Methods
                    </h2>
                    <Link href="/payment-methods/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            New Payment Method
                        </Button>
                    </Link>
                </div>

                <div v-if="paymentMethods.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="method in paymentMethods" :key="method.id">
                        <CardContent class="pt-6">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <CreditCard class="w-5 h-5 text-gray-600" />
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ method.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ method.code }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Link :href="`/payment-methods/${method.id}/edit`">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                    </Link>
                                    <Button 
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                        @click="deleteMethod(method.id)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-3">
                                <Badge :class="getTypeColor(method.type)" class="text-white">
                                    {{ method.type }}
                                </Badge>
                                <Badge v-if="method.requires_reference" variant="outline">
                                    Requires Reference
                                </Badge>
                                <Badge v-if="method.is_active" variant="default" class="bg-green-500">
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
                        <CreditCard class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No payment methods found</p>
                        <Link href="/payment-methods/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Create First Payment Method
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
