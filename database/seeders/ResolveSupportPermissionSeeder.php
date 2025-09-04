<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ResolveSupportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permission resolve_support
        $permission = Permission::firstOrCreate([
            'name' => 'resolve_support',
            'guard_name' => 'web'
        ]);

        // Gán permission cho các role admin và super_admin
        $adminRole = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }

        $this->command->info('Permission resolve_support đã được tạo và gán cho admin roles.');
    }
}
