<template>
    <div class="pos-app">
        <router-view />
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';

onMounted(() => {
    console.log('PosApp component mounted');
});
</script>

<style>
/* Estilos globales del POS */
.pos-app {
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    background: #f5f6fa;
}
</style>
