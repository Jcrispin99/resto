# 🍽️ Restaurant Management System - Database Structure

## 📋 Resumen del Proyecto

Sistema integral de gestión para restaurantes que incluye:
- Gestión de productos e inventario (Kardex)
- Sistema POS (Punto de Venta)
- Control de compras y proveedores
- Ventas B2B/mayoristas
- Gestión de mesas y reservas
- Control de caja
- Recetas y combos

---

## 🗂️ Estructura Actual (37 Tablas)

### 1️⃣ **Core & Multi-tenancy**

#### `companies`
Empresas principales del sistema.
- `name`, `ruc`, `trade_name`
- `legal_address`, `phone`, `email`
- Soporte multi-empresa

#### `branches`
Sucursales de cada empresa.
- `company_id` (FK a companies)
- Información de ubicación (`address`, `ubigeo_code`)
- `kitchen_printer_ip` - IP de impresora de cocina
- Configuración por sucursal

---

### 2️⃣ **Productos & Inventario**

#### `product_categories`
Categorías jerárquicas de productos.
- `parent_id` (auto-referencia)
- `name`, `full_name` (auto-generado: "Bebidas > Gaseosas")
- `is_active`

#### `units`
Unidades de medida (kg, L, unidad, etc.).
- `name`, `symbol`
- `type`: 'unit', 'weight', 'volume', 'length', 'time'

#### `product_attributes`
Atributos para variantes (Color, Tamaño, Sabor).
- `name`

#### `product_attribute_values`
Valores de atributos (Rojo, Grande, Vainilla).
- `attribute_id` (FK a product_attributes)
- `value`

#### `product_template`
Producto base/plantilla.
- `category_id`, `menu_category_id` (para POS)
- `unit_id`
- `name`, `description`
- `internal_reference`, `barcode`
- `product_type`: 'consumable', 'storable', 'service'
- **`can_be_sold`** - Si aparece en POS
- **`can_be_purchased`** - Si se puede comprar a proveedores
- **`can_be_stocked`** - Si se controla inventario
- `sale_price`
- `is_active`

#### `product_product`
Variantes específicas del template.
- `template_id` (FK a product_template)
- `barcode`, `sku`
- `sale_price` (hereda o override)
- Nombre dinámico: "Coca-Cola - 500ml - Fría"

#### `attribute_value_product` (Pivot)
Relaciona variantes con valores de atributos.
- `product_id`, `attribute_value_id`

#### `product_menu_settings` (1:1 con product_template)
Configuración adicional para menú POS.
- `product_template_id` (unique)
- `menu_name`, `menu_description` (override)
- `preparation_time_minutes`
- `is_featured`, `display_order`
- Información nutricional: `calories`, `is_vegetarian`, `is_vegan`, `is_gluten_free`
- `allergens` (JSON)
- Disponibilidad: `available_for_dine_in`, `available_for_takeout`, `available_for_delivery`

#### `productables` (Polimórfica)
Items de transacciones (compras, ventas, transferencias).
- `morphs('productable')` - PurchaseOrder, Order, SaleOrder, StockTransfer
- `product_id` (FK a product_product)
- `quantity`
- `price`, `discount`, `tax_rate`
- `subtotal`, `total`

#### `imageables` (Polimórfica)
Imágenes de productos, categorías, etc.
- `morphs('imageable')`
- `path`, `size`

#### `inventories` (Kardex)
Movimientos de inventario tipo Kardex.
- `product_id`, `warehouse_id`
- `morphs('inventoryable')` - PurchaseOrder, Order, StockTransfer
- `detail`
- **IN:** `quantity_in`, `cost_in`, `total_in`
- **OUT:** `quantity_out`, `cost_out`, `total_out`
- **BALANCE:** `quantity_balance`, `cost_balance`, `total_balance`

#### `warehouses`
Almacenes.
- `branch_id`
- `name`, `code`
- `is_active`

---

### 3️⃣ **Partners (Clientes/Proveedores)**

#### `partners`
Contactos comerciales (clientes y proveedores).
- `code`, `partner_type`: 'person', 'company'
- `name`, `trade_name`
- `tax_id` (RUC/DNI)
- `email`, `phone`
- `address`, `ubigeo_code`
- **`is_customer`**, **`is_supplier`**
- `payment_terms_days`
- `notes`, `is_active`

---

### 4️⃣ **Compras & Transferencias**

#### `purchase_orders`
Órdenes de compra a proveedores (todo el flujo).
- `order_number`
- `branch_id`, `warehouse_id`, `partner_id` (proveedor)
- Fechas: `order_date`, `expected_delivery_date`, `received_date`, `paid_date`
- **Status:** `quote_request → quote_received → ordered → approved → received → paid`
- `subtotal`, `tax`, `total`
- `created_by`, `approved_by`

#### `sale_orders`
Ventas B2B/mayoristas (cotizaciones, entregas programadas).
- `order_number`
- `branch_id`, `warehouse_id`, `partner_id` (cliente)
- Fechas: `order_date`, `quote_valid_until`, `delivery_date`, `paid_date`
- **Status:** `quote → quote_sent → approved → processing → delivered → paid`
- `subtotal`, `discount`, `tax`, `total`
- Delivery: `delivery_address`, `delivery_contact`, `delivery_phone`

#### `stock_transfers`
Transferencias entre almacenes.
- `transfer_number`
- `from_warehouse_id`, `to_warehouse_id`
- `transfer_date`
- **Status:** `pending → in_transit → received`
- `created_by`, `received_by`

*(Items de todas estas transacciones están en `productables`)*

---

### 5️⃣ **Recetas & Combos**

#### `recipes`
Ingredientes necesarios para elaborar un plato.
- `product_template_id` (el plato)
- `ingredient_id` (FK a product_product)
- `quantity`, `unit_id`
- `waste_percentage` (% de merma)
- `notes`

#### `combos`
Paquetes promocionales.
- `name`, `description`
- `price`, `regular_price`, `discount_percentage`
- `image`
- `start_date`, `end_date`
- `is_active`

#### `combo_items`
Productos que incluye el combo.
- `combo_id`
- `product_template_id`
- `quantity`
- `allow_substitution`

---

### 6️⃣ **POS (Punto de Venta)**

#### `pos_terminals`
Dispositivos físicos de venta (cajas, tablets).
- `branch_id`
- `code`, `name`
- `ip_address`, `printer_ip`
- `is_active`

#### `cash_registers`
Turnos de caja (apertura/cierre).
- `terminal_id`
- `opening_balance`, `closing_balance`, `expected_balance`, `difference`
- `opened_by`, `closed_by`
- `opened_at`, `closed_at`
- **Status:** `open`, `closed`

#### `cash_movements`
Movimientos de dinero durante el turno.
- `cash_register_id`
- **Type:** `income`, `expense`, `opening`, `closing`, `deposit`, `withdrawal`
- `concept`, `amount`, `payment_method`
- `reference`, `notes`
- `user_id`

#### `payment_methods`
Métodos de pago aceptados.
- `name`, `type`: 'cash', 'card', 'transfer', 'qr', 'wallet'
- `is_active`

#### `taxes`
Impuestos (IGV, ISC, etc.).
- `name`, `code`
- `tax_type`: 'percentage', 'fixed'
- `rate`
- `is_active`

---

### 7️⃣ **Mesas & Reservas**

#### `table_areas`
Áreas del restaurante (Terraza, Salón, VIP).
- `branch_id`
- `name`, `description`
- `is_active`

#### `tables`
Mesas físicas.
- `branch_id`, `area_id`
- `table_number`, `name`
- `capacity`
- **Status:** `available`, `occupied`, `reserved`, `cleaning`

#### `reservations`
Reservaciones de clientes.
- `reservation_number`
- `branch_id`, `partner_id` (cliente), `table_id` (nullable)
- `reservation_date`, `reservation_time`
- `guests_count`
- **Status:** `pending → confirmed → seated → completed / cancelled / no_show`
- `special_requests`
- `confirmed_at`, `seated_at`

---

### 8️⃣ **Órdenes de Restaurante**

#### `orders`
Pedidos en el restaurante (POS).
- `order_number`
- `branch_id`, `cash_register_id`, `table_id`, `partner_id` (opcional)
- **Type:** `dine_in`, `takeout`, `delivery`, `digital_menu`
- **Status:** `pending → confirmed → preparing → ready → served → completed`
- **Payment Status:** `unpaid`, `partial`, `paid`, `refunded`
- Timestamps: `order_date`, `scheduled_time`, `served_time`, `completed_time`, `paid_at`
- `waiter_id`, `cashier_id`
- `guests_count`
- Montos: `subtotal`, `discount`, `tax`, `service_charge`, `delivery_fee`, `tip_amount`, `total`
- `notes`

#### `order_items`
Productos del pedido.
- `order_id`
- `product_template_id`, `product_id` (variante opcional)
- `quantity`
- `unit_price`, `discount`, `tax_id`, `tax_amount`
- `subtotal`, `total`
- **Status:** `pending → preparing → ready → served`
- `special_instructions` (notas de cocina)
- `prepared_by`
- Timestamps: `sent_to_kitchen_at`, `ready_at`, `served_at`

#### `order_payments`
Pagos de órdenes.
- `order_id`, `payment_method_id`
- `amount`
- `reference`, `notes`
- `created_by`

---

### 9️⃣ **Utilidades**

#### `branch_menu_availability`
Disponibilidad de productos por sucursal y horario.
- `branch_id`, `menu_item_id`
- `available_from`, `available_to`
- `is_available_on` (JSON - días de semana)

---

## 📁 Módulos Futuros (Guardados para implementación)

### 🔧 **Modificadores de Menú**
*Ubicación: `database/migrations/_future_modifiers/`*

Permitirá personalizar productos:
- "Sin cebolla", "Término medio", "Extra queso"
- Agrupación de modificadores (single/multiple)
- Precios adicionales

**Tablas:**
- `product_modifiers`
- `product_modifier_options`
- `product_template_modifiers` (pivot)

---

### 📊 **Analytics & Reportes**

Pre-calcular estadísticas para dashboards:

**Tablas planificadas:**
- `sales_summary_daily` - Resumen diario de ventas
- `product_sales_summary` - Ventas por producto
- `category_sales_summary` - Ventas por categoría
- `waiter_performance` - Desempeño de mozos
- `peak_hours` - Horas pico

---

### 💸 **Gastos Operativos**

Control de gastos del negocio (NO compras de inventario):

**Tablas planificadas:**
- `expense_categories` - Categorías de gastos
- `expenses` - Gastos registrados
- `cost_centers` - Centros de costo
- `payroll` - Nómina
- `attendance` - Asistencia de empleados

---

### 👨‍🍳 **Estaciones de Cocina** (Multi-cocina)

Si en el futuro se divide la cocina:

**Tablas planificadas:**
- `kitchen_stations` - Estaciones (Parrilla, Frío, Postres)
- `kitchen_tickets` - Comandas por estación
- `kitchen_ticket_items` - Items de comandas

---

## 🎯 Principios de Diseño Aplicados

1. **DRY (Don't Repeat Yourself)**
   - Una sola tabla `productables` para items de todas las transacciones
   - `imageables` polimórfica para todas las imágenes
   - `inventories` polimórfica para todos los movimientos de stock

2. **Simplicidad**
   - Eliminado redundancias (menu_items → usar product_template)
   - Sin tablas de items separadas (order_items sí, pero purchase_order_items NO)

3. **Escalabilidad**
   - Arquitectura preparada para crecer
   - Módulos futuros documentados
   - Polimorfismo para flexibilidad

4. **Negocio Real**
   - Estados que reflejan flujos reales (cotización → compra → pago)
   - Control de caja completo
   - Kardex para trazabilidad de inventario

---

## 📊 Estadísticas

- **Tablas Activas:** 37
- **Tablas Futuras:** ~15
- **Relaciones Polimórficas:** 3 (productables, imageables, inventories)
- **Migraciones Ejecutadas:** ✅ Todas sin errores

---

## 🚀 Próximos Pasos Sugeridos

1. **Seeders:** Crear datos de prueba para productos, recetas, combos
2. **Modelos:** Actualizar modelos de Laravel con relaciones correctas
3. **Filament Resources:** Generar/actualizar recursos para admin panel
4. **API:** Endpoints para POS y aplicación móvil
5. **Impresión:** Integración con impresoras de cocina/tickets
6. **Reportes:** Implementar consultas optimizadas para analytics

---

*Última actualización: 2025-12-01*
