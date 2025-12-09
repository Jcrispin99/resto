# 🚀 Guía de Inicio - POS Frontend

## ✅ Estado Actual

**Backend API:** ✅ Completado (33 endpoints)  
**Frontend Base:** ✅ Completado

---

## 📋 Archivos Creados

### Configuración:
- `vite.config.ts` - Dual build (admin + POS)
- `tsconfig.json` - Path aliases
- `routes/web.php` - Ruta POS
- `resources/views/pos.blade.php` - HTML base

### Frontend POS:
```
resources/js/pos/
├── pos.ts (entry point)
├── PosApp.vue (app principal)
├── router/
│   └── index.ts (Vue Router + guards)
├── stores/
│   └── auth.ts (Pinia auth store)
├── layouts/
│   └── PosLayout.vue (header responsivo)
└── views/
    ├── LoginView.vue (login moderno)
    ├── TablesView.vue (placeholder)
    ├── OrderView.vue (placeholder)
    └── PaymentView.vue (placeholder)
```

---

## 🎯 Para Iniciar el Proyecto

### 1. Instalar Dependencias (si no lo hiciste)
```bash
npm install
```

### 2. Compilar Assets
```bash
npm run dev
```

### 3. Iniciar Laravel
```bash
# En otra terminal
php artisan serve
```

### 4. Abrir POS
```
http://localhost:8000/pos
```

---

## 🔐 Credenciales de Prueba

```
Email: test@pos.com
Password: password123
```

---

## 🎨 Características Implementadas

### LoginView
- ✅ Diseño moderno con gradientes
- ✅ Animaciones suaves
- ✅ Responsive (móvil + PC)
- ✅ Validación de formulario
- ✅ Manejo de errores
- ✅ Auto-completa credenciales demo

### PosLayout
- ✅ Header con logo
- ✅ Reloj en tiempo real
- ✅ Info de usuario con avatar
- ✅ Botón logout con confirmación
- ✅ Responsive (oculta elementos en móvil)
- ✅ Diseño moderno con TailwindCSS

### Router
- ✅ Vue Router configurado
- ✅ Navigation guards (auth)
- ✅ Rutas protegidas
- ✅ Redirect a login si no auth

### Auth Store (Pinia)
- ✅ Login/Logout
- ✅ Token management
- ✅ LocalStorage persistence
- ✅ Axios auto-config

---

## 🛠️ Próximos Pasos

### 1. TablesView (Vista de Mesas)
- Grid de mesas
- Estados visuales (disponible, ocupada, etc)
- Selección de mesa
- Filtro por área

### 2. OrderView (Tomar Pedido)
- Grid de productos por categoría
- Carrito de pedido
- Agregar/quitar items
- Instrucciones especiales

### 3. PaymentView (Procesar Pago)
- Resumen de cuenta
- Métodos de pago
- Calcular cambio
- Finalizar orden

---

## 📱 Características Responsivas

### Móvil (< 768px):
- Header más compacto (h-14)
- Logo solo icono
- Sin reloj central
- Sin detalles de usuario
- Touch-optimized (min-height: 48px)

### Tablet/PC (>= 768px):
- Header completo (h-16)
- Logo con texto
- Reloj visible
- Detalles de usuario
- Hover effects

---

## 🐛 Troubleshooting

### Error: Module not found '@pos/...'
**Solución:** Reinicia el servidor Vite
```bash
# Ctrl+C en terminal de npm
npm run dev
```

### Error: Cannot find module 'pinia'
**Solución:** 
```bash
npm install pinia vue-router
```

### Página en blanco en /pos
**Verificar:**
1. Vite está corriendo (`npm run dev`)
2. Laravel está corriendo (`php artisan serve`)
3. Revisar consola del navegador (F12)

---

## 🎯 Testing

### Test Manual:
1. Ir a `http://localhost:8000/pos`
2. Debería redirigir a `/pos/login`
3. Ingresar credenciales demo
4. Debería redirigir a `/pos/tables`
5. Logout debería volver a login

### Test API:
```bash
php test_all_api.php
```

---

## 📊 Estructura de Rutas

```
/pos/login    → LoginView (público)
/pos          → Redirect a /pos/tables
/pos/tables   → TablesView (requiere auth)
/pos/order/:tableId → OrderView (requiere auth)
/pos/payment/:orderId → PaymentView (requiere auth)
```

---

## 🎨 Paleta de Colores

- **Primary:** Blue (from-blue-600 to-blue-700)
- **Success:** Green-500
- **Danger:** Red-500
- **Warning:** Yellow-500
- **Background:** Gray-50
- **Text:** Gray-800

---

## ✨ Animaciones Implementadas

- Fade-in en login (keyframe)
- Bounce en logo (keyframe)
- Shake en error (keyframe)
- Spin en loading (keyframe)
- Scale en botones (transform)

---

## 🚀 Listo para Desarrollo

El frontend POS está configurado y listo. Puedes empezar a:

1. Implementar **TablesView** con grid de mesas
2. Conectar con API `/api/pos/tables`
3. Agregar funcionalidad de selección
4. Continuar con OrderView

**¡Backend API + Frontend Base = Completado! 🎉**
