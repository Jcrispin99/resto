import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import type { Product, Category, ApiResponse } from '@pos/types';

export const useProductsStore = defineStore('products', () => {
    // State
    const products = ref<Product[]>([]);
    const categories = ref<Category[]>([]);
    const selectedCategory = ref<number | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Getters
    const hasProducts = computed(() => products.value.length > 0);
    const hasCategories = computed(() => categories.value.length > 0);

    // Actions
    async function fetchCategories(): Promise<void> {
        try {
            const response = await axios.get<ApiResponse<Category[]>>('/api/pos/categories');
            categories.value = response.data.data;
        } catch (err: any) {
            console.error('Error fetching categories:', err);
            error.value = 'Error al cargar categorías';
        }
    }

    async function fetchProducts(categoryId?: number): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const params = categoryId ? { category_id: categoryId } : {};
            const response = await axios.get<ApiResponse<Product[]>>('/api/pos/products', { params });
            
            products.value = response.data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cargar productos';
            console.error('Error fetching products:', err);
        } finally {
            isLoading.value = false;
        }
    }

    function selectCategory(categoryId: number | null): void {
        selectedCategory.value = categoryId;
        fetchProducts(categoryId || undefined);
    }

    function getProductById(id: number): Product | undefined {
        return products.value.find(p => p.id === id);
    }

    function clearProducts(): void {
        products.value = [];
        selectedCategory.value = null;
    }

    return {
        // State
        products,
        categories,
        selectedCategory,
        isLoading,
        error,
        // Getters
        hasProducts,
        hasCategories,
        // Actions
        fetchCategories,
        fetchProducts,
        selectCategory,
        getProductById,
        clearProducts,
    };
});

// Re-export types for convenience
export type { Product, Category };
