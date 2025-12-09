<template>
    <PosLayout>
        <div class="order-view">
            <!-- Columna Izquierda: Productos -->
            <div class="products-section">
                <!-- Header con categorías -->
                <div class="categories-header">
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        @click="selectCategory(category.id)"
                        :class="['category-btn', { active: selectedCategory === category.id }]"
                    >
                        <span class="category-icon">{{ category.icon }}</span>
                        <span class="category-name">{{ category.name }}</span>
                    </button>
                </div>

                <!-- Grid de productos -->
                <div class="products-grid">
                    <div v-if="isLoading" class="loading-state">
                        <div class="spinner-large"></div>
                        <p>Cargando productos...</p>
                    </div>

                    <div
                        v-else
                        v-for="product in products"
                        :key="product.id"
                        @click="addToCart(product)"
                        class="product-card"
                    >
                        <div class="product-image">
                            <span class="product-icon">🍽️</span>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">{{ product.name }}</h3>
                            <p v-if="product.description" class="product-desc">
                                {{ product.description }}
                            </p>
                            <div class="product-price">
                                S/ {{ product.sale_price.toFixed(2) }}
                            </div>
                        </div>
                    </div>

                    <div v-if="!isLoading && products.length === 0" class="empty-products">
                        <p>📭 No hay productos en esta categoría</p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Carrito -->
            <div class="cart-section">
                <!-- Header del carrito -->
                <div class="cart-header">
                    <h2 class="cart-title">🛒 Pedido</h2>
                    <div class="table-info-badge">
                        Mesa {{ tableNumber }}
                    </div>
                </div>

                <!-- Comensales -->
                <div class="guests-control">
                    <label class="guests-label">👥 Comensales:</label>
                    <div class="guests-buttons">
                        <button @click="decrementGuests" class="qty-btn">-</button>
                        <span class="guests-count">{{ guestsCount }}</span>
                        <button @click="incrementGuests" class="qty-btn">+</button>
                    </div>
                </div>

                <!-- Items del carrito -->
                <div class="cart-items">
                    <div v-if="cartItems.length === 0" class="empty-cart">
                        <p>🍽️ Carrito vacío</p>
                        <p class="empty-hint">Selecciona productos para agregar</p>
                    </div>

                    <div
                        v-for="item in cartItems"
                        :key="item.product.id"
                        class="cart-item"
                    >
                        <div class="item-header">
                            <h4 class="item-name">{{ item.product.name }}</h4>
                            <button @click="removeFromCart(item.product.id)" class="remove-btn">
                                ✕
                            </button>
                        </div>

                        <div class="item-controls">
                            <div class="quantity-control">
                                <button @click="decrementQty(item.product.id)" class="qty-btn">-</button>
                                <span class="qty-display">{{ item.quantity }}</span>
                                <button @click="incrementQty(item.product.id)" class="qty-btn">+</button>
                            </div>
                            <div class="item-price">
                                S/ {{ (item.quantity * item.unit_price).toFixed(2) }}
                            </div>
                        </div>

                        <input
                            v-model="item.special_instructions"
                            @input="updateInstructions(item.product.id, item.special_instructions)"
                            type="text"
                            placeholder="Instrucciones especiales..."
                            class="item-instructions"
                        />
                    </div>
                </div>

                <!-- Totales -->
                <div class="cart-totals">
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
                        <span>S/ {{ total.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="cart-actions">
                    <button @click="cancelOrder" class="btn-cancel" :disabled="isSaving">
                        Cancelar
                    </button>
                    
                    <!-- Si es orden nueva -->
                    <button
                        v-if="!existingOrderId"
                        @click="saveOrder"
                        class="btn-save"
                        :disabled="cartItems.length === 0 || isSaving"
                    >
                        <span v-if="!isSaving">Enviar Pedido</span>
                        <span v-else class="saving-text">
                            <span class="spinner-small"></span>
                            Guardando...
                        </span>
                    </button>
                    
                    <!-- Si es orden existente -->
                    <template v-else>
                        <button
                            v-if="hasNewItems"
                            @click="addItemsToOrder"
                            class="btn-add-items"
                            :disabled="isSaving"
                        >
                            <span v-if="!isSaving">Agregar Items</span>
                            <span v-else class="saving-text">
                                <span class="spinner-small"></span>
                                Agregando...
                            </span>
                        </button>
                        <button
                            @click="closeAccount"
                            class="btn-close-account"
                            :disabled="isSaving"
                        >
                            <span v-if="!isSaving">Cerrar Cuenta</span>
                            <span v-else class="saving-text">
                                <span class="spinner-small"></span>
                                Cerrando...
                            </span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </PosLayout>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProductsStore } from '@pos/stores/products';
import { useOrdersStore } from '@pos/stores/orders';
import PosLayout from '@pos/layouts/PosLayout.vue';
import type { Product } from '@pos/types';

const route = useRoute();
const router = useRouter();
const productsStore = useProductsStore();
const ordersStore = useOrdersStore();

const tableNumber = computed(() => route.params.tableId);

// Products
const products = computed(() => productsStore.products);
const categories = computed(() => productsStore.categories);
const selectedCategory = computed(() => productsStore.selectedCategory);
const isLoading = computed(() => productsStore.isLoading);

// Cart/Order from orders store
const cartItems = computed(() => ordersStore.cartItems);
const guestsCount = computed(() => ordersStore.guestsCount);
const subtotal = computed(() => ordersStore.cartSubtotal);
const tax = computed(() => ordersStore.cartTax);
const total = computed(() => ordersStore.cartTotal);
const isSaving = computed(() => ordersStore.isSaving);
const existingOrderId = computed(() => ordersStore.currentOrder?.id || null);
const hasNewItems = computed(() => ordersStore.hasNewItems);

// Category selection
function selectCategory(categoryId: number) {
    productsStore.selectCategory(categoryId);
}

// Cart actions
function addToCart(product: Product) {
    ordersStore.addToCart(product);
}

function removeFromCart(productId: number) {
    ordersStore.removeFromCart(productId);
}

function incrementQty(productId: number) {
    const item = cartItems.value.find(i => i.product.id === productId);
    if (item) {
        ordersStore.updateCartQuantity(productId, item.quantity + 1);
    }
}

function decrementQty(productId: number) {
    const item = cartItems.value.find(i => i.product.id === productId);
    if (item && item.quantity > 1) {
        ordersStore.updateCartQuantity(productId, item.quantity - 1);
    } else {
        removeFromCart(productId);
    }
}

function updateInstructions(productId: number, instructions: string) {
    ordersStore.updateCartInstructions(productId, instructions);
}

function incrementGuests() {
    ordersStore.setGuests(guestsCount.value + 1);
}

function decrementGuests() {
    if (guestsCount.value > 1) {
        ordersStore.setGuests(guestsCount.value - 1);
    }
}

// Order actions
function cancelOrder() {
    if (confirm('¿Cancelar pedido y volver a mesas?')) {
        ordersStore.clearCart();
        router.push({ name: 'tables' });
    }
}

async function saveOrder() {
    if (cartItems.value.length === 0) return;

    const order = await ordersStore.createOrder();
    
    if (order) {
        alert('✅ Pedido enviado a cocina!');
        ordersStore.clearCart();
        router.push({ name: 'tables' });
    } else {
        alert('❌ Error: ' + (ordersStore.error || 'Error al guardar pedido'));
    }
}

async function addItemsToOrder() {
    const success = await ordersStore.addItemsToOrder();
    
    if (success) {
        alert('✅ Items agregados al pedido!');
        ordersStore.clearCart();
        router.push({ name: 'tables' });
    } else {
        alert('❌ Error: ' + (ordersStore.error || 'Error al agregar items'));
    }
}

async function closeAccount() {
    const confirmed = confirm('¿Cerrar cuenta y enviar a caja?');
    if (!confirmed) return;

    const success = await ordersStore.closeOrder();
    
    if (success) {
        alert('✅ Cuenta cerrada. Cliente puede pagar en caja.');
        ordersStore.clearCart();
        router.push({ name: 'tables' });
    } else {
        alert('❌ Error: ' + (ordersStore.error || 'Error al cerrar cuenta'));
    }
}

onMounted(async () => {
    const tableId = Number(route.params.tableId);
    ordersStore.setTable(tableId);
    
    // Load existing order if table is occupied
    await ordersStore.loadOrderByTable(tableId);
    
    // Load products
    await productsStore.fetchCategories();
    
    if (categories.value.length > 0) {
        productsStore.selectCategory(categories.value[0].id);
    }
});
</script>

<style scoped>
.order-view {
    height: 100%;
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 0;
}

@media (max-width: 1024px) {
    .order-view {
        grid-template-columns: 1fr 350px;
    }
}

@media (max-width: 768px) {
    .order-view {
        grid-template-columns: 1fr;
    }
    
    .cart-section {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        max-height: 50vh;
        border-radius: 1rem 1rem 0 0;
        box-shadow: 0 -4px 6px -1px rgb(0 0 0 / 0.1);
    }
}

/* Sección de productos */
.products-section {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #f9fafb;
}

.categories-header {
    background: white;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    flex-shrink: 0;
}

.category-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 80px;
}

.category-btn:hover {
    border-color: #3b82f6;
}

.category-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.category-icon {
    font-size: 1.5rem;
}

.category-name {
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}

.products-grid {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    align-content: start;
}

.product-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border-color: #3b82f6;
}

.product-image {
    font-size: 3rem;
    text-align: center;
    margin-bottom: 0.5rem;
}

.product-info {
    text-align: center;
}

.product-name {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.product-desc {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    font-size: 1rem;
    font-weight: 700;
    color: #10b981;
}

.loading-state,
.empty-products {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 3rem;
    color: #6b7280;
}

/* Sección del carrito */
.cart-section {
    background: white;
    border-left: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.cart-header {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.table-info-badge {
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.guests-control {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.guests-label {
    font-weight: 500;
    color: #374151;
}

.guests-buttons {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.guests-count {
    min-width: 2rem;
    text-align: center;
    font-weight: 600;
    font-size: 1.125rem;
}

.qty-btn {
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    background: #3b82f6;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1.125rem;
    font-weight: 600;
    transition: background 0.2s;
}

.qty-btn:hover {
    background: #2563eb;
}

.qty-btn:active {
    transform: scale(0.95);
}

.cart-items {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.empty-cart {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
}

.empty-hint {
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.cart-item {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 0.75rem;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.5rem;
}

.item-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    flex: 1;
}

.remove-btn {
    background: #ef4444;
    color: white;
    border: none;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.75rem;
}

.item-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-display {
    min-width: 1.5rem;
    text-align: center;
    font-weight: 600;
}

.item-price {
    font-weight: 700;
    color: #10b981;
}

.item-instructions {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 0.75rem;
}

.cart-totals {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.total-final {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 2px solid #e5e7eb;
}

.cart-actions {
    padding: 1rem;
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 0.75rem;
}

.btn-cancel,
.btn-save {
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-save {
    background: #10b981;
    color: white;
}

.btn-save:hover:not(:disabled) {
    background: #059669;
}

.btn-save:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-add-items {
    background: #f59e0b;
    color: white;
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-add-items:hover:not(:disabled) {
    background: #d97706;
}

.btn-add-items:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-close-account {
    background: #8b5cf6;
    color: white;
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-close-account:hover:not(:disabled) {
    background: #7c3aed;
}

.btn-close-account:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.saving-text {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.spinner-small,
.spinner-large {
    border: 2px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.spinner-small {
    width: 1rem;
    height: 1rem;
}

.spinner-large {
    width: 3rem;
    height: 3rem;
    border-width: 4px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
