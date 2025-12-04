<script setup lang="ts">
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { debounce } from 'lodash-es';

interface Product {
    id: number;
    name: string;
    sku?: string;
    barcode?: string;
    sale_price?: number;
}

interface Props {
    modelValue?: number | null;
    placeholder?: string;
    initialName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Search product by name or SKU...',
    initialName: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
    'select': [product: Product];
}>();

const searchQuery = ref('');
const results = ref<Product[]>([]);
const loading = ref(false);
const showResults = ref(false);
const selectedProduct = ref<Product | null>(null);

// Set initial name if provided (for Edit mode)
if (props.initialName && props.modelValue) {
    searchQuery.value = props.initialName;
    selectedProduct.value = {
        id: props.modelValue,
        name: props.initialName,
    };
}

const searchProducts = debounce(async () => {
    if (searchQuery.value.length < 2) {
        results.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await fetch(`/api/products/search?q=${encodeURIComponent(searchQuery.value)}`);
        if (response.ok) {
            results.value = await response.json();
            showResults.value = true;
        }
    } catch (error) {
        console.error('Error searching products:', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
}, 300);

watch(searchQuery, () => {
    if (!selectedProduct.value) {
        searchProducts();
    }
});

const selectProduct = (product: Product) => {
    selectedProduct.value = product;
    searchQuery.value = product.name;
    emit('update:modelValue', product.id);
    emit('select', product);
    showResults.value = false;
};

const clearSelection = () => {
    selectedProduct.value = null;
    searchQuery.value = '';
    emit('update:modelValue', null);
    results.value = [];
    showResults.value = false;
};

const handleFocus = () => {
    if (results.value.length > 0) {
        showResults.value = true;
    }
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.product-autocomplete')) {
        showResults.value = false;
    }
};

// Close dropdown when clicking outside
if (typeof window !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
}
</script>

<template>
    <div class="product-autocomplete relative">
        <div class="relative">
            <Input 
                v-model="searchQuery" 
                :placeholder="placeholder"
                @focus="handleFocus"
                autocomplete="off"
            />
            <button
                v-if="selectedProduct"
                type="button"
                @click="clearSelection"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                ✕
            </button>
        </div>
        
        <div 
            v-if="showResults && (results.length > 0 || loading)" 
            class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
        >
            <div v-if="loading" class="p-3 text-sm text-gray-500 text-center">
                Searching...
            </div>
            <div 
                v-else 
                v-for="product in results" 
                :key="product.id"
                @click="selectProduct(product)"
                class="p-3 hover:bg-gray-100 cursor-pointer border-b last:border-b-0"
            >
                <div class="font-medium text-sm">{{ product.name }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span v-if="product.sku">SKU: {{ product.sku }}</span>
                    <span v-if="product.sale_price" class="ml-3">
                        S/. {{ Number(product.sale_price).toFixed(2) }}
                    </span>
                </div>
            </div>
        </div>

        <div 
            v-if="showResults && !loading && results.length === 0 && searchQuery.length >= 2"
            class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg p-3 text-sm text-gray-500 text-center"
        >
            No products found
        </div>
    </div>
</template>
