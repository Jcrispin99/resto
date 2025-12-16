import { createRouter, createWebHistory, type RouteRecordRaw, type NavigationGuardNext, type RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@pos/stores/auth';
import type { UserRole } from '@pos/types';

// Extend RouteMeta to include our custom properties
declare module 'vue-router' {
    interface RouteMeta {
        requiresAuth?: boolean;
        allowedRoles?: UserRole[];
        title?: string;
    }
}

const routes: RouteRecordRaw[] = [
    // ========================
    // Public Routes
    // ========================
    {
        path: '/pos/login',
        name: 'login',
        component: () => import('@pos/views/LoginView.vue'),
        meta: { 
            requiresAuth: false,
            title: 'Iniciar Sesión',
        },
    },

    // ========================
    // Redirect
    // ========================
    {
        path: '/pos',
        redirect: () => {
            const authStore = useAuthStore();
            if (authStore.isWaiter) return '/pos/tables';
            if (authStore.isCashier) return '/pos/cashier';
            return '/pos/tables'; // Default for admin
        },
    },

    // ========================
    // Waiter Routes (Mozo)
    // ========================
    {
        path: '/pos/tables',
        name: 'tables',
        component: () => import('@pos/views/TablesView.vue'),
        meta: { 
            requiresAuth: true,
            allowedRoles: ['waiter', 'admin'],
            title: 'Mesas',
        },
    },
    {
        path: '/pos/order/:tableId',
        name: 'order',
        component: () => import('@pos/views/OrderView.vue'),
        meta: { 
            requiresAuth: true,
            allowedRoles: ['waiter', 'admin'],
            title: 'Tomar Pedido',
        },
    },

    // ========================
    // Cashier Routes (Cajero)
    // ========================
    {
        path: '/pos/cashier',
        name: 'cashier',
        component: () => import('@pos/views/CashierView.vue'),
        meta: { 
            requiresAuth: true,
            allowedRoles: ['cashier', 'admin'],
            title: 'Caja',
        },
    },
    {
        path: '/pos/payment/:orderId',
        name: 'payment',
        component: () => import('@pos/views/PaymentView.vue'),
        meta: { 
            requiresAuth: true,
            allowedRoles: ['cashier', 'admin'],
            title: 'Procesar Pago',
        },
    },

    // ========================
    // Fallback
    // ========================
    {
        path: '/pos/:pathMatch(.*)*',
        redirect: '/pos/login',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Flag to disable auth check during development
const AUTH_REQUIRED = true; // Set to false for development without auth

// Navigation guard
router.beforeEach((to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
    const authStore = useAuthStore();
    
    // Skip auth check if disabled
    if (!AUTH_REQUIRED) {
        // Update document title
        if (to.meta.title) {
            document.title = `${to.meta.title} | POS`;
        }
        next();
        return;
    }

    const requiresAuth = to.meta.requiresAuth !== false;
    const allowedRoles = to.meta.allowedRoles as UserRole[] | undefined;

    // Not authenticated and route requires auth
    if (requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login', query: { redirect: to.fullPath } });
        return;
    }

    // Already authenticated and trying to access login
    if (to.name === 'login' && authStore.isAuthenticated) {
        // Redirect based on role
        if (authStore.isWaiter) {
            next({ name: 'tables' });
        } else if (authStore.isCashier) {
            next({ name: 'cashier' });
        } else {
            next({ name: 'tables' }); // Admin goes to tables by default
        }
        return;
    }

    // Check role-based access
    if (allowedRoles && allowedRoles.length > 0) {
        const userRole = authStore.userRole;
        
        if (!userRole || !allowedRoles.includes(userRole)) {
            // User doesn't have permission, redirect to their default route
            if (authStore.isWaiter) {
                next({ name: 'tables' });
            } else if (authStore.isCashier) {
                next({ name: 'cashier' });
            } else {
                next({ name: 'login' });
            }
            return;
        }
    }

    // Update document title
    if (to.meta.title) {
        document.title = `${to.meta.title} | POS`;
    }

    next();
});

export default router;
