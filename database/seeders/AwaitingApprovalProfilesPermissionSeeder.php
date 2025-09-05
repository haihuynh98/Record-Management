<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AwaitingApprovalProfilesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permission for viewing awaiting approval profiles
        $permission = 'view_awaiting_approval_profile';
        Permission::firstOrCreate(['name' => $permission]);

        // Assign permission to roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo($permission);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($permission);

        $approver = Role::firstOrCreate(['name' => 'approver']);
        $approver->givePermissionTo($permission);
    }
}
