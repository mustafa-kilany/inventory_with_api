<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Item permissions
            'view items',
            'create items',
            'edit items',
            'delete items',
            
            // Purchase request permissions
            'view purchase requests',
            'create purchase requests',
            'edit purchase requests',
            'delete purchase requests',
            'approve purchase requests',
            'fulfill purchase requests',
            
            // Stock transaction permissions
            'view stock transactions',
            'create stock transactions',
            'edit stock transactions',
            'delete stock transactions',
            
            // User management permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            
            // Admin permissions
            'access admin panel',
            'view reports',
            'manage system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $employee = Role::create(['name' => 'employee']);
        $employee->givePermissionTo([
            'view items',
            'view purchase requests',
            'create purchase requests',
            'edit purchase requests',
        ]);

        $approver = Role::create(['name' => 'approver']);
        $approver->givePermissionTo([
            'view items',
            'view purchase requests',
            'create purchase requests',
            'edit purchase requests',
            'approve purchase requests',
        ]);

        $stockKeeper = Role::create(['name' => 'stock_keeper']);
        $stockKeeper->givePermissionTo([
            'view items',
            'create items',
            'edit items',
            'view purchase requests',
            'fulfill purchase requests',
            'view stock transactions',
            'create stock transactions',
            'edit stock transactions',
        ]);

        $administrator = Role::create(['name' => 'administrator']);
        $administrator->givePermissionTo(Permission::all());

        // Create default admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@inventory.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'employee_id' => 'ADM001',
            'department' => 'IT',
        ]);
        $admin->assignRole('administrator');

        // Create sample users for each role
        $employeeUser = User::create([
            'name' => 'John Employee',
            'email' => 'employee@inventory.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'employee_id' => 'EMP001',
            'department' => 'Sales',
        ]);
        $employeeUser->assignRole('employee');

        $approverUser = User::create([
            'name' => 'Jane Approver',
            'email' => 'approver@inventory.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'employee_id' => 'APR001',
            'department' => 'Management',
        ]);
        $approverUser->assignRole('approver');

        $stockKeeperUser = User::create([
            'name' => 'Bob Stockkeeper',
            'email' => 'stockkeeper@inventory.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'employee_id' => 'STK001',
            'department' => 'Warehouse',
        ]);
        $stockKeeperUser->assignRole('stock_keeper');
    }
}
