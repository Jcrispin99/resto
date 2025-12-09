import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import type { 
    Order, 
    OrderItem, 
    CreateOrderPayload, 
    ApiResponse,
    Product 
} from '@pos/types';

export const useOrdersStore = defineStore('orders', () => {
    // State
    const currentOrder = ref<Order | null>(null);
    const orders = ref<Order[]>([]);
    const isLoading = ref(false);
    const isSaving = ref(false);
    const error = ref<string | null>(null);

    // For cart/new order
    const cartItems = ref<{
        product: Product;
        quantity: number;
        unit_price: number;
        special_instructions: string;
    }[]>([]);
    const tableId = ref<number | null>(null);
    const guestsCount = ref<number>(1);

    // Getters
    const hasCurrentOrder = computed(() => !!currentOrder.value);
    
    const cartItemsCount = computed(() => {
        return cartItems.value.reduce((total, item) => total + item.quantity, 0);
    });

    const cartSubtotal = computed(() => {
        return cartItems.value.reduce((total, item) => {
            return total + (item.quantity * item.unit_price);
        }, 0);
    });

    const cartTax = computed(() => {
        return cartSubtotal.value * 0.18; // IGV 18%
    });

    const cartTotal = computed(() => {
        return cartSubtotal.value + cartTax.value;
    });

    const hasNewItems = computed(() => {
        if (!currentOrder.value) return cartItems.value.length > 0;
        
        // Check if there are any completely new products (not in original order)
        const originalProductIds = new Set(currentOrder.value.items.map(i => i.product.id));
        const hasNewProducts = cartItems.value.some(item => !originalProductIds.has(item.product.id));
        if (hasNewProducts) return true;

        // Check if any existing product has increased quantity
        for (const cartItem of cartItems.value) {
            const originalItem = currentOrder.value.items.find(i => i.product.id === cartItem.product.id);
            if (originalItem && cartItem.quantity > originalItem.quantity) {
                return true;
            }
        }

        return false;
    });

    // Cart Actions
    function setTable(id: number) {
        tableId.value = id;
    }

    function setGuests(count: number) {
        guestsCount.value = Math.max(1, Math.min(count, 50));
    }

    function addToCart(product: Product, quantity: number = 1, unitPrice?: number) {
        const existingItem = cartItems.value.find(item => item.product.id === product.id);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cartItems.value.push({
                product,
                quantity,
                unit_price: unitPrice !== undefined ? unitPrice : product.sale_price,
                special_instructions: '',
            });
        }
    }

    function removeFromCart(productId: number) {
        const index = cartItems.value.findIndex(item => item.product.id === productId);
        if (index !== -1) {
            cartItems.value.splice(index, 1);
        }
    }

    function updateCartQuantity(productId: number, quantity: number) {
        const item = cartItems.value.find(item => item.product.id === productId);
        if (item) {
            if (quantity <= 0) {
                removeFromCart(productId);
            } else {
                item.quantity = quantity;
            }
        }
    }

    function updateCartInstructions(productId: number, instructions: string) {
        const item = cartItems.value.find(item => item.product.id === productId);
        if (item) {
            item.special_instructions = instructions;
        }
    }

    function clearCart() {
        cartItems.value = [];
        tableId.value = null;
        guestsCount.value = 1;
        currentOrder.value = null;
    }

    // API Actions
    async function loadOrder(orderId: number): Promise<Order | null> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<Order>>(`/api/pos/orders/${orderId}`);
            currentOrder.value = response.data.data;

            // Populate cart with existing items
            if (currentOrder.value) {
                guestsCount.value = currentOrder.value.guests_count;
                tableId.value = currentOrder.value.table_id;
                
                cartItems.value = currentOrder.value.items.map(item => ({
                    product: item.product,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    special_instructions: item.special_instructions || '',
                }));
            }

            return currentOrder.value;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cargar la orden';
            console.error('Error loading order:', err);
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    async function loadOrderByTable(tableId: number): Promise<Order | null> {
        isLoading.value = true;
        error.value = null;

        try {
            // Get table info with current order
            const response = await axios.get<ApiResponse<any[]>>('/api/pos/tables');
            const tables = response.data.data;
            const table = tables.find((t: any) => t.id === tableId);

            if (table?.current_order) {
                return await loadOrder(table.current_order.id);
            }

            return null;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cargar la mesa';
            console.error('Error loading table order:', err);
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    async function createOrder(): Promise<Order | null> {
        if (cartItems.value.length === 0) {
            error.value = 'El carrito está vacío';
            return null;
        }

        isSaving.value = true;
        error.value = null;

        try {
            const payload: CreateOrderPayload = {
                table_id: tableId.value,
                guests_count: guestsCount.value,
                items: cartItems.value.map(item => ({
                    product_template_id: item.product.id,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    special_instructions: item.special_instructions || null,
                })),
            };

            const response = await axios.post<ApiResponse<Order>>('/api/pos/orders', payload);
            currentOrder.value = response.data.data;
            
            return currentOrder.value;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al crear la orden';
            console.error('Error creating order:', err);
            return null;
        } finally {
            isSaving.value = false;
        }
    }

    async function addItemsToOrder(): Promise<boolean> {
        if (!currentOrder.value) {
            error.value = 'No hay orden activa';
            return false;
        }

        isSaving.value = true;
        error.value = null;

        try {
            const itemsToSend: {
                product_template_id: number;
                quantity: number;
                unit_price: number;
                special_instructions: string | null;
            }[] = [];

            // Build a map of original items by product id
            const originalItemsMap = new Map(
                currentOrder.value.items.map(i => [i.product.id, i])
            );

            for (const cartItem of cartItems.value) {
                const originalItem = originalItemsMap.get(cartItem.product.id);

                if (!originalItem) {
                    // Completely new product
                    itemsToSend.push({
                        product_template_id: cartItem.product.id,
                        quantity: cartItem.quantity,
                        unit_price: cartItem.unit_price,
                        special_instructions: cartItem.special_instructions || null,
                    });
                } else if (cartItem.quantity > originalItem.quantity) {
                    // Same product but with increased quantity - send only the difference
                    const additionalQty = cartItem.quantity - originalItem.quantity;
                    itemsToSend.push({
                        product_template_id: cartItem.product.id,
                        quantity: additionalQty,
                        unit_price: cartItem.unit_price,
                        special_instructions: cartItem.special_instructions || null,
                    });
                }
            }

            if (itemsToSend.length === 0) {
                error.value = 'No hay items nuevos para agregar';
                return false;
            }

            const payload = { items: itemsToSend };

            await axios.post(`/api/pos/orders/${currentOrder.value.id}/items`, payload);
            
            // Reload order to get updated data
            await loadOrder(currentOrder.value.id);
            
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al agregar items';
            console.error('Error adding items:', err);
            return false;
        } finally {
            isSaving.value = false;
        }
    }

    async function closeOrder(): Promise<boolean> {
        if (!currentOrder.value) {
            error.value = 'No hay orden activa';
            return false;
        }

        isSaving.value = true;
        error.value = null;

        try {
            await axios.patch(`/api/pos/orders/${currentOrder.value.id}/close`);
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cerrar la cuenta';
            console.error('Error closing order:', err);
            return false;
        } finally {
            isSaving.value = false;
        }
    }

    async function cancelOrder(): Promise<boolean> {
        if (!currentOrder.value) return true;

        isSaving.value = true;
        error.value = null;

        try {
            // For now, just clear the cart
            // In production, you might want to call an API to cancel the order
            clearCart();
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cancelar';
            return false;
        } finally {
            isSaving.value = false;
        }
    }

    return {
        // State
        currentOrder,
        orders,
        isLoading,
        isSaving,
        error,
        cartItems,
        tableId,
        guestsCount,
        // Getters
        hasCurrentOrder,
        cartItemsCount,
        cartSubtotal,
        cartTax,
        cartTotal,
        hasNewItems,
        // Cart Actions
        setTable,
        setGuests,
        addToCart,
        removeFromCart,
        updateCartQuantity,
        updateCartInstructions,
        clearCart,
        // API Actions
        loadOrder,
        loadOrderByTable,
        createOrder,
        addItemsToOrder,
        closeOrder,
        cancelOrder,
    };
});
