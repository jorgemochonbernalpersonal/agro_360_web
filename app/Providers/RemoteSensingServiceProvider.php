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

        // Register NASA services
        $this->app->singleton(\App\Services\RemoteSensing\NasaLSTService::class);
        $this->app->singleton(\App\Services\RemoteSensing\NasaAreaRequestService::class);
        $this->app->singleton(\App\Services\RemoteSensing\NasaVIIRSService::class);
        $this->app->singleton(\App\Services\RemoteSensing\NasaSpectralBandsService::class);
        $this->app->singleton(\App\Services\RemoteSensing\NasaLAIService::class);
        $this->app->singleton(\App\Services\RemoteSensing\NasaSMAPService::class);
        $this->app->singleton(\App\Services\RemoteSensing\NasaETService::class);

        // Register Advanced Calculators
        $this->app->singleton(\App\Services\RemoteSensing\Calculators\LAICalculator::class);
        $this->app->singleton(\App\Services\RemoteSensing\Calculators\ChlorophyllCalculator::class);
        $this->app->singleton(\App\Services\RemoteSensing\Calculators\MaturityCalculator::class);

        // Register Detectors
        $this->app->singleton(\App\Services\RemoteSensing\Detectors\AnomalyDetector::class);

        // Register Advanced Analysis Service (aggregator)
        $this->app->singleton(\App\Services\RemoteSensing\AdvancedAnalysisService::class);
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
