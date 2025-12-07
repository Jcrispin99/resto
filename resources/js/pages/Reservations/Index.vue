<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Eye, Calendar, Users } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import {ref, watch } from 'vue';

interface Partner {
    id: number;
    name: string;
}

interface Table {
    id: number;
    number: string;
}

interface Reservation {
    id: number;
    reservation_number: string;
    reservation_date: string;
    reservation_time: string;
    guests_count: number;
    status: string;
    partner: Partner;
    table: Table | null;
}

interface Filters {
    status?: string;
    date?: string;
}

const props = defineProps<{
    reservations: {
        data: Reservation[];
    };
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Reservations', href: '/reservations' },
];

const statusFilter = ref(props.filters.status || '');
const dateFilter = ref(props.filters.date || '');

watch([statusFilter, dateFilter], ([newStatus, newDate]) => {
    router.get('/reservations', { status: newStatus, date: newDate }, {
        preserveState: true,
        replace: true,
    });
});

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-500',
        confirmed: 'bg-blue-500',
        seated: 'bg-purple-500',
        completed: 'bg-green-500',
        cancelled: 'bg-red-500',
        no_show: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatTime = (time: string) => {
    const date = new Date(time);
    return date.toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Reservations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Reservations
                    </h2>
                    <Link href="/reservations/create">
                        <Button>
                            <Plus class="w-4 h-4 mr-2" />
                            New Reservation
                        </Button>
                    </Link>
                </div>

                <!-- Filters -->
                <Card class="mb-6">
                    <CardContent class="pt-6">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium mb-2 block">Status</label>
                                <Select v-model="statusFilter">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="undefined">All Statuses</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="confirmed">Confirmed</SelectItem>
                                        <SelectItem value="seated">Seated</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                        <SelectItem value="no_show">No Show</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-2 block">Date</label>
                                <input 
                                    type="date" 
                                    v-model="dateFilter"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div v-if="reservations.data.length > 0" class="space-y-3">
                    <Card v-for="reservation in reservations.data" :key="reservation.id" class="hover:shadow-md transition-shadow">
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <Calendar class="w-5 h-5 text-gray-500" />
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ reservation.reservation_number }}</h3>
                                            <p class="text-sm text-gray-600">{{ reservation.partner.name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-600 ml-8">
                                        <span>📅 {{ formatDate(reservation.reservation_date) }}</span>
                                        <span>🕒 {{ formatTime(reservation.reservation_time) }}</span>
                                        <span class="flex items-center gap-1">
                                            <Users class="w-4 h-4" />
                                            {{ reservation.guests_count }} guests
                                        </span>
                                        <span v-if="reservation.table">🍽️ Table {{ reservation.table.number }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <Badge :class="getStatusColor(reservation.status)" class="text-white">
                                        {{ reservation.status }}
                                    </Badge>
                                    <Link :href="`/reservations/${reservation.id}`">
                                        <Button variant="outline" size="sm">
                                            <Eye class="w-4 h-4 mr-2" />
                                            View
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else>
                    <CardContent class="py-12 text-center">
                        <Calendar class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No reservations found</p>
                        <Link href="/reservations/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Create First Reservation
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
