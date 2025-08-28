<?php

namespace App\Console\Commands;

use App\Jobs\CleanupProfileViewingSessions;
use Illuminate\Console\Command;

class CleanupProfileSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile-sessions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup inactive profile viewing sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of inactive profile viewing sessions...');
        
        CleanupProfileViewingSessions::dispatch();
        
        $this->info('Cleanup job dispatched successfully.');
    }
}
