<?php

namespace App\Providers;

use App\Services\MenuAccessService;
use App\Services\LeaveApprovalWorkflowService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MenuAccessService::class);
        $this->app->singleton(LeaveApprovalWorkflowService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
