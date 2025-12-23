<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
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
        // Forzar HTTPS solo en producción (no en local)
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        } elseif (app()->environment('local')) {
            // Asegurar HTTP en local
            \Illuminate\Support\Facades\URL::forceScheme('http');
        }

        // Personalizar email de verificación de Laravel para Agro365
        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            // Solo forzar HTTPS en producción
            if (app()->environment('production')) {
                $url = str_replace('http://', 'https://', $url);
            }
            
            // Generar URL absoluta para la imagen
            $logoUrl = url('images/logo.png');
            
            // Solo forzar HTTPS en producción
            if (app()->environment('production')) {
                $logoUrl = str_replace('http://', 'https://', $logoUrl);
            }
            
            return (new MailMessage)
                ->subject('Verifica tu cuenta en Agro365')
                ->line(new HtmlString(
                    '<div style="text-align:center; margin-bottom: 16px;">
                        <img src="'.$logoUrl.'" alt="Agro365"
                             style="max-width: 160px; height: auto;">
                     </div>'
                ))
                ->greeting('Hola ' . ($notifiable->name ?: '') . ',')
                ->line('Gracias por registrarte en Agro365, tu cuaderno de campo digital para viticultores.')
                ->line('Para activar tu cuenta y empezar a utilizar la plataforma, por favor verifica tu dirección de correo electrónico haciendo clic en el siguiente botón:')
                ->action('Verificar mi email', $url)
                ->line('Este enlace de verificación expirará en 24 horas.')
                ->line('Si no has solicitado esta cuenta, puedes ignorar este mensaje sin problemas.')
                ->line('Si tienes alguna pregunta, puedes contactarnos en info@agro365.es')
                ->salutation("Saludos,\nEl equipo de Agro365");
        });

        // Personalizar email de reset de contraseña de Laravel para Agro365
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $email = $notifiable->getEmailForPasswordReset();
            
            // Generar URL de reset con el token
            // Construir URL con token en la ruta y email como query parameter
            $url = route('password.reset', ['token' => $token]) . '?email=' . urlencode($email);
            
            // En producción, forzar HTTPS
            if (app()->environment('production')) {
                $url = str_replace('http://', 'https://', $url);
            }
            
            // Log solo en desarrollo para debugging
            if (app()->environment('local')) {
                \Log::info('Password reset URL generated', [
                    'email' => $email,
                    'token_length' => strlen($token),
                    'url' => $url,
                    'environment' => app()->environment(),
                ]);
            }
            
            // Generar URL absoluta para la imagen
            $logoUrl = url('images/logo.png');
            
            // Solo forzar HTTPS en producción
            if (app()->environment('production')) {
                $logoUrl = str_replace('http://', 'https://', $logoUrl);
            }
            
            return (new MailMessage)
                ->subject('Restablece tu contraseña en Agro365')
                ->line(new HtmlString(
                    '<div style="text-align:center; margin-bottom: 16px;">
                        <img src="'.$logoUrl.'" alt="Agro365"
                             style="max-width: 160px; height: auto;">
                     </div>'
                ))
                ->greeting('Hola ' . ($notifiable->name ?: '') . ',')
                ->line('Has solicitado restablecer tu contraseña en Agro365.')
                ->line('Haz clic en el siguiente botón para crear una nueva contraseña:')
                ->action('Restablecer Contraseña', $url)
                ->line('Este enlace expirará en 2 horas.')
                ->line('Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje sin problemas.')
                ->line('Si tienes alguna pregunta, puedes contactarnos en info@agro365.es')
                ->salutation("Saludos,\nEl equipo de Agro365");
        });

        // Registrar observers para stock tracking
        \App\Models\Harvest::observe(\App\Observers\HarvestObserver::class);
        \App\Models\Invoice::observe(\App\Observers\InvoiceObserver::class);
        \App\Models\InvoiceItem::observe(\App\Observers\InvoiceItemObserver::class);
        \App\Models\InvoiceItem::observe(\App\Observers\InvoiceItemObserver::class);
    }
}
