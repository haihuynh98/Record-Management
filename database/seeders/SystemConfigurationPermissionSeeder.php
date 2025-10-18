<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SystemConfigurationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permission cho quản lý cấu hình hệ thống
        $permissionId = \DB::table('permissions')
            ->where('name', 'manage_system_configuration')
            ->where('guard_name', 'web')
            ->value('id');
            
        if (!$permissionId) {
            $permissionId = \DB::table('permissions')->insertGetId([
                'name' => 'manage_system_configuration',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $permission = Permission::find($permissionId);

        // Gán permission cho role admin và super_admin
        $adminRole = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }
    }
}
