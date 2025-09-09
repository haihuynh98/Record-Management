<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProfileApprovedByStatisticsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo quyền cho trang thống kê người duyệt
        $permissions = [
            'profile_approved_by_statistics_view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Gán quyền cho các role có thể xem thống kê
        $statisticsRoles = ['admin', 'super_admin', 'approver'];
        foreach ($statisticsRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo('profile_approved_by_statistics_view');
            }
        }

        $this->command->info('Profile Approved By Statistics Permissions seeded successfully!');
    }
}
