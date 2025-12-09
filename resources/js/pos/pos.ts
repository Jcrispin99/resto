import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import PosApp from './PosApp.vue';

console.log('🍽️ POS App iniciando...');

// Crear app Vue
const app = createApp(PosApp);

// Pinia para state management
const pinia = createPinia();
app.use(pinia);

// Router
app.use(router);

// Montar en #pos-app
app.mount('#pos-app');

console.log('✅ POS App montada correctamente');
