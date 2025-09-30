<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateDocZuhairSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Ensure owner-specific permissions exist
        $approveAsOwner     = Permission::firstOrCreate(['name' => 'approve as owner']);
        $ownerForceApprove  = Permission::firstOrCreate(['name' => 'owner force approve']);

        // 2) Create Owner role (no admin powers)
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);

        // Grant only the owner-specific permissions here
        $ownerRole->syncPermissions([$approveAsOwner, $ownerForceApprove]);

        // 3) Create/Update the user
        $user = User::firstOrCreate(
            ['email' => 'doc.zuhair@inventory.com'],
            [
                'name'        => 'Doc. Zuhair',
                'password'    => Hash::make('password'), // change in production
                'department'  => 'Executive',
                'employee_id' => 'OWN-001',
                'is_active'   => true,
            ]
        );

        // 4) Ensure he is NOT an administrator
        if ($user->hasRole('administrator')) {
            $user->removeRole('administrator');
        }

        // 5) Ensure he has only the owner role (add if missing)
        if (!$user->hasRole('owner')) {
            $user->assignRole('owner');
        }

        // If you want the owner to also VIEW things (but still NOT admin),
        // you can optionally give him read-only-ish permissions here, e.g.:
        // $viewItems = Permission::firstOrCreate(['name' => 'view items']);
        // $viewPurchaseRequests = Permission::firstOrCreate(['name' => 'view purchase requests']);
        // $ownerRole->givePermissionTo([$viewItems, $viewPurchaseRequests]);
    }
}
