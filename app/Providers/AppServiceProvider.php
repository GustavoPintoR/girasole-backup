<?php

namespace App\Providers;

use App\Helpers\CompanyHelper;
use App\Interfaces\CompanyInterface;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        CompanyHelper::setCompanyClassesConfig();
        $this->app->singleton(CompanyInterface::class, CompanyService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });

        if ($this->app->runningInConsole()) {
            Actions::registerCommands();
        }

        RateLimiter::for('notifications', function ($job) {
            return Limit::perSecond(2, 1);
        });
    }
}
