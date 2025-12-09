// ========================
// User & Auth Types
// ========================

export type UserRole = 'waiter' | 'cashier' | 'admin';

export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    branch_id?: number;
}

export interface AuthState {
    user: User | null;
    token: string | null;
    isAuthenticated: boolean;
}

// ========================
// Table Types
// ========================

export type TableStatus = 'available' | 'occupied' | 'preparing' | 'ready' | 'serving';

export interface TableArea {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
}

export interface Table {
    id: number;
    number: string;
    capacity: number;
    is_active: boolean;
    status: TableStatus;
    area: TableArea;
    current_order: OrderSummary | null;
}

// ========================
// Product Types
// ========================

export interface Category {
    id: number;
    name: string;
    type: string;
    icon: string;
}

export interface KitchenStation {
    id: number;
    name: string;
}

export interface Product {
    id: number;
    name: string;
    description: string | null;
    sale_price: number;
    internal_reference: string | null;
    barcode: string | null;
    product_type: string;
    image: string | null;
    category: Category | null;
    kitchen_station: KitchenStation | null;
}

// ========================
// Order Types
// ========================

export type OrderStatus = 'pending' | 'preparing' | 'ready' | 'completed' | 'cancelled';
export type PaymentStatus = 'unpaid' | 'partial' | 'paid';

export interface OrderItem {
    id: number;
    product_template_id: number;
    product: Product;
    quantity: number;
    unit_price: number;
    subtotal: number;
    tax_amount: number;
    total: number;
    status: string;
    special_instructions: string | null;
}

export interface OrderSummary {
    id: number;
    order_number: string;
    guests_count: number;
    status: OrderStatus;
    total: number;
}

export interface Order {
    id: number;
    order_number: string;
    branch_id: number;
    table_id: number | null;
    waiter_id: number;
    order_type: 'dine_in' | 'takeout' | 'delivery';
    status: OrderStatus;
    payment_status: PaymentStatus;
    guests_count: number;
    subtotal: number;
    tax: number;
    discount: number;
    total: number;
    order_date: string;
    paid_at: string | null;
    completed_time: string | null;
    table: Table | null;
    waiter: User | null;
    items: OrderItem[];
    payments: OrderPayment[];
    created_at: string;
    updated_at: string;
}

// ========================
// Payment Types
// ========================

export interface PaymentMethod {
    id: number;
    code: string;
    name: string;
    is_active: boolean;
}

export interface OrderPayment {
    id: number;
    order_id: number;
    payment_method_id: number;
    payment_method: PaymentMethod;
    amount: number;
    reference_number: string | null;
    payment_date: string;
    status: 'pending' | 'completed' | 'cancelled' | 'refunded';
}

// ========================
// Cart Types (for creating orders)
// ========================

export interface CartItem {
    product: Product;
    quantity: number;
    unit_price: number;
    special_instructions: string;
}

export interface CreateOrderPayload {
    table_id: number | null;
    guests_count: number;
    order_type?: 'dine_in' | 'takeout' | 'delivery';
    items: {
        product_template_id: number;
        quantity: number;
        unit_price: number;
        special_instructions: string | null;
    }[];
}

export interface ProcessPaymentPayload {
    payment_method_id: number;
    amount: number;
    reference_number?: string;
    cash_register_id?: number;
}

// ========================
// API Response Types
// ========================

export interface ApiResponse<T> {
    success: boolean;
    message?: string;
    data: T;
}
