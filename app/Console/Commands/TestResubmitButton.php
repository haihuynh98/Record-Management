<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Profile;
use App\Models\User;

class TestResubmitButton extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:resubmit-button {user_email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test resubmit button visibility for different users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userEmail = $this->argument('user_email') ?? 'haitao1';
        $user = User::where('email', $userEmail)->first();

        if (!$user) {
            $this->error("User with email '{$userEmail}' not found!");
            return 1;
        }

        $this->info("=== TESTING RESUBMIT BUTTON FOR USER: {$user->email} ===");
        $this->info("Environment: " . config('app.env'));
        $this->info("User ID: {$user->id}");
        $this->info("User Roles: " . $user->roles->pluck('name')->implode(', '));
        $this->info("User Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->info("");

        // Get profiles visible to this user
        $query = Profile::query();
        if ($user->hasRole('creator')) {
            $query->where('created_by', $user->id);
        } elseif ($user->hasRole('approver')) {
            $query->where('status', 0);
        }
        // Admin and super_admin can see all profiles

        $profiles = $query->get();

        $this->info("Profiles visible to user:");
        $this->table(
            ['ID', 'Code', 'Status', 'Created By', 'Has Resubmit Permission', 'Is Creator', 'Is Admin', 'Should Show Resubmit'],
            $profiles->map(function ($profile) use ($user) {
                $hasPermission = $user->hasPermissionTo('resubmit_profile');
                $isRejected = $profile->status === 2;
                $isCreator = $profile->created_by === $user->id;
                $isAdmin = $user->hasRole(['admin', 'super_admin']);
                $shouldShow = $hasPermission && $isRejected && ($isCreator || $isAdmin);

                return [
                    $profile->id,
                    $profile->code,
                    $profile->status . ' (' . ($profile->status === 0 ? 'Chờ duyệt' : ($profile->status === 1 ? 'Đã duyệt' : 'Từ chối')) . ')',
                    $profile->created_by,
                    $hasPermission ? 'YES' : 'NO',
                    $isCreator ? 'YES' : 'NO',
                    $isAdmin ? 'YES' : 'NO',
                    $shouldShow ? 'YES' : 'NO'
                ];
            })->toArray()
        );

        // Test specific rejected profiles
        $rejectedProfiles = Profile::where('status', 2)->get();
        if ($rejectedProfiles->count() > 0) {
            $this->info("");
            $this->info("All rejected profiles (status = 2):");
            $this->table(
                ['ID', 'Code', 'Created By', 'Creator Email', 'Should Show for Current User'],
                $rejectedProfiles->map(function ($profile) use ($user) {
                    $creator = User::find($profile->created_by);
                    $hasPermission = $user->hasPermissionTo('resubmit_profile');
                    $isCreator = $profile->created_by === $user->id;
                    $isAdmin = $user->hasRole(['admin', 'super_admin']);
                    $shouldShow = $hasPermission && ($isCreator || $isAdmin);

                    return [
                        $profile->id,
                        $profile->code,
                        $profile->created_by,
                        $creator ? $creator->email : 'Unknown',
                        $shouldShow ? 'YES' : 'NO'
                    ];
                })->toArray()
            );
        }

        return 0;
    }
}
