<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seefds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
        ]);

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            DashboardChartPermissionSeeder::class,
            AwaitingApprovalProfilesPermissionSeeder::class,
            ProfileApprovedByStatisticsPermissionSeeder::class,
            ProfileApprovedByDateStatisticsPermissionSeeder::class,
            OffHoursStatisticsPermissionSeeder::class,
            OffHoursDateStatisticsPermissionSeeder::class,
            OffHoursApprovedByStatisticsPermissionSeeder::class,
            OffHoursApprovedByDateStatisticsPermissionSeeder::class,
        ]);

    }
}
