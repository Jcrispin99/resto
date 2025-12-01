<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main company
        $company = Company::create([
            'business_name' => 'RESTAURANTES DEL PERÚ S.A.C.',
            'trade_name' => 'RestoPeru',
            'tax_id' => '20123456789',
            'logo' => '/images/logo.png',
            'is_active' => true,
        ]);

        // Create main branch (Casa Matriz) with all settings
        Branch::create([
            'company_id' => $company->id,
            'code' => 'M001',
            'name' => 'Casa Matriz - Miraflores',
            'business_name' => 'RESTAURANTES DEL PERÚ S.A.C.',
            'tax_id' => '20123456789',
            'address' => 'Av. Larco 1234',
            'ubigeo_code' => '150122', // Lima > Lima > Miraflores
            'country' => 'PE',
            'latitude' => -12.119820,
            'longitude' => -77.031440,
            'phone' => '+51 1 4441234',
            'email' => 'miraflores@restoperu.pe',
            'website' => 'https://restoperu.pe',
            'manager_id' => null,
            'opening_time' => '12:00:00',
            'closing_time' => '23:00:00',
            'max_tables' => 25,
            'max_capacity' => 100,
            'is_active' => true,
            // Settings
            'currency' => 'PEN',
            'timezone' => 'America/Lima',
            'tax_percentage' => 18.00,
            'print_kitchen_ticket' => true,
            'print_customer_receipt' => true,
            'accept_reservations' => true,
            'accept_delivery' => true,
            'accept_takeout' => true,
            'config_json' => [
                'auto_close_orders' => true,
                'auto_close_minutes' => 15,
                'require_waiter_assignment' => true,
                'allow_split_payments' => true,
            ],
        ]);

        // Create second branch with different settings
        Branch::create([
            'company_id' => $company->id,
            'code' => 'S002',
            'name' => 'Sucursal San Isidro',
            'business_name' => 'RESTAURANTES DEL PERÚ S.A.C.',
            'tax_id' => '20123456789',
            'address' => 'Av. Conquistadores 456',
            'ubigeo_code' => '150131', // Lima > Lima > San Isidro
            'country' => 'PE',
            'latitude' => -12.095370,
            'longitude' => -77.036540,
            'phone' => '+51 1 4445678',
            'email' => 'sanisidro@restoperu.pe',
            'website' => 'https://restoperu.pe',
            'manager_id' => null,
            'opening_time' => '12:00:00',
            'closing_time' => '00:00:00',
            'max_tables' => 30,
            'max_capacity' => 120,
            'is_active' => true,
            // Settings
            'currency' => 'PEN',
            'timezone' => 'America/Lima',
            'tax_percentage' => 18.00,
            'print_kitchen_ticket' => true,
            'print_customer_receipt' => true,
            'accept_reservations' => true,
            'accept_delivery' => false, // Este local no hace delivery
            'accept_takeout' => true,
            'config_json' => [
                'auto_close_orders' => true,
                'auto_close_minutes' => 20,
                'require_waiter_assignment' => true,
                'allow_split_payments' => true,
            ],
        ]);

        $this->command->info('✅ Created company with 2 branches (unified model)');
    }
}
