<?php

namespace App\Providers;

use App\Repositories\AgriculturalActivityRepository;
use App\Repositories\PlotRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar repositorios como singletons
        $this->app->singleton(PlotRepository::class, function ($app) {
            return new PlotRepository;
        });

        $this->app->singleton(AgriculturalActivityRepository::class, function ($app) {
            return new AgriculturalActivityRepository;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
