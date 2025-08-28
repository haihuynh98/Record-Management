<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardChartPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các quyền mới
        $permissions = [
            'approve_profile',
            'reject_profile', 
            'resubmit_profile'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Gán quyền approve và reject cho role admin, super_admin, và approver
        $approveRejectRoles = ['admin', 'super_admin', 'approver'];
        foreach ($approveRejectRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo(['approve_profile', 'reject_profile']);
            }
        }

        // Gán quyền resubmit cho role creator
        $creatorRole = Role::where('name', 'creator')->first();
        if ($creatorRole) {
            $creatorRole->givePermissionTo('resubmit_profile');
        }

        // Gán tất cả quyền cho super_admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo('resubmit_profile');
        }

        $this->command->info('Dashboard Chart Permissions seeded successfully!');
    }
}
