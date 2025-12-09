<?php

namespace Database\Seeders;

use App\Models\KitchenStation;
use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\ProductTemplate;
use App\Models\Table;
use App\Models\TableArea;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PosTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create test users
        $this->createUsers();

        // 2. Create table areas and tables
        $this->createTables();

        // 3. Create kitchen stations
        $this->createKitchenStations();

        // 4. Create payment methods
        $this->createPaymentMethods();

        // 5. Create product categories
        $categories = $this->createCategories();

        // 6. Create products
        $this->createProducts($categories);

        $this->command->info('✅ POS test data created successfully!');
    }

    private function createUsers(): void
    {
        // Test POS user
        User::firstOrCreate(
            ['email' => 'test@pos.com'],
            [
                'name' => 'Mozo Demo',
                'password' => Hash::make('password123'),
            ]
        );

        // Test cashier
        User::firstOrCreate(
            ['email' => 'cajero@pos.com'],
            [
                'name' => 'Cajero Demo',
                'password' => Hash::make('password123'),
            ]
        );

        $this->command->info('✅ Users created');
    }

    private function createTables(): void
    {
        // Get first company/branch
        $branchId = 1; // Default company ID

        // Create table areas
        $salaArea = TableArea::firstOrCreate(
            ['name' => 'Sala Principal', 'branch_id' => $branchId],
            [
                'description' => 'Área principal del restaurante',
                'is_active' => true,
            ]
        );

        $terraza = TableArea::firstOrCreate(
            ['name' => 'Terraza', 'branch_id' => $branchId],
            [
                'description' => 'Área exterior',
                'is_active' => true,
            ]
        );

        $vip = TableArea::firstOrCreate(
            ['name' => 'Área VIP', 'branch_id' => $branchId],
            [
                'description' => 'Zona reservada premium',
                'is_active' => true,
            ]
        );

        // Create tables for each area
        $tablesData = [
            // Sala Principal
            ['area' => $salaArea, 'number' => '1', 'capacity' => 4],
            ['area' => $salaArea, 'number' => '2', 'capacity' => 4],
            ['area' => $salaArea, 'number' => '3', 'capacity' => 2],
            ['area' => $salaArea, 'number' => '4', 'capacity' => 6],
            ['area' => $salaArea, 'number' => '5', 'capacity' => 4],
            ['area' => $salaArea, 'number' => '6', 'capacity' => 2],

            // Terraza
            ['area' => $terraza, 'number' => '7', 'capacity' => 4],
            ['area' => $terraza, 'number' => '8', 'capacity' => 4],
            ['area' => $terraza, 'number' => '9', 'capacity' => 6],
            ['area' => $terraza, 'number' => '10', 'capacity' => 8],

            // VIP
            ['area' => $vip, 'number' => '11', 'capacity' => 8],
            ['area' => $vip, 'number' => '12', 'capacity' => 10],
        ];

        foreach ($tablesData as $tableData) {
            Table::firstOrCreate(
                ['number' => $tableData['number'], 'branch_id' => $branchId],
                [
                    'area_id' => $tableData['area']->id,
                    'capacity' => $tableData['capacity'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Tables and areas created');
    }

    private function createKitchenStations(): void
    {
        $branchId = 1;

        $stations = [
            'Cocina Caliente',
            'Cocina Fría',
            'Parrilla',
            'Bar',
            'Postres',
        ];

        foreach ($stations as $name) {
            KitchenStation::firstOrCreate(
                ['name' => $name, 'branch_id' => $branchId],
                [
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Kitchen stations created');
    }

    private function createPaymentMethods(): void
    {
        $methods = [
            ['name' => 'Efectivo', 'code' => 'CASH'],
            ['name' => 'Tarjeta', 'code' => 'CARD'],
            ['name' => 'Yape', 'code' => 'YAPE'],
            ['name' => 'Plin', 'code' => 'PLIN'],
            ['name' => 'Transferencia', 'code' => 'TRANSFER'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Payment methods created');
    }

    private function createCategories(): array
    {
        $categories = [
            ['name' => 'Entradas', 'type' => 'menu'],
            ['name' => 'Platos Principales', 'type' => 'menu'],
            ['name' => 'Carnes', 'type' => 'menu'],
            ['name' => 'Pescados', 'type' => 'menu'],
            ['name' => 'Pastas', 'type' => 'menu'],
            ['name' => 'Pizza', 'type' => 'menu'],
            ['name' => 'Bebidas', 'type' => 'menu'],
            ['name' => 'Postres', 'type' => 'menu'],
        ];

        $result = [];
        foreach ($categories as $catData) {
            $category = ProductCategory::firstOrCreate(
                ['name' => $catData['name']],
                [
                    'type' => $catData['type'],
                    'is_active' => true,
                ]
            );
            $result[$catData['name']] = $category;
        }

        $this->command->info('✅ Categories created');

        return $result;
    }

    private function createProducts(array $categories): void
    {
        $hotStation = KitchenStation::where('name', 'Cocina Caliente')->first();
        $coldStation = KitchenStation::where('name', 'Cocina Fría')->first();
        $grillStation = KitchenStation::where('name', 'Parrilla')->first();
        $barStation = KitchenStation::where('name', 'Bar')->first();
        $dessertStation = KitchenStation::where('name', 'Postres')->first();

        $products = [
            // Entradas
            ['name' => 'Ensalada César', 'category' => 'Entradas', 'price' => 18.00, 'station' => $coldStation, 'desc' => 'Lechuga romana, crutones, queso parmesano'],
            ['name' => 'Tequeños', 'category' => 'Entradas', 'price' => 15.00, 'station' => $hotStation, 'desc' => '6 unidades con salsas'],
            ['name' => 'Alitas BBQ', 'category' => 'Entradas', 'price' => 22.00, 'station' => $grillStation, 'desc' => '10 aliñas con salsa BBQ'],

            // Platos Principales
            ['name' => 'Lomo Saltado', 'category' => 'Platos Principales', 'price' => 32.00, 'station' => $hotStation, 'desc' => 'Carne, papas fritas, arroz'],
            ['name' => 'Arroz con Pollo', 'category' => 'Platos Principales', 'price' => 28.00, 'station' => $hotStation, 'desc' => 'Tradicional arroz con pollo y ensalada'],

            // Carnes
            ['name' => 'Bife Angosto', 'category' => 'Carnes', 'price' => 45.00, 'station' => $grillStation, 'desc' => '300g con guarnición'],
            ['name' => 'Churrasco', 'category' => 'Carnes', 'price' => 38.00, 'station' => $grillStation, 'desc' => 'Carne a la parrilla con papas'],

            // Pescados
            ['name' => 'Ceviche Clásico', 'category' => 'Pescados', 'price' => 35.00, 'station' => $coldStation, 'desc' => 'Pescado fresco, limón, cebolla'],
            ['name' => 'Tiradito', 'category' => 'Pescados', 'price' => 33.00, 'station' => $coldStation, 'desc' => 'Pescado en láminas con crema'],

            // Pastas
            ['name' => 'Spaghetti Carbonara', 'category' => 'Pastas', 'price' => 26.00, 'station' => $hotStation, 'desc' => 'Pasta con salsa carbonara'],
            ['name' => 'Fetuccini Alfredo', 'category' => 'Pastas', 'price' => 28.00, 'station' => $hotStation, 'desc' => 'Con salsa blanca y pollo'],

            // Pizza
            ['name' => 'Pizza Margarita', 'category' => 'Pizza', 'price' => 24.00, 'station' => $hotStation, 'desc' => 'Clásica con tomate y albahaca'],
            ['name' => 'Pizza Americana', 'category' => 'Pizza', 'price' => 28.00, 'station' => $hotStation, 'desc' => 'Pepperoni, jamón, champiñones'],

            // Bebidas
            ['name' => 'Chicha Morada', 'category' => 'Bebidas', 'price' => 8.00, 'station' => $barStation, 'desc' => '1 litro'],
            ['name' => 'Limonada', 'category' => 'Bebidas', 'price' => 7.00, 'station' => $barStation, 'desc' => 'Natural 500ml'],
            ['name' => 'Inca Kola', 'category' => 'Bebidas', 'price' => 5.00, 'station' => $barStation, 'desc' => 'Botella 500ml'],
            ['name' => 'Cerveza Pilsen', 'category' => 'Bebidas', 'price' => 12.00, 'station' => $barStation, 'desc' => 'Botella 650ml'],

            // Postres
            ['name' => 'Suspiro Limeño', 'category' => 'Postres', 'price' => 12.00, 'station' => $dessertStation, 'desc' => 'Postre tradicional peruano'],
            ['name' => 'Brownie con Helado', 'category' => 'Postres', 'price' => 14.00, 'station' => $dessertStation, 'desc' => 'Brownie tibio con helado de vainilla'],
            ['name' => 'Tres Leches', 'category' => 'Postres', 'price' => 13.00, 'station' => $dessertStation, 'desc' => 'Torta húmeda de tres leches'],
        ];

        foreach ($products as $prodData) {
            $category = $categories[$prodData['category']];

            ProductTemplate::firstOrCreate(
                ['name' => $prodData['name']],
                [
                    'internal_reference' => strtoupper(substr($prodData['name'], 0, 3)).rand(100, 999),
                    'description' => $prodData['desc'],
                    'product_type' => 'consumable',
                    'sale_price' => $prodData['price'],
                    'can_be_sold' => true,
                    'category_id' => $category->id,
                    'kitchen_station_id' => $prodData['station']->id,
                    'unit_id' => 1, // Default unit
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Products created');
    }
}
