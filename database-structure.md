# Estructura de Base de Datos - Sistema de Restaurantes

> **Nota**: Esta documentación refleja la estructura **ACTUAL** implementada en las migraciones. Las tablas marcadas como **[FUTURO]** están documentadas pero aún no tienen migración.

## 📋 Índice
1. [Gestión de Empresas y Sucursales](#1-gestión-de-empresas-y-sucursales)
2. [Logística e Inventario](#2-logística-e-inventario)
3. [Productos y Sistema de Variantes](#3-productos-y-sistema-de-variantes)
4. [Tablas Polimórficas](#4-tablas-polimórficas)
5. [Socios de Negocio (Partners)](#5-socios-de-negocio-partners)
6. [Compras y Ventas](#6-compras-y-ventas)
7. [Carta Digital (POS)](#7-carta-digital-pos)
8. [Punto de Venta (POS)](#8-punto-de-venta-pos)
9. [Mesas y Reservas](#9-mesas-y-reservas)
10. [Comandas y Pedidos](#10-comandas-y-pedidos)
11. [Funcionalidades Futuras](#11-funcionalidades-futuras)

---

## 1. Gestión de Empresas y Sucursales

### `companies`
Empresas (solo datos fiscales/corporativos)
- `id` - BIGINT PRIMARY KEY
- `business_name` - VARCHAR(200) (Razón social)
- `trade_name` - VARCHAR(200) (Nombre comercial)
- `tax_id` - VARCHAR(20) UNIQUE (RUC)
- `logo` - VARCHAR(255) NULLABLE
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `branches`
Sucursales/Locales
- `id` - BIGINT PRIMARY KEY
- `company_id` - BIGINT FK → companies
- `code` - VARCHAR(10) UNIQUE
- `name` - VARCHAR(150)
- `business_name` - VARCHAR(200) NULL (Razón social si factura independiente)
- `tax_id` - VARCHAR(11) NULL (RUC si factura independiente)
- `address` - VARCHAR(255)
- `ubigeo_code` - VARCHAR(6) NULL (Código ubigeo: departamento-provincia-distrito)
- `kitchen_printer_ip` - VARCHAR(45) NULL (IP de la impresora de cocina)
- `country` - VARCHAR(3) DEFAULT 'PE'
- `latitude` - DECIMAL(10,8) NULL
- `longitude` - DECIMAL(11,8) NULL
- `phone` - VARCHAR(20)
- `email` - VARCHAR(150)
- `website` - VARCHAR(255) NULL
- `manager_id` - BIGINT NULL (FK → users, sin constraint)
- `opening_time` - TIME NULL
- `closing_time` - TIME NULL
- `max_tables` - INT DEFAULT 0
- `max_capacity` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `currency` - VARCHAR(3) DEFAULT 'PEN'
- `timezone` - VARCHAR(50) DEFAULT 'America/Lima'
- `tax_percentage` - DECIMAL(5,2) DEFAULT 18.00
- `print_kitchen_ticket` - BOOLEAN DEFAULT true
- `print_customer_receipt` - BOOLEAN DEFAULT true
- `accept_reservations` - BOOLEAN DEFAULT true
- `accept_delivery` - BOOLEAN DEFAULT true
- `accept_takeout` - BOOLEAN DEFAULT true
- `config_json` - JSON NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

> **Nota**: Los settings de sucursal están integrados directamente en la tabla `branches` (no hay tabla `branch_settings` separada).

---

## 2. Logística e Inventario

### `warehouses`
Almacenes
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK → branches
- `code` - VARCHAR(20) UNIQUE
- `name` - VARCHAR(100)
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `inventories`
Kardex - Movimientos de inventario (tipo entrada-salida-balance)
- `id` - BIGINT PRIMARY KEY
- `product_id` - BIGINT FK → product_product
- `warehouse_id` - BIGINT FK → warehouses
- `inventoryable_id` - BIGINT (ID de la entidad relacionada)
- `inventoryable_type` - VARCHAR(50) (purchase_orders, orders, etc.)
- `detail` - VARCHAR(255) NULL (Descripción del movimiento)
- `quantity_in` - DECIMAL(10,3) DEFAULT 0
- `cost_in` - DECIMAL(10,2) DEFAULT 0
- `total_in` - DECIMAL(10,2) DEFAULT 0
- `quantity_out` - DECIMAL(10,3) DEFAULT 0
- `cost_out` - DECIMAL(10,2) DEFAULT 0
- `total_out` - DECIMAL(10,2) DEFAULT 0
- `quantity_balance` - DECIMAL(10,3) DEFAULT 0
- `cost_balance` - DECIMAL(10,2) DEFAULT 0
- `total_balance` - DECIMAL(10,2) DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

> **Nota importante**: Esta tabla implementa el sistema de kardex con entrada-salida-balance. Es polimórfica para relacionarse con diferentes tipos de documentos.

---

## 3. Productos y Sistema de Variantes

### `product_categories`
Categorías de productos (jerárquico)
- `id` - BIGINT PRIMARY KEY
- `parent_id` - BIGINT FK → product_categories NULL
- `name` - VARCHAR(100)
- `full_name` - VARCHAR(500) NULL (Auto-calculado: "Padre / Hijo")
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `units`
Unidades de medida
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(50)
- `abbreviation` - VARCHAR(10)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_attributes`
Atributos de productos (Talla, Color, Material, etc.)
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(50)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_attribute_values`
Valores de atributos (S, M, L, XL, Rojo, Azul, etc.)
- `id` - BIGINT PRIMARY KEY
- `attribute_id` - BIGINT FK → product_attributes
- `value` - VARCHAR(100)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_template`
Plantilla de producto (información general)
- `id` - BIGINT PRIMARY KEY
- `category_id` - BIGINT FK → product_categories
- `menu_category_id` - BIGINT FK → product_categories NULL (Categoría para mostrar en POS)
- `unit_id` - BIGINT FK → units
- `name` - VARCHAR(200)
- `description` - TEXT NULL
- `internal_reference` - VARCHAR(50) NULL
- `barcode` - VARCHAR(100) NULL
- `product_type` - ENUM('consumable', 'storable', 'service', 'combo') DEFAULT 'storable'
- `can_be_sold` - BOOLEAN DEFAULT false (Si true, aparece en POS como producto vendible)
- `can_be_purchased` - BOOLEAN DEFAULT true
- `can_be_stocked` - BOOLEAN DEFAULT true
- `sale_price` - DECIMAL(10,2) DEFAULT 0 (Precio base de venta)
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

### `product_product`
Variantes específicas de productos (SKUs)
- `id` - BIGINT PRIMARY KEY
- `template_id` - BIGINT FK → product_template
- `sku` - VARCHAR(50) UNIQUE
- `barcode` - VARCHAR(100) UNIQUE NULL
- `sale_price` - DECIMAL(10,2) NULL (NULL = usa precio del template)
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

### `attribute_value_product`
Pivot: Valores de atributos de cada variante
- `id` - BIGINT PRIMARY KEY
- `attribute_value_id` - BIGINT FK → product_attribute_values
- `product_id` - BIGINT FK → product_product
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- UNIQUE (attribute_value_id, product_id)

### `product_menu_settings`
Configuraciones de menú/POS para productos
- `id` - BIGINT PRIMARY KEY
- `product_template_id` - BIGINT FK → product_template UNIQUE
- `menu_name` - VARCHAR(200) NULL (Override del nombre para el menú)
- `menu_description` - TEXT NULL (Descripción para clientes)
- `preparation_time_minutes` - INT DEFAULT 0
- `is_featured` - BOOLEAN DEFAULT false (Destacado en el menú)
- `display_order` - INT DEFAULT 0
- `calories` - INT NULL
- `is_spicy` - BOOLEAN DEFAULT false
- `is_vegetarian` - BOOLEAN DEFAULT false
- `is_vegan` - BOOLEAN DEFAULT false
- `is_gluten_free` - BOOLEAN DEFAULT false
- `allergens` - JSON NULL (["gluten", "lactose", "nuts"])
- `available_for_dine_in` - BOOLEAN DEFAULT true
- `available_for_takeout` - BOOLEAN DEFAULT true
- `available_for_delivery` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 4. Tablas Polimórficas

### `productables`
Relación polimórfica de productos con diferentes entidades
- `id` - BIGINT PRIMARY KEY
- `product_id` - BIGINT FK → product_product
- `productable_id` - BIGINT (ID de la entidad relacionada)
- `productable_type` - VARCHAR(50) (purchase_orders, sale_orders, recipes, etc.)
- `quantity` - DECIMAL(10,3)
- `price` - DECIMAL(10,2) (Precio unitario usado)
- `discount` - DECIMAL(10,2) DEFAULT 0 (Descuento aplicado)
- `tax_rate` - DECIMAL(8,2) DEFAULT 18.00 (% IGV aplicado)
- `subtotal` - DECIMAL(10,2) (quantity * price - discount)
- `total` - DECIMAL(10,2) (subtotal + impuestos)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `imageables`
Galería de imágenes polimórfica
- `id` - BIGINT PRIMARY KEY
- `path` - VARCHAR(255) UNIQUE
- `size` - INT DEFAULT 0 (File size in bytes)
- `imageable_id` - BIGINT (ID de la entidad)
- `imageable_type` - VARCHAR(50) (product_template, product_product, branches, etc.)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 5. Socios de Negocio (Partners)

### `partners`
Contactos unificados (clientes, proveedores, o ambos)
- `id` - BIGINT PRIMARY KEY
- `code` - VARCHAR(20) UNIQUE
- `partner_type` - ENUM('individual', 'company') DEFAULT 'company'
- `name` - VARCHAR(200) (Razón social o nombre completo)
- `trade_name` - VARCHAR(200) NULL (Nombre comercial)
- `tax_id` - VARCHAR(20) UNIQUE (RUC/DNI)
- `email` - VARCHAR(150)
- `phone` - VARCHAR(20)
- `address` - VARCHAR(255) NULL
- `ubigeo_code` - VARCHAR(6) NULL (Código ubigeo INEI)
- `is_customer` - BOOLEAN DEFAULT false
- `is_supplier` - BOOLEAN DEFAULT false
- `payment_terms_days` - INT DEFAULT 0 (0 = contado)
- `notes` - TEXT NULL
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

---

## 6. Compras y Ventas

### `purchase_orders`
Órdenes de compra a proveedores
- `id` - BIGINT PRIMARY KEY
- `order_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK → branches
- `warehouse_id` - BIGINT FK → warehouses
- `partner_id` - BIGINT FK → partners (Supplier)
- `order_date` - DATE
- `expected_delivery_date` - DATE NULL
- `received_date` - DATE NULL
- `paid_date` - DATE NULL
- `status` - ENUM('quote_request', 'quote_received', 'ordered', 'approved', 'received', 'paid', 'cancelled') DEFAULT 'quote_request'
- `subtotal` - DECIMAL(10,2)
- `tax` - DECIMAL(10,2)
- `total` - DECIMAL(10,2)
- `notes` - TEXT NULL
- `created_by` - BIGINT NULL (FK → users, sin constraint)
- `approved_by` - BIGINT NULL (FK → users, sin constraint)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

> **Nota**: Los ítems de purchase_orders se manejan mediante la tabla polimórfica `productables`.

### `sale_orders`
Órdenes de venta B2B (mayoristas, cotizaciones)
- `id` - BIGINT PRIMARY KEY
- `order_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK → branches
- `warehouse_id` - BIGINT FK → warehouses NULL
- `partner_id` - BIGINT FK → partners (Customer)
- `order_date` - DATE
- `quote_valid_until` - DATE NULL (Vigencia de cotización)
- `delivery_date` - DATE NULL
- `paid_date` - DATE NULL
- `status` - ENUM('quote', 'quote_sent', 'approved', 'processing', 'delivered', 'paid', 'cancelled') DEFAULT 'quote'
- `subtotal` - DECIMAL(10,2)
- `discount` - DECIMAL(10,2) DEFAULT 0
- `tax` - DECIMAL(10,2)
- `total` - DECIMAL(10,2)
- `delivery_address` - VARCHAR(255) NULL
- `delivery_contact` - VARCHAR(100) NULL
- `delivery_phone` - VARCHAR(20) NULL
- `notes` - TEXT NULL
- `created_by` - BIGINT NULL (FK → users, sin constraint)
- `approved_by` - BIGINT NULL (FK → users, sin constraint)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

> **Nota**: Los ítems de sale_orders se manejan mediante la tabla polimórfica `productables`.

### `stock_transfers`
Transferencias entre almacenes
- `id` - BIGINT PRIMARY KEY
- `transfer_number` - VARCHAR(20) UNIQUE
- `from_warehouse_id` - BIGINT FK → warehouses
- `to_warehouse_id` - BIGINT FK → warehouses
- `transfer_date` - DATE
- `status` - ENUM('pending', 'in_transit', 'received', 'cancelled') DEFAULT 'pending'
- `notes` - TEXT NULL
- `created_by` - BIGINT NULL (FK → users, sin constraint)
- `received_by` - BIGINT NULL (FK → users, sin constraint)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

> **Nota**: Los ítems de transferencias se manejan mediante la tabla polimórfica `productables`.

---

## 7. Carta Digital (POS)

### `recipes`
Recetas (ingredientes de cada plato)
- `id` - BIGINT PRIMARY KEY
- `product_template_id` - BIGINT FK → product_template (El plato/producto que se elabora)
- `ingredient_id` - BIGINT FK → product_product (Ingrediente que consume)
- `quantity` - DECIMAL(10,3) (Cantidad necesaria del ingrediente)
- `unit_id` - BIGINT FK → units
- `waste_percentage` - DECIMAL(5,2) DEFAULT 0 (% de merma esperada)
- `notes` - TEXT NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `combos`
Combos/Promociones
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(200)
- `description` - TEXT NULL
- `price` - DECIMAL(10,2)
- `regular_price` - DECIMAL(10,2)
- `discount_percentage` - DECIMAL(5,2) DEFAULT 0
- `image` - VARCHAR(255) NULL
- `start_date` - DATE
- `end_date` - DATE NULL
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

### `combo_items`
Ítems incluidos en combos
- `id` - BIGINT PRIMARY KEY
- `combo_id` - BIGINT FK → combos
- `product_template_id` - BIGINT FK → product_template (Producto que incluye el combo)
- `quantity` - INT DEFAULT 1
- `allow_substitution` - BOOLEAN DEFAULT false (Permitir cambiar por otro producto)
- `created_at` - TIMESTAMP (solo created_at, sin updated_at)

### `branch_menu_availability`
Disponibilidad de ítems del menú por sucursal
- `branch_id` - BIGINT FK → branches
- `menu_item_id` - BIGINT FK → menu_items
- `is_available` - BOOLEAN DEFAULT true
- `custom_price` - DECIMAL(10,2) NULL
- `updated_at` - TIMESTAMP
- PRIMARY KEY (branch_id, menu_item_id)

> **Nota**: Esta tabla hace referencia a `menu_items` que aún no existe. Actualmente se puede usar con `product_template` que tiene `can_be_sold = true`.

---

## 8. Punto de Venta (POS)

### `pos_terminals`
Terminales de punto de venta
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK → branches
- `code` - VARCHAR(20) UNIQUE
- `name` - VARCHAR(100)
- `ip_address` - VARCHAR(45) NULL
- `printer_ip` - VARCHAR(45) NULL
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `cash_registers`
Cajas registradoras (sesiones de caja)
- `id` - BIGINT PRIMARY KEY
- `terminal_id` - BIGINT FK → pos_terminals
- `opening_balance` - DECIMAL(10,2)
- `closing_balance` - DECIMAL(10,2) NULL
- `expected_balance` - DECIMAL(10,2) NULL
- `difference` - DECIMAL(10,2) NULL
- `opened_by` - BIGINT FK → users
- `closed_by` - BIGINT FK → users NULL
- `opened_at` - TIMESTAMP
- `closed_at` - TIMESTAMP NULL
- `status` - ENUM('open', 'closed') DEFAULT 'open'
- `notes` - TEXT NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `cash_movements`
Movimientos de caja
- `id` - BIGINT PRIMARY KEY
- `cash_register_id` - BIGINT FK → cash_registers
- `type` - ENUM('income', 'expense', 'opening', 'closing', 'deposit', 'withdrawal')
- `concept` - VARCHAR(200)
- `amount` - DECIMAL(10,2)
- `payment_method` - VARCHAR(50) NULL
- `reference` - VARCHAR(100) NULL
- `user_id` - BIGINT FK → users
- `notes` - TEXT NULL
- `created_at` - TIMESTAMP (solo created_at, sin updated_at)

### `payment_methods`
Métodos de pago
- `id` - BIGINT PRIMARY KEY
- `code` - VARCHAR(20) UNIQUE
- `name` - VARCHAR(50)
- `type` - ENUM('cash', 'card', 'transfer', 'wallet', 'other') DEFAULT 'cash'
- `requires_reference` - BOOLEAN DEFAULT false
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `taxes`
Impuestos configurables (SUNAT)
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(255) UNIQUE
- `description` - TEXT NULL
- `invoice_label` - VARCHAR(255) NULL (Label shown on invoice)
- `tax_type` - VARCHAR(255) (IGV, ISC, ICBPER, RETENCION, PERCEPCION, etc.)
- `affectation_type_code` - VARCHAR(2) NULL (SUNAT Catalog 07: 10=Gravado, 20=Exonerado, 30=Inafecto)
- `rate_percent` - DECIMAL(5,2) DEFAULT 0 (18.00 for IGV, 0.30 for ICBPER)
- `is_price_inclusive` - BOOLEAN DEFAULT false (Tax included in price)
- `is_active` - BOOLEAN DEFAULT true
- `is_default` - BOOLEAN DEFAULT false
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 9. Mesas y Reservas

### `table_areas`
Áreas/Zonas (salón, terraza, bar, etc.)
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK → branches
- `name` - VARCHAR(100)
- `description` - TEXT NULL
- `order` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `tables`
Mesas
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK → branches
- `area_id` - BIGINT FK → table_areas
- `number` - VARCHAR(20)
- `capacity` - INT
- `qr_code` - VARCHAR(255) NULL
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `reservations`
Reservas
- `id` - BIGINT PRIMARY KEY
- `reservation_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK → branches
- `partner_id` - BIGINT FK → partners (Customer)
- `table_id` - BIGINT FK → tables NULL
- `reservation_date` - DATE
- `reservation_time` - TIME
- `guests_count` - INT
- `status` - ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') DEFAULT 'pending'
- `special_requests` - TEXT NULL
- `confirmed_at` - TIMESTAMP NULL
- `seated_at` - TIMESTAMP NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 10. Comandas y Pedidos

### `orders`
Pedidos/Órdenes de restaurante (POS)
- `id` - BIGINT PRIMARY KEY
- `order_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK → branches
- `cash_register_id` - BIGINT FK → cash_registers NULL
- `table_id` - BIGINT FK → tables NULL
- `partner_id` - BIGINT FK → partners NULL (Customer)
- `order_type` - ENUM('dine_in', 'takeout', 'delivery', 'digital_menu') DEFAULT 'dine_in'
- `status` - ENUM('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled') DEFAULT 'pending'
- `payment_status` - ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid'
- `order_date` - TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `scheduled_time` - TIMESTAMP NULL
- `served_time` - TIMESTAMP NULL
- `completed_time` - TIMESTAMP NULL
- `paid_at` - TIMESTAMP NULL
- `waiter_id` - BIGINT NULL (FK → users, sin constraint)
- `cashier_id` - BIGINT NULL (FK → users, sin constraint)
- `guests_count` - INT DEFAULT 1
- `subtotal` - DECIMAL(10,2)
- `discount` - DECIMAL(10,2) DEFAULT 0
- `tax` - DECIMAL(10,2)
- `service_charge` - DECIMAL(10,2) DEFAULT 0
- `delivery_fee` - DECIMAL(10,2) DEFAULT 0
- `tip_amount` - DECIMAL(10,2) DEFAULT 0
- `total` - DECIMAL(10,2)
- `notes` - TEXT NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

### `order_items`
Ítems de órdenes
- `id` - BIGINT PRIMARY KEY
- `order_id` - BIGINT FK → orders
- `product_template_id` - BIGINT FK → product_template (Producto ordenado)
- `product_id` - BIGINT FK → product_product NULL (Variante específica si aplica)
- `quantity` - INT
- `unit_price` - DECIMAL(10,2)
- `discount` - DECIMAL(10,2) DEFAULT 0
- `tax_id` - BIGINT FK → taxes NULL
- `tax_amount` - DECIMAL(10,2) DEFAULT 0
- `subtotal` - DECIMAL(10,2)
- `total` - DECIMAL(10,2)
- `status` - ENUM('pending', 'preparing', 'ready', 'served', 'cancelled') DEFAULT 'pending'
- `special_instructions` - TEXT NULL (Notas de cocina: sin cebolla, término medio, etc.)
- `prepared_by` - BIGINT NULL (FK → users, sin constraint)
- `sent_to_kitchen_at` - TIMESTAMP NULL
- `ready_at` - TIMESTAMP NULL
- `served_at` - TIMESTAMP NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `order_payments`
Pagos aplicados a órdenes (permite pagos divididos)
- `id` - BIGINT PRIMARY KEY
- `order_id` - BIGINT FK → orders
- `cash_register_id` - BIGINT FK → cash_registers NULL
- `payment_method_id` - BIGINT FK → payment_methods
- `amount` - DECIMAL(10,2)
- `reference_number` - VARCHAR(100) NULL (Transaction number, card last 4 digits, etc.)
- `payment_date` - TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `processed_by` - BIGINT FK → users
- `status` - ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'completed'
- `notes` - TEXT NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 11. Funcionalidades Futuras

Las siguientes tablas están documentadas pero **NO tienen migración implementada aún**. Se implementarán en futuras versiones según las necesidades del negocio.

### [FUTURO] Sistema de Menú Digital

#### `menu_categories`
Categorías del menú digital
- `id` - BIGINT PRIMARY KEY
- `parent_id` - BIGINT FK → menu_categories NULL
- `name` - VARCHAR(100)
- `description` - TEXT
- `icon` - VARCHAR(100)
- `image` - VARCHAR(255)
- `order` - INT DEFAULT 0
- `is_visible` - BOOLEAN DEFAULT true
- `available_for_delivery` - BOOLEAN DEFAULT true
- `available_for_dine_in` - BOOLEAN DEFAULT true
- `available_for_takeout` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

#### `menu_items`
Ítems del menú (platos, bebidas, etc.)
- `id` - BIGINT PRIMARY KEY
- `sku` - VARCHAR(50) UNIQUE
- `name` - VARCHAR(200)
- `description` - TEXT
- `category_id` - BIGINT FK → menu_categories
- `price` - DECIMAL(10,2)
- `cost` - DECIMAL(10,2)
- `preparation_time_minutes` - INT
- `calories` - INT NULL
- `is_spicy` - BOOLEAN DEFAULT false
- `is_vegetarian` - BOOLEAN DEFAULT false
- `is_vegan` - BOOLEAN DEFAULT false
- `is_gluten_free` - BOOLEAN DEFAULT false
- `allergens` - JSON
- `image` - VARCHAR(255)
- `images` - JSON (galería)
- `is_available` - BOOLEAN DEFAULT true
- `is_featured` - BOOLEAN DEFAULT false
- `order` - INT DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

#### `menu_item_variants`
Variantes de ítems del menú (tamaños, extras)
- `id` - BIGINT PRIMARY KEY
- `menu_item_id` - BIGINT FK → menu_items
- `name` - VARCHAR(100)
- `price_adjustment` - DECIMAL(10,2)
- `is_default` - BOOLEAN DEFAULT false
- `is_available` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

#### `menu_item_modifiers`
Modificadores (extras, complementos) - Ver carpeta `_future_modifiers/`
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(100)
- `type` - ENUM('single', 'multiple')
- `min_selection` - INT DEFAULT 0
- `max_selection` - INT NULL
- `is_required` - BOOLEAN DEFAULT false
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### [FUTURO] Sistema de Cocina

#### `kitchen_stations`
Estaciones de cocina (parrilla, frituras, bebidas, etc.)
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK → branches
- `name` - VARCHAR(100)
- `description` - TEXT
- `printer_ip` - VARCHAR(45)
- `order` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

#### `kitchen_tickets`
Tickets de cocina (comandas)
- `id` - BIGINT PRIMARY KEY
- `ticket_number` - VARCHAR(20) UNIQUE
- `order_id` - BIGINT FK → orders
- `station_id` - BIGINT FK → kitchen_stations
- `priority` - ENUM('low', 'normal', 'high', 'urgent')
- `status` - ENUM('pending', 'preparing', 'ready', 'delivered')
- `printed_at` - TIMESTAMP NULL
- `started_at` - TIMESTAMP NULL
- `completed_at` - TIMESTAMP NULL
- `delivered_at` - TIMESTAMP NULL
- `prepared_by` - BIGINT FK → users NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### [FUTURO] Gastos y Centros de Costo

#### `expense_categories`
- `id` - BIGINT PRIMARY KEY
- `parent_id` - BIGINT FK → expense_categories NULL
- `name` - VARCHAR(100)
- `description` - TEXT
- `code` - VARCHAR(20) UNIQUE
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

#### `expenses`
- `id` - BIGINT PRIMARY KEY
- `expense_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK → branches
- `category_id` - BIGINT FK → expense_categories
- `partner_id` - BIGINT FK → partners NULL
- `expense_date` - DATE
- `description` - VARCHAR(255)
- `amount` - DECIMAL(10,2)
- `tax` - DECIMAL(10,2) DEFAULT 0
- `total` - DECIMAL(10,2)
- `payment_method` - VARCHAR(50)
- `payment_date` - DATE NULL
- `status` - ENUM('pending', 'paid', 'cancelled')
- `invoice_number` - VARCHAR(50)
- `attachment` - VARCHAR(255)
- `notes` - TEXT
- `created_by` - BIGINT FK → users
- `approved_by` - BIGINT FK → users NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### [FUTURO] Partners Extendido

#### `partner_contacts`
Personas de contacto adicionales
- `id` - BIGINT PRIMARY KEY
- `partner_id` - BIGINT FK → partners
- `first_name` - VARCHAR(100)
- `last_name` - VARCHAR(100)
- `position` - VARCHAR(100) NULL
- `email` - VARCHAR(150)
- `phone` - VARCHAR(20)
- `mobile` - VARCHAR(20) NULL
- `is_primary` - BOOLEAN DEFAULT false
- `notes` - TEXT
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

#### `partner_addresses`
Múltiples direcciones por partner
- `id` - BIGINT PRIMARY KEY
- `partner_id` - BIGINT FK → partners
- `address_type` - ENUM('billing', 'shipping', 'delivery', 'other')
- `address_name` - VARCHAR(100)
- `contact_name` - VARCHAR(150) NULL
- `phone` - VARCHAR(20) NULL
- `address` - VARCHAR(255)
- `district` - VARCHAR(100)
- `city` - VARCHAR(100)
- `state` - VARCHAR(100)
- `country` - VARCHAR(100)
- `postal_code` - VARCHAR(10)
- `latitude` - DECIMAL(10,8) NULL
- `longitude` - DECIMAL(11,8) NULL
- `delivery_instructions` - TEXT NULL
- `is_default` - BOOLEAN DEFAULT false
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 📊 Estadísticas

- **Tablas implementadas**: 31
- **Tablas futuras**: ~15
- **Tablas polimórficas**: 3 (productables, imageables, inventories)
- **Sistema de variantes**: Implementado (product_template → product_product + atributos)
- **Sistema de kardex**: Implementado (inventories con entrada-salida-balance)

---

## 🎯 Notas Importantes

1. **Integraciones Polimórficas**: Las tablas `productables`, `imageables` e `inventories` son polimórficas y pueden relacionarse con múltiples tipos de entidades.

2. **Sistema de Productos**: 
   - `product_template` = Producto general (puede tener variantes)
   - `product_product` = SKU específico (variante del template)
   - Los precios pueden estar en ambos niveles (template o variante)

3. **Órdenes vs Sale Orders**:
   - `orders` = Ventas al detal en el restaurante (POS, mesas, delivery)
   - `sale_orders` = Ventas mayoristas, cotizaciones B2B

4. **Inventarios**: Se usa un sistema de kardex con `inventories` que registra entrada-salida-balance, vinculado polimórficamente a diferentes documentos.

5. **Foreign Keys sin constraint**: Varios campos tienen referencias a `users` pero sin constraint formal (por ejemplo `created_by`, `approved_by`) para evitar restricciones al eliminar usuarios.
