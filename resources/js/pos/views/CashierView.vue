<template>
    <PosLayout>
        <div class="cashier-view">
            <!-- Header -->
            <div class="view-header">
                <h1 class="view-title">💳 Caja - Órdenes Pendientes</h1>
                <button @click="refreshOrders" class="refresh-btn" :disabled="isLoading">
                    <span v-if="!isLoading">🔄 Actualizar</span>
                    <span v-else class="spinner-small"></span>
                </button>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="loading-container">
                <div class="spinner-large"></div>
                <p>Cargando órdenes...</p>
            </div>

            <!-- Lista de órdenes -->
            <div v-else-if="hasPendingOrders" class="orders-list">
                <div
                    v-for="order in pendingOrders"
                    :key="order.id"
                    @click="processPayment(order.id)"
                    class="order-card"
                >
                    <div class="order-header">
                        <div class="table-badge">
                            Mesa {{ order.table?.number || 'N/A' }}
                        </div>
                        <div class="order-number">{{ order.order_number }}</div>
                    </div>

                    <div class="order-info">
                        <div class="info-row">
                            <span class="label">👥 Comensales:</span>
                            <span class="value">{{ order.guests_count }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">🍴 Items:</span>
                            <span class="value">{{ order.items?.length || 0 }}</span>
                        </div>
                        <div v-if="order.waiter" class="info-row">
                            <span class="label">👤 Mozo:</span>
                            <span class="value">{{ order.waiter.name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">⏱️ Esperando:</span>
                            <span class="value">{{ getWaitingTime(order.updated_at) }}</span>
                        </div>
                    </div>

                    <div class="order-total">
                        <span>Total:</span>
                        <span class="amount">S/ {{ order.total.toFixed(2) }}</span>
                    </div>

                    <div class="order-action">
                        <button class="btn-process">
                            Procesar Pago →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="empty-state">
                <p style="font-size: 3rem; margin-bottom: 1rem;">✅</p>
                <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">No hay pagos pendientes</p>
                <p style="color: #6b7280;">Las órdenes listas para pagar aparecerán aquí</p>
            </div>
        </div>
    </PosLayout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { usePaymentsStore } from '@pos/stores/payments';
import PosLayout from '@pos/layouts/PosLayout.vue';

const router = useRouter();
const paymentsStore = usePaymentsStore();

// Computed from store
const pendingOrders = computed(() => paymentsStore.pendingOrders);
const hasPendingOrders = computed(() => paymentsStore.hasPendingOrders);
const isLoading = computed(() => paymentsStore.isLoading);

// Use store helper for waiting time
const getWaitingTime = paymentsStore.getWaitingTime;

// Actions
function refreshOrders() {
    paymentsStore.fetchPendingPayments();
}

function processPayment(orderId: number) {
    router.push({ name: 'payment', params: { orderId }, query: { from: 'cashier' } });
}

// Lifecycle
let refreshInterval: number | null = null;

onMounted(() => {
    paymentsStore.fetchPendingPayments();

    // Auto-refresh cada 30 segundos
    refreshInterval = window.setInterval(() => {
        paymentsStore.fetchPendingPayments();
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style scoped>
.cashier-view {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.view-header {
    background: white;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.view-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
}

.refresh-btn {
    padding: 0.75rem 1.5rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.refresh-btn:hover:not(:disabled) {
    background: #2563eb;
}

.refresh-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.loading-container,
.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}

.spinner-large {
    width: 3rem;
    height: 3rem;
    border: 4px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.spinner-small {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid white;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.orders-list {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
    align-content: start;
}

.order-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border-color: #3b82f6;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.table-badge {
    background: #3b82f6;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 1.125rem;
}

.order-number {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
}

.label {
    color: #6b7280;
}

.value {
    font-weight: 600;
    color: #1f2937;
}

.order-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    font-size: 1.25rem;
    font-weight: 700;
}

.amount {
    color: #10b981;
}

.order-action {
    text-align: center;
}

.btn-process {
    width: 100%;
    padding: 0.75rem;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-process:hover {
    background: #059669;
}

@media (max-width: 768px) {
    .view-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .orders-list {
        grid-template-columns: 1fr;
    }
}
</style>
