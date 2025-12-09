import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import type { 
    Order, 
    PaymentMethod, 
    ProcessPaymentPayload, 
    ApiResponse 
} from '@pos/types';

export const usePaymentsStore = defineStore('payments', () => {
    // State
    const paymentMethods = ref<PaymentMethod[]>([]);
    const pendingOrders = ref<Order[]>([]);
    const selectedOrder = ref<Order | null>(null);
    const selectedMethod = ref<PaymentMethod | null>(null);
    const receivedAmount = ref<number>(0);
    const isLoading = ref(false);
    const isProcessing = ref(false);
    const error = ref<string | null>(null);

    // Getters
    const hasPendingOrders = computed(() => pendingOrders.value.length > 0);
    
    const pendingOrdersCount = computed(() => pendingOrders.value.length);

    const orderTotal = computed(() => {
        return selectedOrder.value?.total || 0;
    });

    const change = computed(() => {
        return receivedAmount.value - orderTotal.value;
    });

    const canProcessPayment = computed(() => {
        return selectedMethod.value !== null && 
               receivedAmount.value >= orderTotal.value &&
               selectedOrder.value !== null;
    });

    const isPaymentInsufficient = computed(() => {
        return receivedAmount.value > 0 && receivedAmount.value < orderTotal.value;
    });

    // Actions
    async function fetchPaymentMethods(): Promise<void> {
        try {
            const response = await axios.get<ApiResponse<PaymentMethod[]>>('/api/pos/payment-methods');
            paymentMethods.value = response.data.data;
        } catch (err: any) {
            console.error('Error fetching payment methods:', err);
            error.value = 'Error al cargar métodos de pago';
        }
    }

    async function fetchPendingPayments(): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<Order[]>>('/api/pos/pending-payments');
            pendingOrders.value = response.data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cargar órdenes pendientes';
            console.error('Error fetching pending payments:', err);
        } finally {
            isLoading.value = false;
        }
    }

    async function loadOrder(orderId: number): Promise<Order | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<Order>>(`/api/pos/orders/${orderId}`);
            selectedOrder.value = response.data.data;
            return selectedOrder.value;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cargar la orden';
            console.error('Error loading order:', err);
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    function selectPaymentMethod(method: PaymentMethod) {
        selectedMethod.value = method;
    }

    function setReceivedAmount(amount: number) {
        receivedAmount.value = Math.max(0, amount);
    }

    function setExactAmount() {
        receivedAmount.value = orderTotal.value;
    }

    async function processPayment(): Promise<boolean> {
        if (!selectedOrder.value || !selectedMethod.value) {
            error.value = 'Seleccione un método de pago';
            return false;
        }

        if (receivedAmount.value < orderTotal.value) {
            error.value = 'El monto recibido es insuficiente';
            return false;
        }

        isProcessing.value = true;
        error.value = null;

        try {
            const payload: ProcessPaymentPayload = {
                payment_method_id: selectedMethod.value.id,
                amount: orderTotal.value,
            };

            await axios.post(`/api/pos/orders/${selectedOrder.value.id}/payment`, payload);
            
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al procesar el pago';
            console.error('Error processing payment:', err);
            return false;
        } finally {
            isProcessing.value = false;
        }
    }

    function clearPaymentState() {
        selectedOrder.value = null;
        selectedMethod.value = null;
        receivedAmount.value = 0;
        error.value = null;
    }

    function getMethodIcon(code: string): string {
        const icons: Record<string, string> = {
            cash: '💵',
            card: '💳',
            transfer: '📱',
            yape: '📱',
            plin: '📱',
            credit_card: '💳',
            debit_card: '💳',
        };
        return icons[code] || '💰';
    }

    function getWaitingTime(updatedAt: string): string {
        const now = new Date();
        const updated = new Date(updatedAt);
        const diffMs = now.getTime() - updated.getTime();
        const diffMins = Math.floor(diffMs / 60000);

        if (diffMins < 1) return 'Recién';
        if (diffMins < 60) return `${diffMins} min`;
        
        const hours = Math.floor(diffMins / 60);
        const mins = diffMins % 60;
        return `${hours}h ${mins}m`;
    }

    return {
        // State
        paymentMethods,
        pendingOrders,
        selectedOrder,
        selectedMethod,
        receivedAmount,
        isLoading,
        isProcessing,
        error,
        // Getters
        hasPendingOrders,
        pendingOrdersCount,
        orderTotal,
        change,
        canProcessPayment,
        isPaymentInsufficient,
        // Actions
        fetchPaymentMethods,
        fetchPendingPayments,
        loadOrder,
        selectPaymentMethod,
        setReceivedAmount,
        setExactAmount,
        processPayment,
        clearPaymentState,
        getMethodIcon,
        getWaitingTime,
    };
});
