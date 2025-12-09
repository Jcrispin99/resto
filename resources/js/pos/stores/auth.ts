import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import type { User, UserRole } from '@pos/types';

// Mock users for development
const MOCK_USERS: Record<string, { user: User; password: string }> = {
    'mozo@demo.com': {
        password: '123456',
        user: {
            id: 1,
            name: 'Carlos Mesero',
            email: 'mozo@demo.com',
            role: 'waiter',
            branch_id: 1,
        },
    },
    'cajero@demo.com': {
        password: '123456',
        user: {
            id: 2,
            name: 'María Cajera',
            email: 'cajero@demo.com',
            role: 'cashier',
            branch_id: 1,
        },
    },
    'admin@demo.com': {
        password: '123456',
        user: {
            id: 3,
            name: 'Admin POS',
            email: 'admin@demo.com',
            role: 'admin',
            branch_id: 1,
        },
    },
};

// Flag to toggle between mock and real API
const USE_MOCK = true;

export const useAuthStore = defineStore('auth', () => {
    // State
    const user = ref<User | null>(null);
    const token = ref<string | null>(localStorage.getItem('pos_token'));
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Getters
    const isAuthenticated = computed(() => !!token.value && !!user.value);
    
    const userRole = computed((): UserRole | null => {
        return user.value?.role || null;
    });

    const isWaiter = computed(() => user.value?.role === 'waiter');
    const isCashier = computed(() => user.value?.role === 'cashier');
    const isAdmin = computed(() => user.value?.role === 'admin');

    const canAccessTables = computed(() => {
        return isWaiter.value || isAdmin.value;
    });

    const canAccessCashier = computed(() => {
        return isCashier.value || isAdmin.value;
    });

    // Actions
    async function login(email: string, password: string) {
        isLoading.value = true;
        error.value = null;

        try {
            if (USE_MOCK) {
                // Mock login
                const mockUser = MOCK_USERS[email.toLowerCase()];
                
                if (!mockUser || mockUser.password !== password) {
                    throw new Error('Credenciales inválidas');
                }

                // Simulate API delay
                await new Promise(resolve => setTimeout(resolve, 500));

                const mockToken = `mock_token_${Date.now()}`;
                token.value = mockToken;
                user.value = mockUser.user;

                // Save to localStorage
                localStorage.setItem('pos_token', mockToken);
                localStorage.setItem('pos_user', JSON.stringify(mockUser.user));

                // Configure axios for future requests
                axios.defaults.headers.common['Authorization'] = `Bearer ${mockToken}`;

                return { success: true, user: mockUser.user };
            } else {
                // Real API login
                const response = await axios.post('/api/login', {
                    email,
                    password,
                    device_name: 'POS Tablet',
                });

                token.value = response.data.data.token;
                user.value = response.data.data.user;

                localStorage.setItem('pos_token', token.value!);
                localStorage.setItem('pos_user', JSON.stringify(user.value));

                axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

                return response.data;
            }
        } catch (err: any) {
            error.value = err.message || 'Error al iniciar sesión';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    async function logout() {
        isLoading.value = true;

        try {
            if (!USE_MOCK && token.value) {
                await axios.post('/api/logout');
            }
        } catch (err) {
            console.error('Logout error:', err);
        } finally {
            // Always clear local state
            token.value = null;
            user.value = null;
            error.value = null;
            
            localStorage.removeItem('pos_token');
            localStorage.removeItem('pos_user');
            delete axios.defaults.headers.common['Authorization'];
            
            isLoading.value = false;
        }
    }

    function initializeFromStorage() {
        const savedToken = localStorage.getItem('pos_token');
        const savedUser = localStorage.getItem('pos_user');

        if (savedToken && savedUser) {
            try {
                token.value = savedToken;
                user.value = JSON.parse(savedUser);
                axios.defaults.headers.common['Authorization'] = `Bearer ${savedToken}`;
            } catch (err) {
                // Invalid stored data, clear it
                localStorage.removeItem('pos_token');
                localStorage.removeItem('pos_user');
            }
        }
    }

    function hasRole(role: UserRole): boolean {
        return user.value?.role === role;
    }

    function hasAnyRole(roles: UserRole[]): boolean {
        return roles.includes(user.value?.role as UserRole);
    }

    // Initialize on store creation
    initializeFromStorage();

    return {
        // State
        user,
        token,
        isLoading,
        error,
        // Getters
        isAuthenticated,
        userRole,
        isWaiter,
        isCashier,
        isAdmin,
        canAccessTables,
        canAccessCashier,
        // Actions
        login,
        logout,
        initializeFromStorage,
        hasRole,
        hasAnyRole,
    };
});
