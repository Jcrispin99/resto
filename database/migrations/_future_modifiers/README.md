# Implementación futura: Modificadores de Menú

## 📋 Propósito

Permitir personalización de productos vendibles (ej: "sin cebolla", "término medio", "extra queso").

## 🗂️ Tablas necesarias

### 1. `product_modifiers`
Grupos de modificadores (ej: "Punto de la carne", "Extras", "Sin ingredientes")

```php
- id
- name (ej: "Punto de la carne")
- type: 'single' | 'multiple'
- min_selection (ej: 0 = opcional)
- max_selection (ej: 1 = solo uno)
- is_required (boolean)
```

### 2. `product_modifier_options`
Opciones específicas de cada modificador

```php
- id
- modifier_id (FK)
- name (ej: "Término medio", "Extra queso")
- price (ej: 0.00 o +2.50)
- is_default (boolean)
- is_available (boolean)
```

### 3. `product_template_modifiers` (pivot)
Qué productos tienen qué modificadores

```php
- product_template_id
- modifier_id
- order (para ordenar en UI)
```

## 💡 Ejemplo de uso

```php
Producto: "Hamburguesa Premium"

Modifiers:
  1. "Punto de la carne" (single, required):
     - Término medio ($0)
     - Bien cocido ($0)
     - Jugoso ($0)
  
  2. "Extras" (multiple, max:3):
     - Queso (+$2.00)
     - Bacon (+$3.00)
     - Huevo (+$1.50)
  
  3. "Sin ingredientes" (multiple):
     - Sin cebolla ($0)
     - Sin tomate ($0)
```

## 🔗 Integración con Orders

En `order_items` o `productables`, agregar:
```php
- selected_modifiers (JSON)
  Ejemplo: [
    {"modifier": "Punto de la carne", "option": "Término medio", "price": 0},
    {"modifier": "Extras", "option": "Queso", "price": 2.00},
    {"modifier": "Sin ingredientes", "option": "Sin cebolla", "price": 0}
  ]
```

## 📁 Archivos guardados

Las migraciones están en: `database/migrations/_future_modifiers/`

Cuando estés listo para implementar:
1. Mover archivos de `_future_modifiers/` a `database/migrations/`
2. Renombrar archivos con fecha actual
3. Ejecutar `php artisan migrate`
