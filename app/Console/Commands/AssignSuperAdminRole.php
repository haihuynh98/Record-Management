<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignSuperAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-super-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign super_admin role to a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        // Find user by email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }
        
        // Find super_admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        if (!$superAdminRole) {
            $this->error("Super admin role not found! Please run the permission seeder first.");
            return 1;
        }
        
        // Remove all existing roles
        $user->syncRoles([]);
        
        // Assign super_admin role
        $user->assignRole($superAdminRole);
        
        $this->info("Successfully assigned super_admin role to user: {$user->name} ({$user->email})");
        
        // Show current roles
        $roles = $user->roles->pluck('name')->toArray();
        $this->info("Current roles: " . implode(', ', $roles));
        
        return 0;
    }
}
