<template>
    <PosLayout>
        <div class="payment-view">
            <div class="payment-container">
                <!-- Header -->
                <div class="payment-header">
                    <button @click="goBack" class="back-btn">← Volver</button>
                    <h1 class="title">💳 Procesar Pago</h1>
                </div>

                <!-- Loading -->
                <div v-if="isLoading" class="loading-container">
                    <div class="spinner-large"></div>
                    <p>Cargando orden...</p>
                </div>

                <!-- Content -->
                <div v-else-if="order" class="payment-content">
                    <!-- Resumen de Orden -->
                    <div class="order-summary">
                        <h2 class="section-title">Resumen de Orden</h2>
                        
                        <div class="summary-header">
                            <div class="order-number">{{ order.order_number }}</div>
                            <div class="table-number">Mesa {{ order.table?.number || 'N/A' }}</div>
                        </div>

                        <div class="summary-info">
                            <div class="info-item">
                                <span>👥 Comensales:</span>
                                <span>{{ order.guests_count }}</span>
                            </div>
                            <div v-if="order.waiter" class="info-item">
                                <span>👤 Mozo:</span>
                                <span>{{ order.waiter.name }}</span>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="items-list">
                            <div v-for="item in order.items" :key="item.id" class="item-row">
                                <span class="item-qty">{{ item.quantity }}x</span>
                                <span class="item-name">{{ item.product?.name || 'Producto' }}</span>
                                <span class="item-price">S/ {{ (item.quantity * item.unit_price).toFixed(2) }}</span>
                            </div>
                        </div>

                        <!-- Totales -->
                        <div class="totals">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>S/ {{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="total-row">
                                <span>IGV (18%):</span>
                                <span>S/ {{ tax.toFixed(2) }}</span>
                            </div>
                            <div class="total-row total-final">
                                <span>TOTAL:</span>
                                <span>S/ {{ orderTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Procesar pago -->
                    <div class="cashier-section">
                        <h2 class="section-title">Métodos de Pago</h2>

                        <!-- Selección de métodos -->
                        <div class="payment-methods">
                            <div
                                v-for="method in paymentMethods"
                                :key="method.id"
                                @click="selectPaymentMethod(method)"
                                :class="['method-card', { active: selectedMethod?.id === method.id }]"
                            >
                                <span class="method-icon">{{ getMethodIcon(method.code) }}</span>
                                <span class="method-name">{{ method.name }}</span>
                            </div>
                        </div>

                        <!-- Input de monto -->
                        <div v-if="selectedMethod" class="payment-input">
                            <label class="input-label">Monto Recibido:</label>
                            <div class="amount-input">
                                <span class="currency">S/</span>
                                <input
                                    v-model.number="receivedAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="input-field"
                                    @input="onReceivedAmountChange"
                                />
                            </div>

                            <!-- Botones rápidos -->
                            <div class="quick-amounts">
                                <button @click="setExactAmount" class="quick-btn">Monto Exacto</button>
                                <button @click="setAmount(50)" class="quick-btn">S/ 50</button>
                                <button @click="setAmount(100)" class="quick-btn">S/ 100</button>
                                <button @click="setAmount(200)" class="quick-btn">S/ 200</button>
                            </div>

                            <!-- Cambio -->
                            <div v-if="change >= 0" class="change-display">
                                <span>Cambio:</span>
                                <span class="change-amount">
                                    S/ {{ change.toFixed(2) }}
                                </span>
                            </div>
                            <div v-if="isPaymentInsufficient" class="warning-message">
                                ⚠️ El monto recibido es insuficiente
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="actions">
                            <button
                                @click="handleProcessPayment"
                                class="btn-primary"
                                :disabled="!canProcessPayment || isProcessing"
                            >
                                <span v-if="!isProcessing">Finalizar Pago</span>
                                <span v-else class="saving-text">
                                    <span class="spinner-small"></span>
                                    Procesando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error state -->
                <div v-else class="error-state">
                    <p>❌ No se pudo cargar la orden</p>
                    <button @click="goBack" class="back-btn">Volver a Caja</button>
                </div>
            </div>
        </div>
    </PosLayout>
</template>

<script setup lang="ts">
import { onMounted, computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePaymentsStore } from '@pos/stores/payments';
import PosLayout from '@pos/layouts/PosLayout.vue';
import type { PaymentMethod } from '@pos/types';

const route = useRoute();
const router = useRouter();
const paymentsStore = usePaymentsStore();

// Local state for received amount input
const receivedAmount = ref<number>(0);

// Computed from store
const order = computed(() => paymentsStore.selectedOrder);
const paymentMethods = computed(() => paymentsStore.paymentMethods);
const selectedMethod = computed(() => paymentsStore.selectedMethod);
const isLoading = computed(() => paymentsStore.isLoading);
const isProcessing = computed(() => paymentsStore.isProcessing);
const orderTotal = computed(() => paymentsStore.orderTotal);
const change = computed(() => receivedAmount.value - orderTotal.value);
const canProcessPayment = computed(() => {
    return selectedMethod.value !== null && receivedAmount.value >= orderTotal.value;
});
const isPaymentInsufficient = computed(() => {
    return receivedAmount.value > 0 && receivedAmount.value < orderTotal.value;
});

// Derived calculations
const subtotal = computed(() => {
    if (!order.value) return 0;
    return order.value.total / 1.18; // Remove IGV
});

const tax = computed(() => {
    return subtotal.value * 0.18;
});

// Store helpers
const getMethodIcon = paymentsStore.getMethodIcon;

// Actions
function selectPaymentMethod(method: PaymentMethod) {
    paymentsStore.selectPaymentMethod(method);
}

function onReceivedAmountChange() {
    paymentsStore.setReceivedAmount(receivedAmount.value);
}

function setExactAmount() {
    receivedAmount.value = orderTotal.value;
    paymentsStore.setReceivedAmount(orderTotal.value);
}

function setAmount(amount: number) {
    receivedAmount.value = amount;
    paymentsStore.setReceivedAmount(amount);
}

async function handleProcessPayment() {
    if (!canProcessPayment.value) return;

    const success = await paymentsStore.processPayment();

    if (success) {
        alert(`✅ Pago procesado!\n\nTotal: S/ ${orderTotal.value.toFixed(2)}\nRecibido: S/ ${receivedAmount.value.toFixed(2)}\nCambio: S/ ${change.value.toFixed(2)}`);
        paymentsStore.clearPaymentState();
        router.push({ name: 'cashier' });
    } else {
        alert('❌ Error: ' + (paymentsStore.error || 'Error al procesar el pago'));
    }
}

function goBack() {
    paymentsStore.clearPaymentState();
    router.push({ name: 'cashier' });
}

// Lifecycle
onMounted(async () => {
    const orderId = Number(route.params.orderId);
    
    // Load payment methods
    await paymentsStore.fetchPaymentMethods();
    
    // Load order
    await paymentsStore.loadOrder(orderId);
    
    // Set initial amount to order total
    if (order.value) {
        receivedAmount.value = order.value.total;
        paymentsStore.setReceivedAmount(order.value.total);
    }
});
</script>

<style scoped>
.payment-view {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    overflow-y: auto;
    padding: 2rem 1rem;
    background: #f9fafb;
}

.payment-container {
    width: 100%;
    max-width: 800px;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.payment-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.back-btn {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}

.back-btn:hover {
    background: #e5e7eb;
}

.title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.loading-container,
.error-state {
    padding: 3rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
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

.payment-content {
    padding: 1.5rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #1f2937;
}

/* Order Summary */
.order-summary {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #e5e7eb;
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.order-number {
    font-size: 0.875rem;
    color: #6b7280;
}

.table-number {
    background: #3b82f6;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
}

.summary-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
}

.items-list {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.item-row {
    display: flex;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.item-row:last-child {
    border-bottom: none;
}

.item-qty {
    font-weight: 600;
    color: #6b7280;
    min-width: 2rem;
}

.item-name {
    flex: 1;
}

.item-price {
    font-weight: 600;
    color: #10b981;
}

.totals {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
}

.total-final {
    font-size: 1.25rem;
    font-weight: 700;
    padding-top: 0.75rem;
    margin-top: 0.5rem;
    border-top: 2px solid #e5e7eb;
    color: #1f2937;
}

/* Cashier Section */
.cashier-section {
    margin-top: 1rem;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.method-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
}

.method-card:hover {
    border-color: #3b82f6;
}

.method-card.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.method-icon {
    font-size: 2rem;
}

.method-name {
    font-weight: 500;
}

.payment-input {
    margin-bottom: 1.5rem;
}

.input-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.amount-input {
    display: flex;
    align-items: center;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
    margin-bottom: 1rem;
}

.currency {
    padding: 1rem;
    background: #f9fafb;
    font-weight: 600;
    color: #6b7280;
}

.input-field {
    flex: 1;
    padding: 1rem;
    border: none;
    font-size: 1.25rem;
    font-weight: 600;
}

.input-field:focus {
    outline: none;
}

.quick-amounts {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.quick-btn {
    padding: 0.75rem;
    background: #f3f4f6;
    border: none;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.quick-btn:hover {
    background: #e5e7eb;
}

.change-display {
    display: flex;
    justify-content: space-between;
    padding: 1rem;
    background: #f0fdf4;
    border-radius: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.change-amount {
    color: #10b981;
}

.warning-message {
    color: #f59e0b;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Actions */
.actions {
    margin-top: 2rem;
}

.btn-primary {
    width: 100%;
    padding: 1rem;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover:not(:disabled) {
    background: #059669;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.saving-text {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .payment-view {
        padding: 0;
    }
    
    .payment-container {
        border-radius: 0;
        box-shadow: none;
    }
    
    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-amounts {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
