<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PosUsersSeeder extends Seeder
{
    /**
     * Create test users for POS with different roles.
     */
    public function run(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Admin POS',
                'password' => Hash::make('123456'),
            ]
        );
        $admin->syncRoles(['admin']);

        // Manager user
        $manager = User::firstOrCreate(
            ['email' => 'gerente@pos.com'],
            [
                'name' => 'Gerente Local',
                'password' => Hash::make('123456'),
            ]
        );
        $manager->syncRoles(['manager']);

        // Cashier user
        $cashier = User::firstOrCreate(
            ['email' => 'cajero@pos.com'],
            [
                'name' => 'María Cajera',
                'password' => Hash::make('123456'),
            ]
        );
        $cashier->syncRoles(['cashier']);

        // Waiter users
        $waiter1 = User::firstOrCreate(
            ['email' => 'mozo@pos.com'],
            [
                'name' => 'Carlos Mozo',
                'password' => Hash::make('123456'),
            ]
        );
        $waiter1->syncRoles(['waiter']);

        $waiter2 = User::firstOrCreate(
            ['email' => 'mozo2@pos.com'],
            [
                'name' => 'Pedro Mesero',
                'password' => Hash::make('123456'),
            ]
        );
        $waiter2->syncRoles(['waiter']);

        // Kitchen user
        $kitchen = User::firstOrCreate(
            ['email' => 'cocina@pos.com'],
            [
                'name' => 'Chef Cocina',
                'password' => Hash::make('123456'),
            ]
        );
        $kitchen->syncRoles(['kitchen']);

        $this->command->info('POS users created successfully!');
        $this->command->table(
            ['Email', 'Role', 'Password'],
            [
                ['admin@pos.com', 'admin', '123456'],
                ['gerente@pos.com', 'manager', '123456'],
                ['cajero@pos.com', 'cashier', '123456'],
                ['mozo@pos.com', 'waiter', '123456'],
                ['mozo2@pos.com', 'waiter', '123456'],
                ['cocina@pos.com', 'kitchen', '123456'],
            ]
        );
    }
}
