<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main company (matriz)
        $company = Company::create([
            'parent_id' => null,
            'name' => 'RestoPeru SAC',
            'business_name' => 'RESTAURANTES DEL PERÚ S.A.C.',
            'trade_name' => 'RestoPeru',
            'tax_id' => '20123456789',
            'logo' => '/images/logo.png',
            'phone' => '+51 999 888 777',
            'email' => 'contacto@restoperu.com',
            'website' => 'https://restoperu.com',
            'is_active' => true,
        ]);

        // Create main branch (Sucursal Principal - Miraflores)
        $mainBranch = Company::create([
            'parent_id' => $company->id,
            'code' => 'MIR-001',
            'name' => 'Sucursal Miraflores',
            'business_name' => 'RESTAURANTES DEL PERÚ S.A.C.',
            'trade_name' => 'RestoPeru Miraflores',
            'tax_id' => '20123456789',
            'phone' => '+51 999 111 222',
            'email' => 'miraflores@restoperu.com',
            'website' => 'https://restoperu.com',
            'address' => 'Av. Larco 1234, Miraflores, Lima',
            'ubigeo_code' => '150122',
            'country' => 'PE',
            'latitude' => -12.119659,
            'longitude' => -77.030949,
            'opening_time' => '08:00:00',
            'closing_time' => '22:00:00',
            'max_tables' => 20,
            'max_capacity' => 80,
            'currency' => 'PEN',
            'timezone' => 'America/Lima',
            'tax_percentage' => 18.00,
            'print_kitchen_ticket' => true,
            'print_customer_receipt' => true,
            'accept_reservations' => true,
            'accept_delivery' => true,
            'accept_takeout' => true,
            'is_active' => true,
        ]);

        // Create second branch (San Isidro)
        Company::create([
            'parent_id' => $company->id,
            'code' => 'SIS-001',
            'name' => 'Sucursal San Isidro',
            'business_name' => 'RESTAURANTES DEL PERÚ S.A.C.',
            'trade_name' => 'RestoPeru San Isidro',
            'tax_id' => '20123456789',
            'phone' => '+51 999 333 444',
            'email' => 'sanisidro@restoperu.com',
            'website' => 'https://restoperu.com',
            'address' => 'Av. Conquistadores 567, San Isidro, Lima',
            'ubigeo_code' => '150131',
            'country' => 'PE',
            'latitude' => -12.095893,
            'longitude' => -77.035449,
            'opening_time' => '09:00:00',
            'closing_time' => '23:00:00',
            'max_tables' => 15,
            'max_capacity' => 60,
            'currency' => 'PEN',
            'timezone' => 'America/Lima',
            'tax_percentage' => 18.00,
            'print_kitchen_ticket' => true,
            'print_customer_receipt' => true,
            'accept_reservations' => true,
            'accept_delivery' => true,
            'accept_takeout' => true,
            'is_active' => true,
        ]);

        $this->command->info('✅ Company and branches created successfully');
    }
}
