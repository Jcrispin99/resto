<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================
        // Define Permissions
        // ========================
        
        $permissions = [
            // POS - Mesas
            'pos.tables.view' => 'Ver mesas',
            'pos.tables.manage' => 'Gestionar mesas',
            
            // POS - Órdenes
            'pos.orders.create' => 'Crear órdenes',
            'pos.orders.view' => 'Ver órdenes',
            'pos.orders.edit' => 'Editar órdenes',
            'pos.orders.cancel' => 'Cancelar órdenes',
            'pos.orders.close' => 'Cerrar cuenta',
            
            // POS - Pagos
            'pos.payments.view' => 'Ver pagos pendientes',
            'pos.payments.process' => 'Procesar pagos',
            'pos.payments.refund' => 'Realizar devoluciones',
            
            // POS - Caja
            'pos.cashier.view' => 'Ver caja',
            'pos.cashier.open' => 'Abrir caja',
            'pos.cashier.close' => 'Cerrar caja',
            'pos.cashier.movements' => 'Gestionar movimientos de caja',
            
            // Cocina
            'kitchen.tickets.view' => 'Ver tickets de cocina',
            'kitchen.tickets.manage' => 'Gestionar tickets',
            
            // Productos
            'products.view' => 'Ver productos',
            'products.create' => 'Crear productos',
            'products.edit' => 'Editar productos',
            'products.delete' => 'Eliminar productos',
            
            // Categorías
            'categories.view' => 'Ver categorías',
            'categories.manage' => 'Gestionar categorías',
            
            // Usuarios
            'users.view' => 'Ver usuarios',
            'users.create' => 'Crear usuarios',
            'users.edit' => 'Editar usuarios',
            'users.delete' => 'Eliminar usuarios',
            
            // Reportes
            'reports.sales' => 'Ver reportes de ventas',
            'reports.inventory' => 'Ver reportes de inventario',
            'reports.financial' => 'Ver reportes financieros',
            
            // Configuración
            'settings.view' => 'Ver configuración',
            'settings.manage' => 'Gestionar configuración',
        ];

        // Create permissions
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // ========================
        // Define Roles
        // ========================

        // Super Admin - Acceso total
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Acceso administrativo
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'pos.tables.view', 'pos.tables.manage',
            'pos.orders.create', 'pos.orders.view', 'pos.orders.edit', 'pos.orders.cancel', 'pos.orders.close',
            'pos.payments.view', 'pos.payments.process', 'pos.payments.refund',
            'pos.cashier.view', 'pos.cashier.open', 'pos.cashier.close', 'pos.cashier.movements',
            'kitchen.tickets.view', 'kitchen.tickets.manage',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.view', 'categories.manage',
            'users.view', 'users.create', 'users.edit',
            'reports.sales', 'reports.inventory', 'reports.financial',
            'settings.view',
        ]);

        // Manager - Gerente de local
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'pos.tables.view', 'pos.tables.manage',
            'pos.orders.create', 'pos.orders.view', 'pos.orders.edit', 'pos.orders.close',
            'pos.payments.view', 'pos.payments.process',
            'pos.cashier.view', 'pos.cashier.open', 'pos.cashier.close', 'pos.cashier.movements',
            'kitchen.tickets.view',
            'products.view',
            'categories.view',
            'users.view',
            'reports.sales',
        ]);

        // Cashier - Cajero
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'pos.payments.view', 'pos.payments.process',
            'pos.cashier.view', 'pos.cashier.open', 'pos.cashier.close',
            'pos.orders.view',
        ]);

        // Waiter - Mozo
        $waiter = Role::firstOrCreate(['name' => 'waiter']);
        $waiter->givePermissionTo([
            'pos.tables.view',
            'pos.orders.create', 'pos.orders.view', 'pos.orders.edit', 'pos.orders.close',
            'products.view',
            'categories.view',
        ]);

        // Kitchen - Cocina
        $kitchen = Role::firstOrCreate(['name' => 'kitchen']);
        $kitchen->givePermissionTo([
            'kitchen.tickets.view', 'kitchen.tickets.manage',
            'pos.orders.view',
        ]);

        // ========================
        // Assign roles to existing users (optional)
        // ========================
        
        // Assign super-admin to first user if exists
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasAnyRole(Role::all())) {
            $firstUser->assignRole('super-admin');
        }

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->table(
            ['Role', 'Permissions Count'],
            [
                ['super-admin', Permission::count()],
                ['admin', $admin->permissions->count()],
                ['manager', $manager->permissions->count()],
                ['cashier', $cashier->permissions->count()],
                ['waiter', $waiter->permissions->count()],
                ['kitchen', $kitchen->permissions->count()],
            ]
        );
    }
}
