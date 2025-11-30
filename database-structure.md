# Estructura de Base de Datos - Sistema de Restaurantes

## 📋 Índice
1. [Gestión de Usuarios y Seguridad](#1-gestión-de-usuarios-y-seguridad)
2. [Gestión de Sucursales](#2-gestión-de-sucursales)
3. [Logística e Inventario](#3-logística-e-inventario)
4. [Carta Digital y Productos](#4-carta-digital-y-productos)
5. [Punto de Venta (POS)](#5-punto-de-venta-pos)
6. [Comandas y Pedidos](#6-comandas-y-pedidos)
7. [Costos y Gastos](#7-costos-y-gastos)
8. [Restaurant BI (Business Intelligence)](#8-restaurant-bi-business-intelligence)
9. [Academia](#9-academia)

---

## 2. Gestión de Sucursales

### `companies`
Empresas (solo datos fiscales/corporativos)
- `id` - BIGINT PRIMARY KEY
- `business_name` - VARCHAR(200) (Razón social)
- `trade_name` - VARCHAR(200) (Nombre comercial)
- `tax_id` - VARCHAR(20) UNIQUE (RUC)
- `logo` - VARCHAR(255)
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `branches`
Sucursales/Locales
- `id` - BIGINT PRIMARY KEY
- `company_id` - BIGINT FK -> companies
- `code` - VARCHAR(10) UNIQUE
- `name` - VARCHAR(150)
- `business_name` - VARCHAR(200) NULL (Razón social si factura independiente)
- `tax_id` - VARCHAR(11) NULL (RUC si factura independiente)
- `address` - VARCHAR(255)
- `ubigeo_code` - VARCHAR(6) NULL (Código ubigeo: departamento-provincia-distrito)
- `country` - VARCHAR(3) DEFAULT 'PE'
- `latitude` - DECIMAL(10,8)
- `longitude` - DECIMAL(11,8)
- `phone` - VARCHAR(20)
- `email` - VARCHAR(150)
- `website` - VARCHAR(255)
- `manager_id` - BIGINT FK -> users
- `opening_time` - TIME
- `closing_time` - TIME
- `max_tables` - INT
- `max_capacity` - INT
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

### `branch_settings`
Configuraciones específicas por sucursal
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches UNIQUE
- `currency` - VARCHAR(3) DEFAULT 'PEN'
- `timezone` - VARCHAR(50)
- `tax_percentage` - DECIMAL(5,2) DEFAULT 18.00
- `print_kitchen_ticket` - BOOLEAN DEFAULT true
- `print_customer_receipt` - BOOLEAN DEFAULT true
- `accept_reservations` - BOOLEAN DEFAULT true
- `accept_delivery` - BOOLEAN DEFAULT true
- `accept_takeout` - BOOLEAN DEFAULT true
- `config_json` - JSON
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 3. Logística e Inventario

### `warehouses`
Almacenes
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `code` - VARCHAR(20) UNIQUE
- `name` - VARCHAR(100)
- `type` - ENUM('main', 'kitchen', 'bar', 'storage')
- `responsible_user_id` - BIGINT FK -> users
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_categories`
Categorías de productos
- `id` - BIGINT PRIMARY KEY
- `parent_id` - BIGINT FK -> product_categories NULL
- `name` - VARCHAR(100)
- `description` - TEXT
- `icon` - VARCHAR(100)
- `color` - VARCHAR(7)
- `order` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `units`
Unidades de medida
- `id` - BIGINT PRIMARY KEY
- `code` - VARCHAR(10) UNIQUE
- `name` - VARCHAR(50)
- `abbreviation` - VARCHAR(10)
- `type` - ENUM('weight', 'volume', 'unit', 'length')
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_template`
Plantilla de producto (información general)
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(200)
- `description` - TEXT
- `category_id` - BIGINT FK -> product_categories
- `unit_id` - BIGINT FK -> units
- `type` - ENUM('ingredient', 'consumable', 'finished_product', 'service')
- `is_stockable` - BOOLEAN DEFAULT true
- `is_perishable` - BOOLEAN DEFAULT false
- `shelf_life_days` - INT NULL
- `min_stock` - DECIMAL(10,3) DEFAULT 0
- `max_stock` - DECIMAL(10,3) NULL
- `reorder_point` - DECIMAL(10,3) NULL
- `default_cost_price` - DECIMAL(10,2) DEFAULT 0
- `default_sale_price` - DECIMAL(10,2) DEFAULT 0
- `tax_percentage` - DECIMAL(5,2)
- `image` - VARCHAR(255)
- `has_variants` - BOOLEAN DEFAULT false
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

### `product_template_attributes`
Atributos configurables para plantilla (define qué atributos puede tener)
- `product_template_id` - BIGINT FK -> product_template
- `attribute_id` - BIGINT FK -> product_attributes
- PRIMARY KEY (product_template_id, attribute_id)

### `product_attributes`
Atributos de productos (color, tamaño, peso, etc.)
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(50)
- `display_name` - VARCHAR(100)
- `type` - ENUM('select', 'color', 'text', 'number')
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_attribute_values`
Valores de atributos
- `id` - BIGINT PRIMARY KEY
- `attribute_id` - BIGINT FK -> product_attributes
- `value` - VARCHAR(100)
- `display_value` - VARCHAR(100)
- `color_code` - VARCHAR(7) NULL
- `order` - INT DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_product`
Variantes específicas de productos (cada combinación de atributos)
- `id` - BIGINT PRIMARY KEY
- `product_template_id` - BIGINT FK -> product_template
- `sku` - VARCHAR(50) UNIQUE
- `barcode` - VARCHAR(100) UNIQUE NULL
- `variant_name` - VARCHAR(200) NULL (ej: "Rojo - Grande")
- `cost_price` - DECIMAL(10,2) NULL (si NULL, usa default_cost_price del template)
- `sale_price` - DECIMAL(10,2) NULL (si NULL, usa default_sale_price del template)
- `image` - VARCHAR(255) NULL
- `is_active` - BOOLEAN DEFAULT true
- `is_default_variant` - BOOLEAN DEFAULT false
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `product_product_attributes`
Valores de atributos específicos de cada variante
- `product_product_id` - BIGINT FK -> product_product
- `attribute_value_id` - BIGINT FK -> product_attribute_values
- PRIMARY KEY (product_product_id, attribute_value_id)

---

## Tablas Polimórficas

### `productables`
Relación polimórfica de productos con diferentes entidades (órdenes, compras, recetas, etc.)
- `id` - BIGINT PRIMARY KEY
- `product_product_id` - BIGINT FK -> product_product
- `productable_id` - BIGINT (ID de la entidad relacionada)
- `productable_type` - VARCHAR(50) (order_item, purchase_order_item, recipe, stock_transfer_item, etc.)
- `quantity` - DECIMAL(10,3)
- `unit_price` - DECIMAL(10,2) NULL
- `discount` - DECIMAL(10,2) DEFAULT 0
- `tax_percentage` - DECIMAL(5,2) NULL
- `subtotal` - DECIMAL(10,2) NULL
- `total` - DECIMAL(10,2) NULL
- `metadata` - JSON NULL (datos adicionales específicos del contexto)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- INDEX (productable_id, productable_type)

### `imageables`
Galería de imágenes polimórfica para cualquier entidad
- `id` - BIGINT PRIMARY KEY
- `imageable_id` - BIGINT (ID de la entidad)
- `imageable_type` - VARCHAR(50) (product_template, product_product, menu_item, user, branch, etc.)
- `image_url` - VARCHAR(255)
- `thumbnail_url` - VARCHAR(255) NULL
- `title` - VARCHAR(200) NULL
- `alt_text` - VARCHAR(255) NULL
- `order` - INT DEFAULT 0
- `is_primary` - BOOLEAN DEFAULT false
- `size_bytes` - BIGINT NULL
- `mime_type` - VARCHAR(50) NULL
- `width` - INT NULL
- `height` - INT NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- INDEX (imageable_id, imageable_type)

---

### `stock`
Inventario actual
- `id` - BIGINT PRIMARY KEY
- `warehouse_id` - BIGINT FK -> warehouses
- `product_product_id` - BIGINT FK -> product_product
- `quantity` - DECIMAL(10,3) DEFAULT 0
- `reserved_quantity` - DECIMAL(10,3) DEFAULT 0
- `available_quantity` - DECIMAL(10,3) GENERATED ALWAYS AS (quantity - reserved_quantity)
- `last_purchase_price` - DECIMAL(10,2)
- `average_cost` - DECIMAL(10,2)
- `last_movement_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `stock_movements`
Kardex - Movimientos de inventario
- `id` - BIGINT PRIMARY KEY
- `warehouse_id` - BIGINT FK -> warehouses
- `product_product_id` - BIGINT FK -> product_product
- `movement_type` - ENUM('purchase', 'sale', 'transfer', 'adjustment', 'production', 'waste', 'return')
- `reference_type` - VARCHAR(50) (purchase_order, sale_order, production_order, etc.)
- `reference_id` - BIGINT
- `quantity` - DECIMAL(10,3)
- `unit_cost` - DECIMAL(10,2)
- `total_cost` - DECIMAL(10,2)
- `previous_stock` - DECIMAL(10,3)
- `new_stock` - DECIMAL(10,3)
- `user_id` - BIGINT FK -> users
- `notes` - TEXT
- `created_at` - TIMESTAMP

### `partners`
Contactos unificados (clientes, proveedores, ambos)
- `id` - BIGINT PRIMARY KEY
- `code` - VARCHAR(20) UNIQUE
- `partner_type` - ENUM('individual', 'company')
- `business_name` - VARCHAR(200) (razón social)
- `trade_name` - VARCHAR(200) NULL (nombre comercial)
- `tax_id` - VARCHAR(20) UNIQUE NULL (RUC/DNI)
- `first_name` - VARCHAR(100) NULL
- `last_name` - VARCHAR(100) NULL
- `email` - VARCHAR(150)
- `phone` - VARCHAR(20)
- `mobile` - VARCHAR(20) NULL
- `website` - VARCHAR(255) NULL
- `birth_date` - DATE NULL (para clientes individuales)
- `address` - VARCHAR(255)
- `district` - VARCHAR(100)
- `city` - VARCHAR(100)
- `state` - VARCHAR(100)
- `country` - VARCHAR(100) DEFAULT 'PE'
- `postal_code` - VARCHAR(10)
- `latitude` - DECIMAL(10,8) NULL
- `longitude` - DECIMAL(11,8) NULL
- `is_customer` - BOOLEAN DEFAULT false
- `is_supplier` - BOOLEAN DEFAULT false
- `is_transporter` - BOOLEAN DEFAULT false
- `customer_code` - VARCHAR(20) NULL UNIQUE
- `supplier_code` - VARCHAR(20) NULL UNIQUE
- `payment_terms_days` - INT DEFAULT 0
- `credit_limit` - DECIMAL(10,2) DEFAULT 0
- `loyalty_points` - INT DEFAULT 0
- `total_orders` - INT DEFAULT 0
- `total_spent` - DECIMAL(10,2) DEFAULT 0
- `total_purchases` - DECIMAL(10,2) DEFAULT 0
- `notes` - TEXT
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP NULL

### `partner_contacts`
Personas de contacto adicionales (para empresas)
- `id` - BIGINT PRIMARY KEY
- `partner_id` - BIGINT FK -> partners
- `first_name` - VARCHAR(100)
- `last_name` - VARCHAR(100)
- `position` - VARCHAR(100) NULL (cargo)
- `email` - VARCHAR(150)
- `phone` - VARCHAR(20)
- `mobile` - VARCHAR(20) NULL
- `is_primary` - BOOLEAN DEFAULT false
- `notes` - TEXT
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `partner_addresses`
Múltiples direcciones por contacto (envío, facturación, etc.)
- `id` - BIGINT PRIMARY KEY
- `partner_id` - BIGINT FK -> partners
- `address_type` - ENUM('billing', 'shipping', 'delivery', 'other')
- `address_name` - VARCHAR(100) (ej: "Oficina Principal", "Almacén Norte")
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

### `purchase_orders`
Órdenes de compra
- `id` - BIGINT PRIMARY KEY
- `order_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK -> branches
- `warehouse_id` - BIGINT FK -> warehouses
- `partner_id` - BIGINT FK -> partners (proveedor)
- `order_date` - DATE
- `expected_delivery_date` - DATE
- `received_date` - DATE NULL
- `status` - ENUM('draft', 'pending', 'approved', 'received', 'cancelled')
- `subtotal` - DECIMAL(10,2)
- `tax` - DECIMAL(10,2)
- `total` - DECIMAL(10,2)
- `notes` - TEXT
- `created_by` - BIGINT FK -> users
- `approved_by` - BIGINT FK -> users NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `purchase_order_items`
Ítems de órdenes de compra
- `id` - BIGINT PRIMARY KEY
- `purchase_order_id` - BIGINT FK -> purchase_orders
- `product_product_id` - BIGINT FK -> product_product
- `quantity` - DECIMAL(10,3)
- `received_quantity` - DECIMAL(10,3) DEFAULT 0
- `unit_price` - DECIMAL(10,2)
- `tax_percentage` - DECIMAL(5,2)
- `subtotal` - DECIMAL(10,2)
- `total` - DECIMAL(10,2)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `stock_transfers`
Transferencias entre almacenes
- `id` - BIGINT PRIMARY KEY
- `transfer_number` - VARCHAR(20) UNIQUE
- `from_warehouse_id` - BIGINT FK -> warehouses
- `to_warehouse_id` - BIGINT FK -> warehouses
- `transfer_date` - DATE
- `status` - ENUM('pending', 'in_transit', 'received', 'cancelled')
- `notes` - TEXT
- `created_by` - BIGINT FK -> users
- `received_by` - BIGINT FK -> users NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `stock_transfer_items`
Ítems de transferencias
- `id` - BIGINT PRIMARY KEY
- `transfer_id` - BIGINT FK -> stock_transfers
- `product_product_id` - BIGINT FK -> product_product
- `quantity` - DECIMAL(10,3)
- `received_quantity` - DECIMAL(10,3) DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 4. Carta Digital y Productos

### `menu_categories`
Categorías del menú digital
- `id` - BIGINT PRIMARY KEY
- `parent_id` - BIGINT FK -> menu_categories NULL
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

### `menu_items`
Ítems del menú (platos, bebidas, etc.)
- `id` - BIGINT PRIMARY KEY
- `sku` - VARCHAR(50) UNIQUE
- `name` - VARCHAR(200)
- `description` - TEXT
- `category_id` - BIGINT FK -> menu_categories
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

### `menu_item_variants`
Variantes de ítems del menú (tamaños, extras)
- `id` - BIGINT PRIMARY KEY
- `menu_item_id` - BIGINT FK -> menu_items
- `name` - VARCHAR(100) (Pequeño, Mediano, Grande)
- `price_adjustment` - DECIMAL(10,2) (+ o -)
- `is_default` - BOOLEAN DEFAULT false
- `is_available` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `menu_item_modifiers`
Modificadores (extras, complementos)
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(100)
- `type` - ENUM('single', 'multiple')
- `min_selection` - INT DEFAULT 0
- `max_selection` - INT NULL
- `is_required` - BOOLEAN DEFAULT false
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `menu_item_modifier_options`
Opciones de modificadores
- `id` - BIGINT PRIMARY KEY
- `modifier_id` - BIGINT FK -> menu_item_modifiers
- `name` - VARCHAR(100)
- `price` - DECIMAL(10,2) DEFAULT 0
- `is_default` - BOOLEAN DEFAULT false
- `is_available` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `menu_item_modifier_relations`
Relación entre ítems del menú y modificadores
- `menu_item_id` - BIGINT FK -> menu_items
- `modifier_id` - BIGINT FK -> menu_item_modifiers
- `order` - INT DEFAULT 0
- PRIMARY KEY (menu_item_id, modifier_id)

### `recipes`
Recetas (ingredientes de cada plato)
- `id` - BIGINT PRIMARY KEY
- `menu_item_id` - BIGINT FK -> menu_items
- `product_product_id` - BIGINT FK -> product_product
- `quantity` - DECIMAL(10,3)
- `unit_id` - BIGINT FK -> units
- `waste_percentage` - DECIMAL(5,2) DEFAULT 0
- `notes` - TEXT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `combos`
Combos/Promociones
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(200)
- `description` - TEXT
- `price` - DECIMAL(10,2)
- `regular_price` - DECIMAL(10,2)
- `discount_percentage` - DECIMAL(5,2)
- `image` - VARCHAR(255)
- `start_date` - DATE
- `end_date` - DATE NULL
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `combo_items`
Ítems incluidos en combos
- `id` - BIGINT PRIMARY KEY
- `combo_id` - BIGINT FK -> combos
- `menu_item_id` - BIGINT FK -> menu_items
- `quantity` - INT DEFAULT 1
- `allow_substitution` - BOOLEAN DEFAULT false
- `created_at` - TIMESTAMP

### `branch_menu_availability`
Disponibilidad de ítems del menú por sucursal
- `branch_id` - BIGINT FK -> branches
- `menu_item_id` - BIGINT FK -> menu_items
- `is_available` - BOOLEAN DEFAULT true
- `custom_price` - DECIMAL(10,2) NULL
- `updated_at` - TIMESTAMP
- PRIMARY KEY (branch_id, menu_item_id)

---

## 5. Punto de Venta (POS)

### `pos_terminals`
Terminales de punto de venta
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `code` - VARCHAR(20) UNIQUE
- `name` - VARCHAR(100)
- `ip_address` - VARCHAR(45)
- `printer_ip` - VARCHAR(45)
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `cash_registers`
Cajas registradoras
- `id` - BIGINT PRIMARY KEY
- `terminal_id` - BIGINT FK -> pos_terminals
- `opening_balance` - DECIMAL(10,2)
- `closing_balance` - DECIMAL(10,2) NULL
- `expected_balance` - DECIMAL(10,2) NULL
- `difference` - DECIMAL(10,2) NULL
- `opened_by` - BIGINT FK -> users
- `closed_by` - BIGINT FK -> users NULL
- `opened_at` - TIMESTAMP
- `closed_at` - TIMESTAMP NULL
- `status` - ENUM('open', 'closed')
- `notes` - TEXT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `cash_movements`
Movimientos de caja
- `id` - BIGINT PRIMARY KEY
- `cash_register_id` - BIGINT FK -> cash_registers
- `type` - ENUM('income', 'expense', 'opening', 'closing', 'deposit', 'withdrawal')
- `concept` - VARCHAR(200)
- `amount` - DECIMAL(10,2)
- `payment_method` - VARCHAR(50)
- `reference` - VARCHAR(100)
- `user_id` - BIGINT FK -> users
- `notes` - TEXT
- `created_at` - TIMESTAMP

### `payment_methods`
Métodos de pago
- `id` - BIGINT PRIMARY KEY
- `code` - VARCHAR(20) UNIQUE
- `name` - VARCHAR(50)
- `type` - ENUM('cash', 'card', 'transfer', 'wallet', 'other')
- `requires_reference` - BOOLEAN DEFAULT false
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `taxes`
Impuestos configurables (SUNAT)
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(255) UNIQUE
- `description` - TEXT
- `invoice_label` - VARCHAR(255) (Texto en boleta/factura)
- `tax_type` - VARCHAR(255) (IGV, ISC, ICBPER, RETENCION, PERCEPCION, etc.)
- `affectation_type_code` - VARCHAR(2) NULL (Catálogo 07 SUNAT: 10=Gravado, 20=Exonerado, 30=Inafecto)
- `rate_percent` - DECIMAL(5,2) DEFAULT 0 (18.00 para IGV, 0.40 para ICBPER)
- `is_price_inclusive` - BOOLEAN DEFAULT false
- `is_default` - BOOLEAN DEFAULT false
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 6. Comandas y Pedidos

### `tables`
Mesas
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `area_id` - BIGINT FK -> table_areas
- `number` - VARCHAR(20)
- `capacity` - INT
- `qr_code` - VARCHAR(255)
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `table_areas`
Áreas/Zonas (salón, terraza, etc.)
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `name` - VARCHAR(100)
- `description` - TEXT
- `order` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP



### `orders`
Pedidos/Órdenes
- `id` - BIGINT PRIMARY KEY
- `order_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK -> branches
- `cash_register_id` - BIGINT FK -> cash_registers NULL (sesión de caja donde se procesó)
- `table_id` - BIGINT FK -> tables NULL
- `partner_id` - BIGINT FK -> partners NULL (cliente)
- `order_type` - ENUM('dine_in', 'takeout', 'delivery', 'digital_menu')
- `status` - ENUM('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled')
- `payment_status` - ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid'
- `order_date` - TIMESTAMP
- `scheduled_time` - TIMESTAMP NULL
- `served_time` - TIMESTAMP NULL
- `completed_time` - TIMESTAMP NULL
- `paid_at` - TIMESTAMP NULL
- `waiter_id` - BIGINT FK -> users NULL
- `cashier_id` - BIGINT FK -> users NULL (quien cobró)
- `guests_count` - INT DEFAULT 1
- `subtotal` - DECIMAL(10,2)
- `discount` - DECIMAL(10,2) DEFAULT 0
- `tax` - DECIMAL(10,2)
- `service_charge` - DECIMAL(10,2) DEFAULT 0
- `delivery_fee` - DECIMAL(10,2) DEFAULT 0
- `tip_amount` - DECIMAL(10,2) DEFAULT 0
- `total` - DECIMAL(10,2)
- `notes` - TEXT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `order_items`
Ítems de órdenes
- `id` - BIGINT PRIMARY KEY
- `order_id` - BIGINT FK -> orders
- `menu_item_id` - BIGINT FK -> menu_items
- `variant_id` - BIGINT FK -> menu_item_variants NULL
- `quantity` - INT
- `unit_price` - DECIMAL(10,2)
- `discount` - DECIMAL(10,2) DEFAULT 0
- `tax_id` - BIGINT FK -> taxes NULL
- `tax_amount` - DECIMAL(10,2) DEFAULT 0
- `subtotal` - DECIMAL(10,2)
- `total` - DECIMAL(10,2)
- `status` - ENUM('pending', 'preparing', 'ready', 'served', 'cancelled')
- `special_instructions` - TEXT
- `prepared_by` - BIGINT FK -> users NULL
- `sent_to_kitchen_at` - TIMESTAMP NULL
- `ready_at` - TIMESTAMP NULL
- `served_at` - TIMESTAMP NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `order_item_modifiers`
Modificadores aplicados a los ítems
- `id` - BIGINT PRIMARY KEY
- `order_item_id` - BIGINT FK -> order_items
- `modifier_option_id` - BIGINT FK -> menu_item_modifier_options
- `quantity` - INT DEFAULT 1
- `unit_price` - DECIMAL(10,2)
- `total` - DECIMAL(10,2)
- `created_at` - TIMESTAMP

### `order_payments`
Pagos aplicados a órdenes (permite pagos divididos)
- `id` - BIGINT PRIMARY KEY
- `order_id` - BIGINT FK -> orders
- `cash_register_id` - BIGINT FK -> cash_registers NULL
- `payment_method_id` - BIGINT FK -> payment_methods
- `amount` - DECIMAL(10,2)
- `reference_number` - VARCHAR(100) NULL (número de operación, últimos 4 dígitos de tarjeta, etc.)
- `payment_date` - TIMESTAMP
- `processed_by` - BIGINT FK -> users
- `status` - ENUM('pending', 'completed', 'cancelled', 'refunded')
- `notes` - TEXT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `kitchen_tickets`
Tickets de cocina (comandas)
- `id` - BIGINT PRIMARY KEY
- `ticket_number` - VARCHAR(20) UNIQUE
- `order_id` - BIGINT FK -> orders
- `station_id` - BIGINT FK -> kitchen_stations
- `priority` - ENUM('low', 'normal', 'high', 'urgent')
- `status` - ENUM('pending', 'preparing', 'ready', 'delivered')
- `printed_at` - TIMESTAMP NULL
- `started_at` - TIMESTAMP NULL
- `completed_at` - TIMESTAMP NULL
- `delivered_at` - TIMESTAMP NULL
- `prepared_by` - BIGINT FK -> users NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `kitchen_ticket_items`
Ítems del ticket de cocina
- `id` - BIGINT PRIMARY KEY
- `ticket_id` - BIGINT FK -> kitchen_tickets
- `order_item_id` - BIGINT FK -> order_items
- `quantity` - INT
- `status` - ENUM('pending', 'preparing', 'ready')
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `kitchen_stations`
Estaciones de cocina (parrilla, frituras, bebidas, etc.)
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `name` - VARCHAR(100)
- `description` - TEXT
- `printer_ip` - VARCHAR(45)
- `order` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `menu_item_stations`
Relación entre ítems del menú y estaciones
- `menu_item_id` - BIGINT FK -> menu_items
- `station_id` - BIGINT FK -> kitchen_stations
- PRIMARY KEY (menu_item_id, station_id)

### `reservations`
Reservas
- `id` - BIGINT PRIMARY KEY
- `reservation_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK -> branches
- `partner_id` - BIGINT FK -> partners (cliente)
- `table_id` - BIGINT FK -> tables NULL
- `reservation_date` - DATE
- `reservation_time` - TIME
- `guests_count` - INT
- `status` - ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show')
- `special_requests` - TEXT
- `confirmed_at` - TIMESTAMP NULL
- `seated_at` - TIMESTAMP NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 7. Costos y Gastos

### `expense_categories`
Categorías de gastos
- `id` - BIGINT PRIMARY KEY
- `parent_id` - BIGINT FK -> expense_categories NULL
- `name` - VARCHAR(100)
- `description` - TEXT
- `code` - VARCHAR(20) UNIQUE
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `expenses`
Gastos operativos
- `id` - BIGINT PRIMARY KEY
- `expense_number` - VARCHAR(20) UNIQUE
- `branch_id` - BIGINT FK -> branches
- `category_id` - BIGINT FK -> expense_categories
- `partner_id` - BIGINT FK -> partners NULL (proveedor)
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
- `created_by` - BIGINT FK -> users
- `approved_by` - BIGINT FK -> users NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `cost_centers`
Centros de costo
- `id` - BIGINT PRIMARY KEY
- `code` - VARCHAR(20) UNIQUE
- `name` - VARCHAR(100)
- `description` - TEXT
- `budget` - DECIMAL(10,2) DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `expense_cost_center`
Relación gastos - centros de costo
- `expense_id` - BIGINT FK -> expenses
- `cost_center_id` - BIGINT FK -> cost_centers
- `amount` - DECIMAL(10,2)
- `percentage` - DECIMAL(5,2)
- PRIMARY KEY (expense_id, cost_center_id)

### `payroll`
Nómina
- `id` - BIGINT PRIMARY KEY
- `user_id` - BIGINT FK -> users
- `branch_id` - BIGINT FK -> branches
- `period_start` - DATE
- `period_end` - DATE
- `base_salary` - DECIMAL(10,2)
- `bonuses` - DECIMAL(10,2) DEFAULT 0
- `commissions` - DECIMAL(10,2) DEFAULT 0
- `overtime` - DECIMAL(10,2) DEFAULT 0
- `deductions` - DECIMAL(10,2) DEFAULT 0
- `net_salary` - DECIMAL(10,2)
- `payment_date` - DATE
- `status` - ENUM('draft', 'approved', 'paid')
- `notes` - TEXT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `attendance`
Asistencia de empleados
- `id` - BIGINT PRIMARY KEY
- `user_id` - BIGINT FK -> users
- `branch_id` - BIGINT FK -> branches
- `attendance_date` - DATE
- `check_in` - TIME
- `check_out` - TIME NULL
- `worked_hours` - DECIMAL(5,2) NULL
- `overtime_hours` - DECIMAL(5,2) DEFAULT 0
- `status` - ENUM('present', 'absent', 'late', 'half_day', 'leave')
- `notes` - TEXT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 8. Restaurant BI (Business Intelligence)

### `sales_summary_daily`
Resumen de ventas diarias
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `summary_date` - DATE
- `total_orders` - INT
- `total_customers` - INT
- `dine_in_orders` - INT
- `takeout_orders` - INT
- `delivery_orders` - INT
- `gross_sales` - DECIMAL(10,2)
- `discounts` - DECIMAL(10,2)
- `taxes` - DECIMAL(10,2)
- `net_sales` - DECIMAL(10,2)
- `avg_ticket` - DECIMAL(10,2)
- `total_items_sold` - INT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- UNIQUE (branch_id, summary_date)

### `product_sales_summary`
Resumen de ventas por producto
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `menu_item_id` - BIGINT FK -> menu_items
- `summary_date` - DATE
- `quantity_sold` - INT
- `gross_sales` - DECIMAL(10,2)
- `net_sales` - DECIMAL(10,2)
- `cost` - DECIMAL(10,2)
- `profit` - DECIMAL(10,2)
- `profit_margin` - DECIMAL(5,2)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `category_sales_summary`
Resumen de ventas por categoría
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `category_id` - BIGINT FK -> menu_categories
- `summary_date` - DATE
- `total_items_sold` - INT
- `gross_sales` - DECIMAL(10,2)
- `net_sales` - DECIMAL(10,2)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `waiter_performance`
Rendimiento de meseros
- `id` - BIGINT PRIMARY KEY
- `user_id` - BIGINT FK -> users
- `branch_id` - BIGINT FK -> branches
- `summary_date` - DATE
- `total_orders` - INT
- `total_sales` - DECIMAL(10,2)
- `avg_ticket` - DECIMAL(10,2)
- `avg_preparation_time` - INT (minutos)
- `customer_satisfaction` - DECIMAL(3,2) NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `peak_hours`
Análisis de horas pico
- `id` - BIGINT PRIMARY KEY
- `branch_id` - BIGINT FK -> branches
- `summary_date` - DATE
- `hour` - INT (0-23)
- `total_orders` - INT
- `total_sales` - DECIMAL(10,2)
- `avg_preparation_time` - INT
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## 9. Academia

### `training_categories`
Categorías de capacitación
- `id` - BIGINT PRIMARY KEY
- `name` - VARCHAR(100)
- `description` - TEXT
- `icon` - VARCHAR(100)
- `order` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `courses`
Cursos de capacitación
- `id` - BIGINT PRIMARY KEY
- `category_id` - BIGINT FK -> training_categories
- `title` - VARCHAR(200)
- `description` - TEXT
- `duration_hours` - INT
- `level` - ENUM('beginner', 'intermediate', 'advanced')
- `thumbnail` - VARCHAR(255)
- `is_mandatory` - BOOLEAN DEFAULT false
- `is_active` - BOOLEAN DEFAULT true
- `created_by` - BIGINT FK -> users
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `course_modules`
Módulos de cursos
- `id` - BIGINT PRIMARY KEY
- `course_id` - BIGINT FK -> courses
- `title` - VARCHAR(200)
- `description` - TEXT
- `order` - INT DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `lessons`
Lecciones
- `id` - BIGINT PRIMARY KEY
- `module_id` - BIGINT FK -> course_modules
- `title` - VARCHAR(200)
- `content` - TEXT
- `content_type` - ENUM('video', 'document', 'presentation', 'quiz', 'text')
- `content_url` - VARCHAR(255)
- `duration_minutes` - INT
- `order` - INT DEFAULT 0
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `quizzes`
Cuestionarios/Exámenes
- `id` - BIGINT PRIMARY KEY
- `lesson_id` - BIGINT FK -> lessons NULL
- `course_id` - BIGINT FK -> courses NULL
- `title` - VARCHAR(200)
- `description` - TEXT
- `passing_score` - INT DEFAULT 70
- `max_attempts` - INT DEFAULT 3
- `time_limit_minutes` - INT NULL
- `is_active` - BOOLEAN DEFAULT true
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `quiz_questions`
Preguntas de cuestionarios
- `id` - BIGINT PRIMARY KEY
- `quiz_id` - BIGINT FK -> quizzes
- `question` - TEXT
- `question_type` - ENUM('multiple_choice', 'true_false', 'short_answer')
- `points` - INT DEFAULT 1
- `order` - INT DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `quiz_answers`
Respuestas de preguntas
- `id` - BIGINT PRIMARY KEY
- `question_id` - BIGINT FK -> quiz_questions
- `answer_text` - TEXT
- `is_correct` - BOOLEAN DEFAULT false
- `order` - INT DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `user_course_enrollments`
Inscripciones a cursos
- `id` - BIGINT PRIMARY KEY
- `user_id` - BIGINT FK -> users
- `course_id` - BIGINT FK -> courses
- `enrolled_at` - TIMESTAMP
- `started_at` - TIMESTAMP NULL
- `completed_at` - TIMESTAMP NULL
- `status` - ENUM('enrolled', 'in_progress', 'completed', 'dropped')
- `progress_percentage` - INT DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `user_lesson_progress`
Progreso de lecciones
- `id` - BIGINT PRIMARY KEY
- `user_id` - BIGINT FK -> users
- `lesson_id` - BIGINT FK -> lessons
- `status` - ENUM('not_started', 'in_progress', 'completed')
- `progress_percentage` - INT DEFAULT 0
- `time_spent_minutes` - INT DEFAULT 0
- `started_at` - TIMESTAMP NULL
- `completed_at` - TIMESTAMP NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `user_quiz_attempts`
Intentos de cuestionarios
- `id` - BIGINT PRIMARY KEY
- `user_id` - BIGINT FK -> users
- `quiz_id` - BIGINT FK -> quizzes
- `attempt_number` - INT
- `score` - INT
- `total_points` - INT
- `percentage` - DECIMAL(5,2)
- `passed` - BOOLEAN
- `started_at` - TIMESTAMP
- `completed_at` - TIMESTAMP NULL
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

### `user_quiz_responses`
Respuestas de usuarios a cuestionarios
- `id` - BIGINT PRIMARY KEY
- `attempt_id` - BIGINT FK -> user_quiz_attempts
- `question_id` - BIGINT FK -> quiz_questions
- `answer_id` - BIGINT FK -> quiz_answers NULL
- `answer_text` - TEXT NULL
- `is_correct` - BOOLEAN
- `points_earned` - INT
- `created_at` - TIMESTAMP

### `certifications`
Certificaciones otorgadas
- `id` - BIGINT PRIMARY KEY
- `user_id` - BIGINT FK -> users
- `course_id` - BIGINT FK -> courses
- `certificate_number` - VARCHAR(50) UNIQUE
- `issued_date` - DATE
- `expiry_date` - DATE NULL
- `certificate_url` - VARCHAR(255)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## Módulos Adicionales Recomendados

### 10. Programa de Fidelización

#### `loyalty_programs`
- Programas de puntos y recompensas
- Niveles de membresía
- Reglas de acumulación

#### `loyalty_transactions`
- Historial de puntos ganados/canjeados
- Referencia a órdenes

#### `rewards`
- Catálogo de recompensas
- Condiciones de canje

### 11. Delivery y Logística

#### `delivery_zones`
- Zonas de cobertura
- Tarifas por zona
- Tiempos estimados

#### `drivers`
- Conductores/Repartidores
- Vehículos
- Documentación

#### `delivery_assignments`
- Asignación de pedidos a drivers
- Seguimiento GPS
- Estados de entrega

#### `delivery_integrations`
- Integración con Rappi, Uber Eats, etc.
- Credenciales API
- Sincronización de pedidos

### 12. Marketing y Promociones

#### `promotions`
- Descuentos
- Cupones
- Ofertas especiales
- Reglas de aplicación

#### `customer_segments`
- Segmentación de clientes
- Campañas dirigidas

### 13. Calidad y Feedback

#### `customer_feedback`
- Encuestas de satisfacción
- Reseñas y calificaciones
- Quejas y reclamos

#### `quality_checks`
- Inspecciones de calidad
- Auditorías internas

---

## Notas Finales

### Índices Recomendados
- Todos los campos `FK` deben tener índices
- Campos usados frecuentemente en búsquedas (`order_number`, `sku`, `email`, etc.)
- Campos de fechas para reportes (`created_at`, `order_date`, `summary_date`)
- Campos de estado (`status`, `is_active`)

### Consideraciones de Performance
- Usar particionamiento en tablas grandes (`stock_movements`, `order_items`, `sales_summary_daily`)
- Implementar caché para menú digital
- Índices compuestos para consultas complejas
- Vistas materializadas para reportes BI

### Soft Deletes
Las siguientes tablas deberían implementar soft deletes (`deleted_at`):
- `product_template`
- `product_product`
- `menu_items`
- `partners`
- `users`
- `branches`
- `orders`
- `combos`
- `promotions`

### Auditoría
Considera agregar campos de auditoría en tablas críticas:
- `created_by`
- `updated_by`
- `deleted_by`

### Multi-Tenancy
El sistema está diseñado para multi-tenancy a nivel de `companies` con `branches` (sucursales).

### Próximos Pasos
1. Definir restricciones de integridad referencial
2. Crear seeders con datos de prueba
3. Implementar triggers para cálculos automáticos
4. Diseñar esquema de respaldos
5. Planificar integración con impresoras térmicas (ESC/POS)
6. Definir API REST para carta digital
7. Implementar sincronización offline para POS
