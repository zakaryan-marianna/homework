<?php

namespace App\Providers;

use App\Contracts\CurrencyManager;
use App\Services\CurrencyManagerService;
use App\Services\OperationHandlerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrencyManager::class, CurrencyManagerService::class);
        $this->app->singleton(OperationHandlerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
