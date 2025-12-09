<template>
    <PosLayout>
        <div class="tables-view">
            <!-- Header con filtros -->
            <div class="view-header">
                <h1 class="view-title">🍽️ Mesas</h1>
                
                <!-- Filtro por área -->
                <div class="area-filters">
                    <button
                        @click="selectArea(null)"
                        :class="['area-btn', { active: selectedArea === null }]"
                    >
                        Todas
                    </button>
                    <button
                        v-for="area in areas"
                        :key="area.id"
                        @click="selectArea(area.id)"
                        :class="['area-btn', { active: selectedArea === area.id }]"
                    >
                        {{ area.name }}
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="loading-container">
                <div class="spinner-large"></div>
                <p>Cargando mesas...</p>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="error-container">
                <p>❌ {{ error }}</p>
                <button @click="refreshTables" class="retry-btn">
                    Reintentar
                </button>
            </div>

            <!-- Grid de mesas -->
            <div v-else class="tables-grid">
                <div
                    v-for="table in tables"
                    :key="table.id"
                    @click="handleTableClick(table)"
                    :class="['table-card', `status-${table.status}`]"
                >
                    <!-- Número de mesa -->
                    <div class="table-number">{{ table.number }}</div>
                    
                    <!-- Estado -->
                    <div class="table-status">
                        <span class="status-dot"></span>
                        {{ getStatusText(table.status) }}
                    </div>
                    
                    <!-- Info adicional -->
                    <div class="table-info">
                        <div class="info-item">
                            <span class="icon">👥</span>
                            <span>{{ table.capacity }} personas</span>
                        </div>
                        
                        <div v-if="table.current_order" class="info-item">
                            <span class="icon">🍴</span>
                            <span>{{ table.current_order.guests_count }} comensales</span>
                        </div>
                    </div>

                    <!-- Orden activa -->
                    <div v-if="table.current_order" class="order-badge">
                        {{ table.current_order.order_number }}
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!isLoading && !error && tables.length === 0" class="empty-state">
                <p>📭 No hay mesas disponibles</p>
            </div>
        </div>
    </PosLayout>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useTablesStore, type Table } from '@pos/stores/tables';
import PosLayout from '@pos/layouts/PosLayout.vue';

const router = useRouter();
const tablesStore = useTablesStore();

const tables = computed(() => tablesStore.tables);
const areas = computed(() => tablesStore.areas);
const selectedArea = computed(() => tablesStore.selectedArea);
const isLoading = computed(() => tablesStore.isLoading);
const error = computed(() => tablesStore.error);

function selectArea(areaId: number | null) {
    tablesStore.selectArea(areaId);
}

function refreshTables() {
    tablesStore.fetchTables(selectedArea.value || undefined);
}

function getStatusText(status: string): string {
    const statusMap: Record<string, string> = {
        available: 'Disponible',
        occupied: 'Ocupada',
        preparing: 'Preparando',
        ready: 'Lista',
        serving: 'Sirviendo',
    };
    return statusMap[status] || status;
}

function handleTableClick(table: Table) {
    if (table.status === 'available') {
        // Nueva orden
        router.push({ name: 'order', params: { tableId: table.id } });
    } else if (table.current_order) {
        // Orden existente
        router.push({ name: 'order', params: { tableId: table.id } });
    }
}

onMounted(async () => {
    await tablesStore.fetchAreas();
    await tablesStore.fetchTables();
});
</script>

<style scoped>
.tables-view {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.view-header {
    background: white;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.view-title {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #1f2937;
}

.area-filters {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.area-btn {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    border: 2px solid #e5e7eb;
    background: white;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.area-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.area-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

/* Loading y Error */
.loading-container,
.error-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    color: #6b7280;
}

.spinner-large {
    width: 3rem;
    height: 3rem;
    border: 4px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.retry-btn {
    padding: 0.75rem 1.5rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.retry-btn:hover {
    background: #2563eb;
}

/* Grid de mesas */
.tables-grid {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    align-content: start;
}

@media (min-width: 640px) {
    .tables-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
}

@media (min-width: 1024px) {
    .tables-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }
}

/* Card de mesa */
.table-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    border: 3px solid;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    min-height: 180px;
    display: flex;
    flex-direction: column;
}

.table-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
}

.table-card:active {
    transform: translateY(-2px);
}

/* Estados de mesa */
.status-available {
    border-color: #10b981;
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
}

.status-occupied {
    border-color: #ef4444;
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
}

.status-preparing {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
}

.status-ready {
    border-color: #06b6d4;
    background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
}

.status-serving {
    border-color: #8b5cf6;
    background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);
}

.table-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.table-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-dot {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background: currentColor;
}

.status-available .table-status {
    color: #10b981;
}

.status-occupied .table-status {
    color: #ef4444;
}

.status-preparing .table-status {
    color: #f59e0b;
}

.status-ready .table-status {
    color: #06b6d4;
}

.status-serving .table-status {
    color: #8b5cf6;
}

.table-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.icon {
    font-size: 1rem;
}

.order-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #1f2937;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.empty-state {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    color: #9ca3af;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .view-header {
        padding: 1rem;
    }
    
    .view-title {
        font-size: 1.5rem;
    }
    
    .tables-grid {
        padding: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 0.75rem;
    }
    
    .table-card {
        padding: 1rem;
        min-height: 150px;
    }
    
    .table-number {
        font-size: 1.5rem;
    }
}
</style>
