<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Profile
        $profilePermissions = [
            'view_profile',
            'view_any_profile',
            'create_profile',
            'update_profile',
            'delete_profile',
            'delete_any_profile',
            'approve_profile',
            'reject_profile',
            'resubmit_profile',
        ];

        // Create permissions for User
        $userPermissions = [
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
        ];

        // Create permissions for Role
        $rolePermissions = [
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
        ];

        $allPermissions = array_merge($profilePermissions, $userPermissions, $rolePermissions);

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo($allPermissions);

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            // Profile permissions
            'view_profile',
            'view_any_profile',
            'create_profile',
            'update_profile',
            'delete_profile',
            'delete_any_profile',
            'approve_profile',
            'reject_profile',
            // User permissions
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            // Role permissions
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
        ]);

        $approver = Role::create(['name' => 'approver']);
        $approver->givePermissionTo([
            'view_profile',
            'view_any_profile',
            'approve_profile',
            'reject_profile',
        ]);

        $creator = Role::create(['name' => 'creator']);
        $creator->givePermissionTo([
            'view_profile',
            'view_any_profile',
            'create_profile',
            'update_profile',
            'resubmit_profile',
        ]);
    }
}
