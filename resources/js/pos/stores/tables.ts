import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import type { Table, TableArea, TableStatus, ApiResponse } from '@pos/types';

export const useTablesStore = defineStore('tables', () => {
    // State
    const tables = ref<Table[]>([]);
    const areas = ref<TableArea[]>([]);
    const selectedArea = ref<number | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Getters
    const hasTables = computed(() => tables.value.length > 0);
    const hasAreas = computed(() => areas.value.length > 0);

    const availableTables = computed(() => 
        tables.value.filter(t => t.status === 'available')
    );

    const occupiedTables = computed(() => 
        tables.value.filter(t => t.status !== 'available')
    );

    // Actions
    async function fetchTables(areaId?: number): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const params = areaId ? { area_id: areaId } : {};
            const response = await axios.get<ApiResponse<Table[]>>('/api/pos/tables', { params });
            
            tables.value = response.data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al cargar mesas';
            console.error('Error fetching tables:', err);
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchAreas(): Promise<void> {
        try {
            const response = await axios.get<ApiResponse<TableArea[]>>('/api/pos/table-areas');
            areas.value = response.data.data;
        } catch (err: any) {
            console.error('Error fetching areas:', err);
            error.value = 'Error al cargar áreas';
        }
    }

    function selectArea(areaId: number | null): void {
        selectedArea.value = areaId;
        fetchTables(areaId || undefined);
    }

    function getTablesByStatus(status: TableStatus): Table[] {
        return tables.value.filter(table => table.status === status);
    }

    function getTableById(id: number): Table | undefined {
        return tables.value.find(t => t.id === id);
    }

    function getStatusText(status: TableStatus): string {
        const statusMap: Record<TableStatus, string> = {
            available: 'Disponible',
            occupied: 'Ocupada',
            preparing: 'Preparando',
            ready: 'Lista',
            serving: 'Sirviendo',
        };
        return statusMap[status] || status;
    }

    function clearTables(): void {
        tables.value = [];
        selectedArea.value = null;
    }

    return {
        // State
        tables,
        areas,
        selectedArea,
        isLoading,
        error,
        // Getters
        hasTables,
        hasAreas,
        availableTables,
        occupiedTables,
        // Actions
        fetchTables,
        fetchAreas,
        selectArea,
        getTablesByStatus,
        getTableById,
        getStatusText,
        clearTables,
    };
});

// Re-export types for convenience
export type { Table, TableArea, TableStatus };
