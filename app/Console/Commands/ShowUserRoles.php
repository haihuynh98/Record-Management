<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ShowUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:show-roles {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show user roles by email or all users if no email provided';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            // Show specific user
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("User with email '{$email}' not found!");
                return 1;
            }
            
            $this->showUserInfo($user);
        } else {
            // Show all users
            $users = User::with('roles')->get();
            
            if ($users->isEmpty()) {
                $this->info("No users found in the system.");
                return 0;
            }
            
            $this->info("All users and their roles:");
            $this->newLine();
            
            foreach ($users as $user) {
                $this->showUserInfo($user);
                $this->newLine();
            }
        }
        
        return 0;
    }
    
    private function showUserInfo(User $user)
    {
        $roles = $user->roles->pluck('name')->toArray();
        $roleText = empty($roles) ? 'No roles' : implode(', ', $roles);
        
        $this->info("User: {$user->name} ({$user->email})");
        $this->line("Roles: {$roleText}");
        $this->line("Created: {$user->created_at->format('Y-m-d H:i:s')}");
    }
}
