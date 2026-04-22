<?php

namespace App\Providers;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Machinery;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineLoss;
use App\Models\MultipartPlotSigpac;
use App\Models\WineryViticulturist;
use App\Models\PlotRemoteSensing;
use App\Observers\AgriculturalActivityObserver;
use App\Observers\MultipartPlotSigpacObserver;
use App\Observers\UserObserver;
use App\Observers\WineryViticulturistObserver;
use App\Observers\PlotRemoteSensingObserver;
use App\Observers\CampaignObserver;
use App\Observers\HarvestObserver;
use App\Observers\InvoiceItemObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PhytosanitaryProductObserver;
use App\Observers\PhytosanitaryTreatmentObserver;
use App\Observers\PlotObserver;
use App\Observers\PlotPlantingObserver;
use App\Observers\WineLossObserver;
use App\Models\DoInspection;
use App\Models\DoLabel;
use App\Models\DoQualification;
use App\Policies\AgriculturalActivityPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\CrewPolicy;
use App\Policies\DoInspectionPolicy;
use App\Policies\DoLabelPolicy;
use App\Policies\DoQualificationPolicy;
use App\Policies\MachineryPolicy;
use App\Policies\PlotPlantingPolicy;
use App\Policies\PlotPolicy;
use App\Services\ContainerStockService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Plot::class               => PlotPolicy::class,
        PlotPlanting::class       => PlotPlantingPolicy::class,
        Campaign::class           => CampaignPolicy::class,
        AgriculturalActivity::class => AgriculturalActivityPolicy::class,
        Crew::class               => CrewPolicy::class,
        Machinery::class          => MachineryPolicy::class,
        DoLabel::class            => DoLabelPolicy::class,
        DoInspection::class       => DoInspectionPolicy::class,
        DoQualification::class    => DoQualificationPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(ContainerStockService::class);
    }

    public function boot(): void
    {
        $this->registerEmailRedirect();

        (new \App\Macros\CollectionMacros)->register();
        \App\Macros\StringMacros::register();

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifica tu cuenta en Agro365')
                ->line(new HtmlString($this->logoHtml()))
                ->greeting('Hola ' . ($notifiable->name ?: '') . ',')
                ->line('Gracias por registrarte en Agro365, tu cuaderno de campo digital para viticultores.')
                ->line('Para activar tu cuenta y empezar a utilizar la plataforma, por favor verifica tu dirección de correo electrónico haciendo clic en el siguiente botón:')
                ->action('Verificar mi email', $url)
                ->line('Este enlace de verificación expirará en 24 horas.')
                ->line('Si no has solicitado esta cuenta, puedes ignorar este mensaje sin problemas.')
                ->line('Si tienes alguna pregunta, puedes contactarnos en info@agro365.es')
                ->salutation("Saludos,\nEl equipo de Agro365");
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $email = $notifiable->getEmailForPasswordReset();
            $url   = route('password.reset', ['token' => $token]) . '?email=' . urlencode($email);

            if ($this->app->environment('local')) {
                \Log::info('Password reset URL generated', [
                    'email'        => $email,
                    'token_length' => strlen($token),
                    'url'          => $url,
                    'environment'  => $this->app->environment(),
                ]);
            }

            return (new MailMessage)
                ->subject('Restablece tu contraseña en Agro365')
                ->line(new HtmlString($this->logoHtml()))
                ->greeting('Hola ' . ($notifiable->name ?: '') . ',')
                ->line('Has solicitado restablecer tu contraseña en Agro365.')
                ->line('Haz clic en el siguiente botón para crear una nueva contraseña:')
                ->action('Restablecer Contraseña', $url)
                ->line('Este enlace expirará en 2 horas.')
                ->line('Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje sin problemas.')
                ->line('Si tienes alguna pregunta, puedes contactarnos en info@agro365.es')
                ->salutation("Saludos,\nEl equipo de Agro365");
        });

        // Observers
        Harvest::observe(HarvestObserver::class);
        Invoice::observe(InvoiceObserver::class);
        InvoiceItem::observe(InvoiceItemObserver::class);
        AgriculturalActivity::observe(AgriculturalActivityObserver::class);
        Plot::observe(PlotObserver::class);
        PlotPlanting::observe(PlotPlantingObserver::class);
        Campaign::observe(CampaignObserver::class);
        PhytosanitaryProduct::observe(PhytosanitaryProductObserver::class);
        PhytosanitaryTreatment::observe(PhytosanitaryTreatmentObserver::class);
        // WineTransferObserver eliminado: WineContainerStockService::recordTransfer/revertTransfer
        // gestiona wine_volume_liters y ContainerHistory. El observer era código anterior al servicio
        // y provocaba doble conteo en getTotalUsed() al modificar used_capacity erróneamente.
        WineLoss::observe(WineLossObserver::class);
        PlotRemoteSensing::observe(PlotRemoteSensingObserver::class);
        User::observe(UserObserver::class);
        WineryViticulturist::observe(WineryViticulturistObserver::class);
        MultipartPlotSigpac::observe(MultipartPlotSigpacObserver::class);
    }

    private function registerEmailRedirect(): void
    {
        $pattern = config('mail.redirect_pattern');
        $target  = config('mail.redirect_to');

        if (! $pattern || ! $target) {
            return;
        }

        Event::listen(MessageSending::class, function (MessageSending $event) use ($pattern, $target) {
            $message = $event->message;

            $newTo = array_map(
                fn (Address $addr) => preg_match($pattern, $addr->getAddress())
                    ? new Address($target, $addr->getName())
                    : $addr,
                $message->getTo()
            );

            $message->to(...$newTo);
        });
    }

    private function logoHtml(): string
    {
        return '<div style="text-align:center; margin-bottom: 16px;">
                    <img src="' . url('images/logo.png') . '" alt="Agro365"
                         style="max-width: 160px; height: auto;">
                </div>';
    }
}
