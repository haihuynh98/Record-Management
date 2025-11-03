<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OffHoursStatisticsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo quyền cho trang thống kê ngoài giờ
        $permissions = [
            'off_hours_statistics_view',
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
                $role->givePermissionTo('off_hours_statistics_view');
            }
        }

        $this->command->info('Off Hours Statistics Permissions seeded successfully!');
    }
}

