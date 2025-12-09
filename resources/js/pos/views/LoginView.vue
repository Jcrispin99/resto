<template>
    <div class="login-view">
        <div class="login-container">
            <!-- Logo y Título -->
            <div class="login-header">
                <div class="logo-large">🍽️</div>
                <h1 class="title">POS Restaurant</h1>
                <p class="subtitle">Sistema de Punto de Venta</p>
            </div>

            <!-- Formulario de Login -->
            <form @submit.prevent="handleLogin" class="login-form">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <span class="label-icon">📧</span>
                        Correo Electrónico
                    </label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="usuario@ejemplo.com"
                        class="form-input"
                        :disabled="isLoading"
                    />
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <span class="label-icon">🔒</span>
                        Contraseña
                    </label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="form-input"
                        :disabled="isLoading"
                    />
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="error-message">
                    ⚠️ {{ errorMessage }}
                </div>

                <!-- Login Button -->
                <button
                    type="submit"
                    class="btn-login"
                    :disabled="isLoading"
                    :class="{ 'is-loading': isLoading }"
                >
                    <span v-if="!isLoading">Iniciar Sesión</span>
                    <span v-else class="loading-content">
                        <span class="spinner"></span>
                        Ingresando...
                    </span>
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="demo-section">
                <p class="demo-title">👤 Usuarios Demo</p>
                <div class="demo-users">
                    <button 
                        type="button" 
                        @click="setCredentials('mozo@demo.com')" 
                        class="demo-btn demo-waiter"
                        :disabled="isLoading"
                    >
                        <span class="demo-icon">🍽️</span>
                        <span class="demo-role">Mozo</span>
                        <span class="demo-email">mozo@demo.com</span>
                    </button>
                    <button 
                        type="button" 
                        @click="setCredentials('cajero@demo.com')" 
                        class="demo-btn demo-cashier"
                        :disabled="isLoading"
                    >
                        <span class="demo-icon">💳</span>
                        <span class="demo-role">Cajero</span>
                        <span class="demo-email">cajero@demo.com</span>
                    </button>
                    <button 
                        type="button" 
                        @click="setCredentials('admin@demo.com')" 
                        class="demo-btn demo-admin"
                        :disabled="isLoading"
                    >
                        <span class="demo-icon">👑</span>
                        <span class="demo-role">Admin</span>
                        <span class="demo-email">admin@demo.com</span>
                    </button>
                </div>
                <p class="demo-password">Contraseña: <code>123456</code></p>
            </div>
        </div>

        <!-- Background Decoration -->
        <div class="background-decoration"></div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@pos/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const email = ref('mozo@demo.com');
const password = ref('123456');
const isLoading = ref(false);
const errorMessage = ref('');

function setCredentials(userEmail: string) {
    email.value = userEmail;
    password.value = '123456';
    errorMessage.value = '';
}

async function handleLogin() {
    if (isLoading.value) return;

    isLoading.value = true;
    errorMessage.value = '';

    try {
        await authStore.login(email.value, password.value);
        
        // Redirect based on role
        if (authStore.isWaiter) {
            router.push('/pos/tables');
        } else if (authStore.isCashier) {
            router.push('/pos/cashier');
        } else {
            // Admin - go to intended page or tables
            const redirectTo = route.query.redirect as string || '/pos/tables';
            router.push(redirectTo);
        }
    } catch (error: any) {
        console.error('Login failed:', error);
        errorMessage.value = error.message || 'Error al iniciar sesión';
    } finally {
        isLoading.value = false;
    }
}
</script>

<style scoped>
.login-view {
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, rgb(59 130 246) 0%, rgb(79 70 229) 100%);
}

.background-decoration {
    position: absolute;
    inset: 0;
    opacity: 0.1;
    background-image: 
        radial-gradient(circle at 20% 50%, white 1px, transparent 1px),
        radial-gradient(circle at 80% 80%, white 1px, transparent 1px);
    background-size: 50px 50px;
}

.login-container {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    width: 100%;
    max-width: 28rem;
    margin: 0 1rem;
    padding: 2rem;
    position: relative;
    z-index: 10;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.logo-large {
    font-size: 3.5rem;
    margin-bottom: 0.5rem;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.subtitle {
    color: #6b7280;
    font-size: 0.875rem;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.label-icon {
    font-size: 1rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 2px solid #e5e7eb;
    transition: all 0.2s;
    font-size: 1rem;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}

.error-message {
    background-color: #fef2f2;
    border: 2px solid #fecaca;
    color: #b91c1c;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.btn-login {
    width: 100%;
    padding: 0.875rem;
    border-radius: 0.5rem;
    background-color: #2563eb;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.2s;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border: none;
    cursor: pointer;
    min-height: 48px;
}

.btn-login:hover:not(:disabled) {
    background-color: #1d4ed8;
}

.btn-login:active:not(:disabled) {
    transform: scale(0.98);
}

.btn-login:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.loading-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.spinner {
    display: inline-block;
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid white;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Demo Section */
.demo-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.demo-title {
    text-align: center;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.75rem;
}

.demo-users {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.demo-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    padding: 0.75rem 0.5rem;
    border-radius: 0.5rem;
    border: 2px solid #e5e7eb;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.demo-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.demo-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.demo-waiter:hover:not(:disabled) {
    border-color: #10b981;
    background: #f0fdf4;
}

.demo-cashier:hover:not(:disabled) {
    border-color: #3b82f6;
    background: #eff6ff;
}

.demo-admin:hover:not(:disabled) {
    border-color: #f59e0b;
    background: #fffbeb;
}

.demo-icon {
    font-size: 1.5rem;
}

.demo-role {
    font-size: 0.75rem;
    font-weight: 600;
    color: #374151;
}

.demo-email {
    font-size: 0.625rem;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.demo-password {
    text-align: center;
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.75rem;
}

.demo-password code {
    background: #f3f4f6;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-family: monospace;
}

@media (max-width: 640px) {
    .login-container {
        padding: 1.5rem;
    }
    
    .title {
        font-size: 1.5rem;
    }
    
    .demo-users {
        grid-template-columns: 1fr;
    }
    
    .demo-btn {
        flex-direction: row;
        justify-content: flex-start;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
    }
}
</style>
