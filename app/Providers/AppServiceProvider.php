<?php

namespace App\Providers;

use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Cấu hình polling cho database notifications (30 giây)
        DatabaseNotifications::pollingInterval('30s');
    }
}
