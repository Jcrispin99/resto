<template>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1 class="login-title">🍽️ POS Restaurant</h1>
                <p class="login-subtitle">Ingresa tus credenciales</p>
            </div>

            <form @submit.prevent="handleLogin" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        placeholder="usuario@pos.com"
                        required
                        :disabled="isLoading"
                    />
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        placeholder="••••••"
                        required
                        :disabled="isLoading"
                    />
                </div>

                <div v-if="error" class="error-message">
                    ❌ {{ error }}
                </div>

                <button type="submit" class="btn-login" :disabled="isLoading">
                    <span v-if="!isLoading">Ingresar</span>
                    <span v-else class="spinner"></span>
                </button>
            </form>

            <!-- Demo users -->
            <div class="demo-section">
                <p class="demo-title">👤 Usuarios de prueba:</p>
                <div class="demo-buttons">
                    <button @click="fillDemo('mozo@pos.com')" class="demo-btn waiter">
                        🍽️ Mozo
                    </button>
                    <button @click="fillDemo('cajero@pos.com')" class="demo-btn cashier">
                        💳 Cajero
                    </button>
                    <button @click="fillDemo('admin@pos.com')" class="demo-btn admin">
                        👑 Admin
                    </button>
                </div>
                <p class="demo-hint">Contraseña: 123456</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@pos/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('123456');

const isLoading = computed(() => authStore.isLoading);
const error = computed(() => authStore.error);

function fillDemo(demoEmail: string) {
    email.value = demoEmail;
    password.value = '123456';
}

async function handleLogin() {
    const success = await authStore.login(email.value, password.value);

    if (success) {
        // Redirect based on role
        if (authStore.isWaiter) {
            router.push({ name: 'tables' });
        } else if (authStore.isCashier) {
            router.push({ name: 'cashier' });
        } else {
            router.push({ name: 'tables' }); // Admin goes to tables
        }
    }
}
</script>

<style scoped>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem;
}

.login-box {
    background: white;
    border-radius: 1rem;
    padding: 2.5rem;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: #6b7280;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.form-group input {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.form-group input:disabled {
    background: #f9fafb;
}

.error-message {
    padding: 0.75rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 0.5rem;
    color: #dc2626;
    font-size: 0.875rem;
}

.btn-login {
    padding: 0.875rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-login:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgb(102 126 234 / 0.4);
}

.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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

.demo-section {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.demo-title {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.75rem;
}

.demo-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.demo-btn {
    padding: 0.5rem 1rem;
    border: 2px solid;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.demo-btn.waiter {
    border-color: #10b981;
    color: #10b981;
}

.demo-btn.waiter:hover {
    background: #10b981;
    color: white;
}

.demo-btn.cashier {
    border-color: #3b82f6;
    color: #3b82f6;
}

.demo-btn.cashier:hover {
    background: #3b82f6;
    color: white;
}

.demo-btn.admin {
    border-color: #f59e0b;
    color: #f59e0b;
}

.demo-btn.admin:hover {
    background: #f59e0b;
    color: white;
}

.demo-hint {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.5rem;
}
</style>
