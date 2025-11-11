<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ViewingSessionsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Xóa các permission cũ nếu có
        $oldPermissions = ['view_viewing_sessions_profile', 'manage_viewing_sessions_view'];
        foreach ($oldPermissions as $permName) {
            $oldPermission = Permission::where('name', $permName)->first();
            if ($oldPermission) {
                $oldPermission->delete();
                $this->command->info("✓ Deleted old permission \"{$permName}\".");
            }
        }

        // Tạo permission theo format Shield standard: page_<ClassName>
        // Shield sẽ tự động nhận biết và hiển thị checkbox trong UI
        $permission = Permission::firstOrCreate([
            'name' => 'page_ManageViewingSessions',
            'guard_name' => 'web'
        ]);

        // Gán permission cho các roles admin và super_admin
        $statisticsRoles = [ 'super_admin'];
        foreach ($statisticsRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permission);
            }
        }

        $this->command->info('✓ Permission "page_ManageViewingSessions" created and assigned to admin and super_admin roles.');
    }
}

