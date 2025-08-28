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
            'cancel_profile',
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

        // Create permissions for Profile Statistics Page
        $profileStatisticsPermissions = [
            'view_profile_statistics',
        ];

        // Create permissions for Dashboard Charts
        $dashboardChartPermissions = [
            'view_dashboard_charts',
        ];

        $allPermissions = array_merge($profilePermissions, $userPermissions, $rolePermissions, $profileStatisticsPermissions, $dashboardChartPermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions($allPermissions);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            // Profile permissions
            'view_profile',
            'view_any_profile',
            'create_profile',
            'update_profile',
            'delete_profile',
            'delete_any_profile',
            'approve_profile',
            'reject_profile',
            'cancel_profile',
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
            // Profile Statistics permissions
            'view_profile_statistics',
            // Dashboard Charts permissions
            'view_dashboard_charts',
        ]);

        $approver = Role::firstOrCreate(['name' => 'approver']);
        $approver->givePermissionTo([
            'view_profile',
            'view_any_profile',
            'approve_profile',
            'reject_profile',
            'cancel_profile',
        ]);

        $creator = Role::firstOrCreate(['name' => 'creator']);
        $creator->givePermissionTo([
            'view_profile',
            'view_any_profile',
            'create_profile',
            'update_profile',
            'resubmit_profile',
        ]);
    }
}
