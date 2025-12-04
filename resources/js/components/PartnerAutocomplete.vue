<script setup lang="ts">
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { debounce } from 'lodash-es';

interface Partner {
    id: number;
    name: string;
    code?: string;
    tax_id?: string;
}

interface Props {
    modelValue?: number | null;
    type?: 'suppliers' | 'customers' | null;
    placeholder?: string;
    initialName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: null,
    placeholder: 'Search partner by name, code or tax ID...',
    initialName: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
    'select': [partner: Partner];
}>();

const searchQuery = ref('');
const results = ref<Partner[]>([]);
const loading = ref(false);
const showResults = ref(false);
const selectedPartner = ref<Partner | null>(null);

// Set initial name if provided (for Edit mode)
if (props.initialName && props.modelValue) {
    searchQuery.value = props.initialName;
    selectedPartner.value = {
        id: props.modelValue,
        name: props.initialName,
    };
}

const searchPartners = debounce(async () => {
    if (searchQuery.value.length < 2) {
        results.value = [];
        return;
    }

    loading.value = true;
    try {
        let url = `/api/partners/search?q=${encodeURIComponent(searchQuery.value)}`;
        if (props.type) {
            url += `&type=${props.type}`;
        }
        
        const response = await fetch(url);
        if (response.ok) {
            results.value = await response.json();
            showResults.value = true;
        }
    } catch (error) {
        console.error('Error searching partners:', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
}, 300);

watch(searchQuery, () => {
    if (!selectedPartner.value) {
        searchPartners();
    }
});

const selectPartner = (partner: Partner) => {
    selectedPartner.value = partner;
    searchQuery.value = partner.name;
    emit('update:modelValue', partner.id);
    emit('select', partner);
    showResults.value = false;
};

const clearSelection = () => {
    selectedPartner.value = null;
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
    if (!target.closest('.partner-autocomplete')) {
        showResults.value = false;
    }
};

// Close dropdown when clicking outside
if (typeof window !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
}
</script>

<template>
    <div class="partner-autocomplete relative">
        <div class="relative">
            <Input 
                v-model="searchQuery" 
                :placeholder="placeholder"
                @focus="handleFocus"
                autocomplete="off"
            />
            <button
                v-if="selectedPartner"
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
                v-for="partner in results" 
                :key="partner.id"
                @click="selectPartner(partner)"
                class="p-3 hover:bg-gray-100 cursor-pointer border-b last:border-b-0"
            >
                <div class="font-medium text-sm">{{ partner.name }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    <span v-if="partner.code">Code: {{ partner.code }}</span>
                    <span v-if="partner.tax_id" class="ml-3">
                        Tax ID: {{ partner.tax_id }}
                    </span>
                </div>
            </div>
        </div>

        <div 
            v-if="showResults && !loading && results.length === 0 && searchQuery.length >= 2"
            class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg p-3 text-sm text-gray-500 text-center"
        >
            No partners found
        </div>
    </div>
</template>
