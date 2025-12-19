<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
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
        // Personalizar email de verificación de Laravel para Agro365
        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifica tu cuenta en Agro365')
                ->line(new HtmlString(
                    '<div style="text-align:center; margin-bottom: 16px;">
                        <img src="'.asset('images/logo.png').'" alt="Agro365"
                             style="max-width: 160px; height: auto;">
                     </div>'
                ))
                ->greeting('Hola ' . ($notifiable->name ?: ''))
                ->line('Gracias por registrarte en Agro365, tu cuaderno de campo digital para viticultores.')
                ->line('Para activar tu cuenta y empezar a utilizar la plataforma, por favor verifica tu dirección de correo electrónico haciendo clic en el siguiente botón:')
                ->action('Verificar mi email', $url)
                ->line('Si no has solicitado esta cuenta, puedes ignorar este mensaje sin problemas.')
                ->salutation("Saludos,\nAgro365");
        });
    }
}
