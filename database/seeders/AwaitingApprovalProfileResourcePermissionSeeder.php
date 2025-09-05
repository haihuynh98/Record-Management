<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AwaitingApprovalProfileResourcePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for AwaitingApprovalProfileResource
        $awaitingApprovalProfilePermissions = [
            'view_awaiting_approval_profile',
            'view_any_awaiting_approval_profile',
            'approve_awaiting_approval_profile',
            'reject_awaiting_approval_profile',
        ];

        foreach ($awaitingApprovalProfilePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo($awaitingApprovalProfilePermissions);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($awaitingApprovalProfilePermissions);

        $approver = Role::firstOrCreate(['name' => 'approver']);
        $approver->givePermissionTo($awaitingApprovalProfilePermissions);
    }
}
