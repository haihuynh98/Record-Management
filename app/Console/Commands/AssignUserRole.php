<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role {email} {role} {--replace : Replace all existing roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');
        $replace = $this->option('replace');
        
        // Find user by email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }
        
        // Find role
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("Role '{$roleName}' not found!");
            $this->info("Available roles: " . Role::pluck('name')->implode(', '));
            return 1;
        }
        
        // Show current roles
        $currentRoles = $user->roles->pluck('name')->toArray();
        $this->info("Current roles: " . implode(', ', $currentRoles));
        
        if ($replace) {
            // Remove all existing roles and assign new one
            $user->syncRoles([$roleName]);
            $this->info("Replaced all roles with '{$roleName}'");
        } else {
            // Add role to existing roles
            if (in_array($roleName, $currentRoles)) {
                $this->warn("User already has role '{$roleName}'");
                return 0;
            }
            $user->assignRole($role);
            $this->info("Added role '{$roleName}' to user");
        }
        
        // Show updated roles
        $updatedRoles = $user->fresh()->roles->pluck('name')->toArray();
        $this->info("Updated roles: " . implode(', ', $updatedRoles));
        
        return 0;
    }
}
