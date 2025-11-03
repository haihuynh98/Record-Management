<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OffHoursApprovedByDateStatisticsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo quyền cho trang thống kê người duyệt ngoài giờ theo ngày
        $permissions = [
            'off_hours_approved_by_date_statistics_view',
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
                $role->givePermissionTo('off_hours_approved_by_date_statistics_view');
            }
        }

        $this->command->info('Off Hours Approved By Date Statistics Permissions seeded successfully!');
    }
}

