<?php

namespace App\Providers;

use App\Models\AgriculturalActivity;
use App\Models\BottlingAuthorization;
use App\Models\Campaign;
use App\Models\CellarOperation;
use App\Models\Client;
use App\Models\Container;
use App\Models\ContainerRoom;
use App\Models\Crew;
use App\Models\DoInspection;
use App\Models\DoLabel;
use App\Models\DoQualification;
use App\Models\EcoCertification;
use App\Models\ExternalGrape;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabelBatch;
use App\Models\Machinery;
use App\Models\MultipartPlotSigpac;
use App\Models\Oenologist;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\PlotRemoteSensing;
use App\Models\ProductLot;
use App\Models\SanitaryRegistration;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineAnalysis;
use App\Models\WineBottling;
use App\Models\WineCost;
use App\Models\WineFermentationControl;
use App\Models\WineLabeling;
use App\Models\WineLoss;
use App\Models\WineryDocument;
use App\Models\WinerySupply;
use App\Models\WineryViticulturist;
use App\Models\WineTastingNote;
use App\Models\WineTransfer;
use App\Observers\AgriculturalActivityObserver;
use App\Observers\CampaignObserver;
use App\Observers\HarvestObserver;
use App\Observers\InvoiceItemObserver;
use App\Observers\InvoiceObserver;
use App\Observers\MultipartPlotSigpacObserver;
use App\Observers\PhytosanitaryProductObserver;
use App\Observers\PhytosanitaryTreatmentObserver;
use App\Observers\PlotObserver;
use App\Observers\PlotPlantingObserver;
use App\Observers\PlotRemoteSensingObserver;
use App\Observers\UserObserver;
use App\Observers\WineLossObserver;
use App\Observers\WineryViticulturistObserver;
use App\Policies\AgriculturalActivityPolicy;
use App\Policies\BottlingAuthorizationPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\CellarOperationPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ContainerPolicy;
use App\Policies\ContainerRoomPolicy;
use App\Policies\CrewPolicy;
use App\Policies\DoInspectionPolicy;
use App\Policies\DoLabelPolicy;
use App\Policies\DoQualificationPolicy;
use App\Policies\EcoCertificationPolicy;
use App\Policies\ExternalGrapePolicy;
use App\Policies\HarvestDeliveryPolicy;
use App\Policies\HarvestPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LabelBatchPolicy;
use App\Policies\MachineryPolicy;
use App\Policies\OenologistPolicy;
use App\Policies\PlotPlantingPolicy;
use App\Policies\PlotPolicy;
use App\Policies\ProductLotPolicy;
use App\Policies\SanitaryRegistrationPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\WineAnalysisPolicy;
use App\Policies\WineBottlingPolicy;
use App\Policies\WineCostPolicy;
use App\Policies\WineFermentationControlPolicy;
use App\Policies\WineLabelingPolicy;
use App\Policies\WineLossPolicy;
use App\Policies\WinePolicy;
use App\Policies\WineryDocumentPolicy;
use App\Policies\WinerySupplyPolicy;
use App\Policies\WineTastingNotePolicy;
use App\Policies\WineTransferPolicy;
use App\Services\ContainerStockService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
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
        Plot::class => PlotPolicy::class,
        PlotPlanting::class => PlotPlantingPolicy::class,
        Campaign::class => CampaignPolicy::class,
        AgriculturalActivity::class => AgriculturalActivityPolicy::class,
        Crew::class => CrewPolicy::class,
        Machinery::class => MachineryPolicy::class,
        Client::class => ClientPolicy::class,
        Harvest::class => HarvestPolicy::class,
        HarvestDelivery::class => HarvestDeliveryPolicy::class,
        Wine::class => WinePolicy::class,
        WineTransfer::class => WineTransferPolicy::class,
        WineLoss::class => WineLossPolicy::class,
        WineFermentationControl::class => WineFermentationControlPolicy::class,
        Container::class => ContainerPolicy::class,
        Invoice::class => InvoicePolicy::class,
        DoLabel::class => DoLabelPolicy::class,
        DoInspection::class => DoInspectionPolicy::class,
        DoQualification::class => DoQualificationPolicy::class,
        BottlingAuthorization::class => BottlingAuthorizationPolicy::class,
        CellarOperation::class => CellarOperationPolicy::class,
        ContainerRoom::class => ContainerRoomPolicy::class,
        EcoCertification::class => EcoCertificationPolicy::class,
        ExternalGrape::class => ExternalGrapePolicy::class,
        LabelBatch::class => LabelBatchPolicy::class,
        Oenologist::class => OenologistPolicy::class,
        ProductLot::class => ProductLotPolicy::class,
        SanitaryRegistration::class => SanitaryRegistrationPolicy::class,
        Supplier::class => SupplierPolicy::class,
        WineAnalysis::class => WineAnalysisPolicy::class,
        WineBottling::class => WineBottlingPolicy::class,
        WineCost::class => WineCostPolicy::class,
        WineLabeling::class => WineLabelingPolicy::class,
        WineTastingNote::class => WineTastingNotePolicy::class,
        WineryDocument::class => WineryDocumentPolicy::class,
        WinerySupply::class => WinerySupplyPolicy::class,
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

            // SEO — host canónico en TODA URL generada (url(), route(), asset(), canonical).
            // Sin esto, una petición por www.agro365.es o por la ruta física de la subcarpeta
            // (/agro_360_web/public/…) generaba <link rel="canonical"> y og:url apuntando a esos
            // hosts/rutas, creando copias duplicadas indexables del sitio. config('app.url') es la
            // única fuente de verdad del dominio (ya la usa el SitemapService).
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifica tu cuenta en Agro365')
                ->greeting('Hola '.($notifiable->name ?: '').',')
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
            $url = route('password.reset', ['token' => $token]).'?email='.urlencode($email);

            if ($this->app->environment('local')) {
                \Log::info('Password reset URL generated', [
                    'email' => $email,
                    'token_length' => strlen($token),
                    'url' => $url,
                    'environment' => $this->app->environment(),
                ]);
            }

            return (new MailMessage)
                ->subject('Restablece tu contraseña en Agro365')
                ->greeting('Hola '.($notifiable->name ?: '').',')
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
        $target = config('mail.redirect_to');

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
}
