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
        // Create dashboard charts permission
        $permission = Permission::firstOrCreate(['name' => 'view_dashboard_charts']);

        // Assign permission to roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permission);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permission);
        }

        $this->command->info('Dashboard charts permission has been created and assigned to roles.');
    }
}
