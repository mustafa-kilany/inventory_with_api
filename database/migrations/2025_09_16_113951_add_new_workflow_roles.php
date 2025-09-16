<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create new roles for enhanced workflow
        $departmentHeadRole = Role::firstOrCreate(['name' => 'department_head']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $purchaseDepartmentRole = Role::firstOrCreate(['name' => 'purchase_department']);
        
        // Create new permissions for the enhanced workflow
        $permissions = [
            'approve as department head',
            'approve as manager',
            'approve as purchase department',
            'view stock levels',
            'view department requests',
            'view all workflow requests',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Assign permissions to roles
        $departmentHeadRole->givePermissionTo([
            'approve as department head',
            'view department requests',
            'view purchase requests',
        ]);
        
        $managerRole->givePermissionTo([
            'approve as manager',
            'view department requests',
            'view purchase requests',
        ]);
        
        $purchaseDepartmentRole->givePermissionTo([
            'approve as purchase department',
            'view all workflow requests',
            'view purchase requests',
        ]);
        
        // Update existing roles with new permissions
        $administratorRole = Role::where('name', 'administrator')->first();
        if ($administratorRole) {
            $administratorRole->givePermissionTo([
                'approve as department head',
                'approve as manager', 
                'approve as purchase department',
                'view stock levels',
                'view department requests',
                'view all workflow requests',
            ]);
        }
        
        // Remove view stock levels from employee role (they shouldn't see stock levels)
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            // Remove items viewing permission from employees
            $employeeRole->revokePermissionTo('view items');
        }
        
        // Stock keeper should be able to view stock levels
        $stockKeeperRole = Role::where('name', 'stock_keeper')->first();
        if ($stockKeeperRole) {
            $stockKeeperRole->givePermissionTo('view stock levels');
        }
        
        // Create sample users for new roles
        $this->createSampleUsers();
    }
    
    private function createSampleUsers()
    {
        // Department Head
        $departmentHead = User::firstOrCreate([
            'email' => 'department.head@inventory.com'
        ], [
            'name' => 'Jane Department Head',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'employee_id' => 'DH001',
            'department' => 'Sales',
        ]);
        $departmentHead->assignRole('department_head');
        
        // Manager
        $manager = User::firstOrCreate([
            'email' => 'manager@inventory.com'
        ], [
            'name' => 'Bob Manager', 
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'employee_id' => 'MGR001',
            'department' => 'Management',
        ]);
        $manager->assignRole('manager');
        
        // Purchase Department
        $purchaseDept = User::firstOrCreate([
            'email' => 'purchase@inventory.com'
        ], [
            'name' => 'Sarah Purchase Department',
            'password' => Hash::make('password'),
            'email_verified_at' => now(), 
            'employee_id' => 'PD001',
            'department' => 'Procurement',
        ]);
        $purchaseDept->assignRole('purchase_department');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove sample users
        User::whereIn('email', [
            'department.head@inventory.com',
            'manager@inventory.com', 
            'purchase@inventory.com'
        ])->delete();
        
        // Remove permissions
        Permission::whereIn('name', [
            'approve as department head',
            'approve as manager',
            'approve as purchase department',
            'view stock levels',
            'view department requests',
            'view all workflow requests',
        ])->delete();
        
        // Remove roles
        Role::whereIn('name', [
            'department_head',
            'manager',
            'purchase_department'
        ])->delete();
    }
};