import { defineStore } from 'pinia';
import axios from 'axios';
import type { User, UserRole } from '@pos/types';

interface AuthState {
    user: User | null;
    token: string | null;
    isLoading: boolean;
    error: string | null;
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        user: JSON.parse(localStorage.getItem('pos_user') || 'null'),
        token: localStorage.getItem('pos_token'),
        isLoading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state): boolean => !!state.token && !!state.user,

        // Role detection based on user.roles array from Spatie
        userRole: (state): UserRole | null => {
            if (!state.user?.roles?.length) return null;
            
            const roles = state.user.roles;
            // Priority: super-admin > admin > manager > cashier > waiter > kitchen
            if (roles.includes('super-admin')) return 'admin';
            if (roles.includes('admin')) return 'admin';
            if (roles.includes('manager')) return 'admin';
            if (roles.includes('cashier')) return 'cashier';
            if (roles.includes('waiter')) return 'waiter';
            if (roles.includes('kitchen')) return 'waiter'; // Kitchen uses waiter-like access
            return null;
        },

        isAdmin: (state): boolean => {
            return state.user?.roles?.some(r => ['super-admin', 'admin', 'manager'].includes(r)) ?? false;
        },

        isWaiter: (state): boolean => {
            return state.user?.roles?.includes('waiter') ?? false;
        },

        isCashier: (state): boolean => {
            return state.user?.roles?.includes('cashier') ?? false;
        },

        isKitchen: (state): boolean => {
            return state.user?.roles?.includes('kitchen') ?? false;
        },

        // Permission-based access
        canAccessTables(): boolean {
            return this.isAdmin || this.isWaiter;
        },

        canAccessCashier(): boolean {
            return this.isAdmin || this.isCashier;
        },

        canAccessKitchen(): boolean {
            return this.isAdmin || this.isKitchen;
        },

        // Check specific permission
        hasPermission: (state) => (permission: string): boolean => {
            return state.user?.permissions?.includes(permission) ?? false;
        },
    },

    actions: {
        /**
         * Login with real API
         */
        async login(email: string, password: string): Promise<boolean> {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.post('/api/login', {
                    email,
                    password,
                    device_name: 'POS Web',
                });

                if (response.data.success) {
                    const { user, token } = response.data.data;

                    this.user = user;
                    this.token = token;

                    // Persist to localStorage
                    localStorage.setItem('pos_token', token);
                    localStorage.setItem('pos_user', JSON.stringify(user));

                    // Set axios default header
                    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

                    return true;
                }

                this.error = 'Error en el login';
                return false;
            } catch (error: unknown) {
                if (axios.isAxiosError(error) && error.response?.data?.message) {
                    this.error = error.response.data.message;
                } else if (axios.isAxiosError(error) && error.response?.data?.errors?.email) {
                    this.error = error.response.data.errors.email[0];
                } else {
                    this.error = 'Error de conexión';
                }
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Logout and clear session
         */
        async logout(): Promise<void> {
            try {
                if (this.token) {
                    await axios.post('/api/logout', {}, {
                        headers: { Authorization: `Bearer ${this.token}` }
                    });
                }
            } catch {
                // Ignore errors on logout
            } finally {
                this.clearSession();
            }
        },

        /**
         * Clear local session data
         */
        clearSession(): void {
            this.user = null;
            this.token = null;
            this.error = null;

            localStorage.removeItem('pos_token');
            localStorage.removeItem('pos_user');
            delete axios.defaults.headers.common['Authorization'];
        },

        /**
         * Initialize auth state from localStorage
         */
        initializeAuth(): void {
            const token = localStorage.getItem('pos_token');
            const userJson = localStorage.getItem('pos_user');

            if (token && userJson) {
                this.token = token;
                this.user = JSON.parse(userJson);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            }
        },

        /**
         * Check if user has a specific role
         */
        hasRole(role: string): boolean {
            return this.user?.roles?.includes(role) ?? false;
        },
    },
});
