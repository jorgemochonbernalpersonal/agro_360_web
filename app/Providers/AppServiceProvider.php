<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Plot;
use App\Models\Campaign;
use App\Models\AgriculturalActivity;
use App\Models\Crew;
use App\Models\Machinery;
use App\Policies\PlotPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\AgriculturalActivityPolicy;
use App\Policies\CrewPolicy;
use App\Policies\MachineryPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Plot::class => PlotPolicy::class,
        Campaign::class => CampaignPolicy::class,
        AgriculturalActivity::class => AgriculturalActivityPolicy::class,
        Crew::class => CrewPolicy::class,
        Machinery::class => MachineryPolicy::class,
    ];

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
        //
    }
}
