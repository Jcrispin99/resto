<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Users, CheckCircle, XCircle, UserX, Armchair } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface Partner {
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

interface Branch {
    id: number;
    name: string;
}

interface Reservation {
    id: number;
    reservation_number: string;
    reservation_date: string;
    reservation_time: string;
    guests_count: number;
    status: string;
    special_requests: string | null;
    confirmed_at: string | null;
    seated_at: string | null;
    partner: Partner;
    table: Table | null;
    branch: Branch;
}

const props = defineProps<{
    reservation: Reservation;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Reservations', href: '/reservations' },
    { title: props.reservation.reservation_number, href: `/reservations/${props.reservation.id}` },
];

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

const formatDateTime = (date: string, time: string) => {
    const dateObj = new Date(date);
    const timeObj = new Date(time);
    return `${dateObj.toLocaleDateString('es-PE', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })} at ${timeObj.toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit',
    })}`;
};

const confirmReservation = () => {
    if (confirm('Confirm this reservation?')) {
        router.post(`/reservations/${props.reservation.id}/confirm`);
    }
};

const seatReservation = () => {
    if (confirm('Seat customers and create order?')) {
        router.post(`/reservations/${props.reservation.id}/seat`);
    }
};

const cancelReservation = () => {
    if (confirm('Cancel this reservation?')) {
        router.post(`/reservations/${props.reservation.id}/cancel`);
    }
};

const noShowReservation = () => {
    if (confirm('Mark as no-show?')) {
        router.post(`/reservations/${props.reservation.id}/no-show`);
    }
};
</script>

<template>
    <Head :title="`Reservation ${reservation.reservation_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="font-semibold text-2xl text-gray-800">
                            {{ reservation.reservation_number }}
                        </h2>
                        <p class="text-gray-600">{{ reservation.partner.name }}</p>
                    </div>
                    <Badge :class="getStatusColor(reservation.status)" class="text-white text-lg px-4 py-2">
                        {{ reservation.status }}
                    </Badge>
                </div>

                <!-- Actions -->
                <Card class="mb-6" v-if="reservation.status !== 'completed' && reservation.status !== 'cancelled'">
                    <CardContent class="pt-6">
                        <div class="flex  gap-3 flex-wrap">
                            <Button 
                                v-if="reservation.status === 'pending'" 
                                @click="confirmReservation"
                                variant="default"
                            >
                                <CheckCircle class="w-4 h-4 mr-2" />
                                Confirm
                            </Button>

                            <Button 
                                v-if="reservation.status === 'confirmed'" 
                                @click="seatReservation"
                                variant="default"
                                class="bg-purple-600 hover:bg-purple-700"
                            >
                                <Armchair class="w-4 h-4 mr-2" />
                                Seat Customers
                            </Button>

                            <Button 
                                v-if="['pending', 'confirmed'].includes(reservation.status)"
                                @click="cancelReservation"
                                variant="destructive"
                            >
                                <XCircle class="w-4 h-4 mr-2" />
                                Cancel
                            </Button>

                            <Button 
                                v-if="reservation.status === 'confirmed'"
                                @click="noShowReservation"
                                variant="outline"
                            >
                                <UserX class="w-4 h-4 mr-2" />
                                No Show
                            </Button>

                            <Link :href="`/reservations/${reservation.id}/edit`">
                                <Button variant="outline">
                                    Edit Details
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Details -->
                <div class="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Reservation Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center gap-2 text-sm">
                                <Calendar class="w-4 h-4 text-gray-500" />
                                <span class="font-medium">Date & Time:</span>
                                <span>{{ formatDateTime(reservation.reservation_date, reservation.reservation_time) }}</span>
                            </div>

                            <div class="flex items-center gap-2 text-sm">
                                <Users class="w-4 h-4 text-gray-500" />
                                <span class="font-medium">Guests:</span>
                                <span>{{ reservation.guests_count }} persons</span>
                            </div>

                            <div v-if="reservation.table" class="flex items-center gap-2 text-sm">
                                <span class="font-medium">Table:</span>
                                <span>{{ reservation.table.number }} ({{ reservation.table.area.name }})</span>
                            </div>

                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-medium">Branch:</span>
                                <span>{{ reservation.branch.name }}</span>
                            </div>

                            <div v-if="reservation.special_requests" class="pt-3 border-t">
                                <p class="text-sm font-medium mb-1">Special Requests:</p>
                                <p class="text-sm text-gray-600 whitespace-pre-line">{{ reservation.special_requests }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Status Timeline</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-start gap-2 text-sm">
                                <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5"></div>
                                <div>
                                    <p class="font-medium">Created</p>
                                    <p class="text-gray-600 text-xs">Reservation created</p>
                                </div>
                            </div>

                            <div v-if="reservation.confirmed_at" class="flex items-start gap-2 text-sm">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></div>
                                <div>
                                    <p class="font-medium">Confirmed</p>
                                    <p class="text-gray-600 text-xs">{{ new Date(reservation.confirmed_at).toLocaleString('es-PE') }}</p>
                                </div>
                            </div>

                            <div v-if="reservation.seated_at" class="flex items-start gap-2 text-sm">
                                <div class="w-2 h-2 rounded-full bg-purple-500 mt-1.5"></div>
                                <div>
                                    <p class="font-medium">Seated</p>
                                    <p class="text-gray-600 text-xs">{{ new Date(reservation.seated_at).toLocaleString('es-PE') }}</p>
                                </div>
                            </div>

                            <div v-if="reservation.status === 'cancelled'" class="flex items-start gap-2 text-sm">
                                <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5"></div>
                                <div>
                                    <p class="font-medium text-red-600">Cancelled</p>
                                </div>
                            </div>

                            <div v-if="reservation.status === 'no_show'" class="flex items-start gap-2 text-sm">
                                <div class="w-2 h-2 rounded-full bg-gray-500 mt-1.5"></div>
                                <div>
                                    <p class="font-medium text-gray-600">No Show</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="mt-6">
                    <Link href="/reservations">
                        <Button variant="outline">← Back to Reservations</Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
