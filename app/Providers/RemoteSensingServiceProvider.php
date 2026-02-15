<?php

namespace App\Providers;

use App\Contracts\RemoteSensing\CacheServiceInterface;
use App\Contracts\RemoteSensing\RemoteSensingProviderInterface;
use App\Contracts\RemoteSensing\WeatherProviderInterface;
use App\Repositories\PlotRemoteSensingRepository;
use App\Services\RemoteSensing\NasaEarthdataService;
use App\Services\RemoteSensing\RateLimitService;
use App\Services\RemoteSensing\RemoteSensingCacheService;
use App\Services\RemoteSensing\WeatherService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Remote Sensing dependencies
 * Registers interfaces and their implementations
 */
class RemoteSensingServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register interfaces
        $this->app->bind(
            RemoteSensingProviderInterface::class,
            NasaEarthdataService::class
        );

        $this->app->bind(
            WeatherProviderInterface::class,
            WeatherService::class
        );

        $this->app->bind(
            CacheServiceInterface::class,
            RemoteSensingCacheService::class
        );

        // Register as singletons for reuse within request
        $this->app->singleton(PlotRemoteSensingRepository::class);
        $this->app->singleton(RemoteSensingCacheService::class);
        $this->app->singleton(RateLimitService::class);
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
