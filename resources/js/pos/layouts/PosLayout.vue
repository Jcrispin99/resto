<template>
    <div class="pos-layout">
        <!-- Header -->
        <header class="pos-header">
            <div class="header-left">
                <div class="logo">
                    <span class="logo-icon">🍽️</span>
                    <span class="logo-text">{{ appName }}</span>
                </div>
            </div>
            
            <div class="header-center">
                <div class="current-time">
                    {{ currentTime }}
                </div>
            </div>
            
            <div class="header-right">
                <!-- User Info -->
                <div v-if="authStore.user" class="user-info">
                    <div class="user-avatar" :class="`role-${authStore.userRole}`">
                        {{ userInitials }}
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ authStore.user.name }}</div>
                        <div class="user-role">{{ roleLabel }}</div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <nav class="nav-buttons">
                    <!-- Tables -->
                    <button
                        @click="goToTables"
                        class="nav-btn"
                        :class="{ active: isRoute('tables') || isRoute('order') }"
                        title="Mesas"
                    >
                        🍽️
                        <span class="btn-text">Mesas</span>
                    </button>

                    <!-- Cashier -->
                    <button
                        @click="goToCashier"
                        class="nav-btn nav-cashier"
                        :class="{ active: isRoute('cashier') || isRoute('payment') }"
                        title="Caja"
                    >
                        💳
                        <span class="btn-text">Caja</span>
                    </button>
                </nav>

                <!-- Logout Button -->
                <button 
                    v-if="authStore.isAuthenticated"
                    @click="handleLogout" 
                    class="btn-logout"
                    :class="{ 'is-loading': isLoggingOut }"
                >
                    <span v-if="!isLoggingOut">🚪</span>
                    <span v-else class="spinner"></span>
                    <span class="btn-text">Salir</span>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pos-content">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@pos/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const currentTime = ref('');
const isLoggingOut = ref(false);
let timeInterval: number | null = null;

const appName = computed(() => 'POS Restaurant');

const userInitials = computed(() => {
    if (!authStore.user) return '??';
    const names = authStore.user.name.split(' ');
    return names.map((n: string) => n[0]).join('').substring(0, 2).toUpperCase();
});

const roleLabel = computed(() => {
    const labels: Record<string, string> = {
        waiter: 'Mozo',
        cashier: 'Cajero',
        admin: 'Administrador',
    };
    return labels[authStore.userRole || ''] || 'Usuario';
});

function isRoute(name: string): boolean {
    return route.name === name;
}

function updateTime() {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('es-PE', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });
}

function goToTables() {
    router.push({ name: 'tables' });
}

function goToCashier() {
    router.push({ name: 'cashier' });
}

async function handleLogout() {
    if (isLoggingOut.value) return;
    
    const confirmed = confirm('¿Seguro que quieres cerrar sesión?');
    if (!confirmed) return;
    
    isLoggingOut.value = true;
    
    try {
        await authStore.logout();
        router.push({ name: 'login' });
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    } finally {
        isLoggingOut.value = false;
    }
}

onMounted(() => {
    updateTime();
    timeInterval = window.setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (timeInterval) {
        clearInterval(timeInterval);
    }
});
</script>

<style scoped>
.pos-layout {
    width: 100vw;
    height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: #f9fafb;
}

/* Header */
.pos-header {
    height: 4rem;
    background-color: #2563eb;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    flex-shrink: 0;
}

.header-left,
.header-center,
.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    font-size: 1.875rem;
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
}

@media (max-width: 768px) {
    .logo-text {
        display: none;
    }
}

.current-time {
    font-family: ui-monospace, monospace;
    font-size: 1.25rem;
    font-weight: 600;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.user-avatar.role-waiter {
    background-color: #10b981;
}

.user-avatar.role-cashier {
    background-color: #f59e0b;
}

.user-avatar.role-admin {
    background-color: #8b5cf6;
}

.user-details {
    text-align: left;
}

@media (max-width: 1024px) {
    .user-details {
        display: none;
    }
}

.user-name {
    font-size: 0.875rem;
    font-weight: 500;
}

.user-role {
    font-size: 0.75rem;
    color: #bfdbfe;
}

/* Navigation */
.nav-buttons {
    display: flex;
    gap: 0.5rem;
}

.nav-btn {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 44px;
    min-height: 44px;
    border: 2px solid transparent;
    cursor: pointer;
    font-size: 1rem;
}

.nav-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.nav-btn.active {
    background-color: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
}

.nav-cashier.active {
    background-color: #10b981;
}

.btn-logout {
    background-color: #ef4444;
    color: white;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 44px;
    min-height: 44px;
    border: none;
    cursor: pointer;
}

.btn-logout:hover {
    background-color: #dc2626;
}

.btn-logout:active {
    background-color: #b91c1c;
    transform: scale(0.95);
}

.btn-logout.is-loading {
    opacity: 0.75;
    cursor: not-allowed;
}

.btn-text {
    margin-left: 0.25rem;
}

@media (max-width: 768px) {
    .btn-text {
        display: none;
    }
}

.spinner {
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

/* Main Content */
.pos-content {
    flex: 1;
    overflow: auto;
    background-color: #f9fafb;
}

/* Responsive */
@media (max-width: 768px) {
    .pos-header {
        height: 3.5rem;
        padding: 0 0.75rem;
    }
    
    .header-center {
        display: none;
    }
    
    .logo-icon {
        font-size: 1.5rem;
    }
    
    .user-avatar {
        width: 2rem;
        height: 2rem;
        font-size: 0.75rem;
    }
}
</style>
