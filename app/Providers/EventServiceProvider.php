<?php

namespace App\Providers;

use App\Events\ActivityCreated;
use App\Events\OfficialReportGenerated;
use App\Events\PaymentCompleted;
use App\Events\PlotLocked;
use App\Events\SubscriptionCreated;
use App\Listeners\CheckWithdrawalPeriodAlerts;
use App\Listeners\ClearUserSubscriptionCache;
use App\Listeners\EnsureViticulturistSelfRecord;
use App\Listeners\GrantBetaAccessOnVerification;
use App\Listeners\LogPlotLocking;
use App\Listeners\LogReportGeneration;
use App\Listeners\LogSubscriptionCreation;
use App\Listeners\SendPaymentConfirmationEmail;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Login → asegurar registro self en WineryViticulturist para viticultores
        Login::class => [
            EnsureViticulturistSelfRecord::class,
        ],

        // Email verification → otorgar 3 meses beta
        Verified::class => [
            GrantBetaAccessOnVerification::class,
        ],

        // Payment events
        PaymentCompleted::class => [
            SendPaymentConfirmationEmail::class,
        ],

        // Subscription events
        SubscriptionCreated::class => [
            LogSubscriptionCreation::class,
            ClearUserSubscriptionCache::class,
        ],

        // Report events
        OfficialReportGenerated::class => [
            LogReportGeneration::class,
        ],

        // Plot events
        PlotLocked::class => [
            LogPlotLocking::class,
        ],

        // Activity events
        ActivityCreated::class => [
            CheckWithdrawalPeriodAlerts::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
