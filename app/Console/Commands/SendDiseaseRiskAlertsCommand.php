<?php

namespace App\Console\Commands;

use App\Models\DiseaseAlertSubscription;
use App\Notifications\DiseaseRiskAlertNotification;
use App\Services\Tools\DiseaseRiskForecast;
use App\Services\Tools\DiseaseRiskModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Envía a los suscriptores públicos de /herramientas/alertas-mildiu-oidio un aviso
 * cuando el riesgo de mildiu u oídio es alto en su municipio en los próximos días.
 */
class SendDiseaseRiskAlertsCommand extends Command
{
    /** Meses de campaña (brotación a envero): fuera de ellos no se avisa. */
    public const SEASON_MONTHS = [3, 4, 5, 6, 7, 8, 9];

    /** Días de previsión que se miran (hoy + 2). */
    public const WINDOW_DAYS = 3;

    /** No repetir el aviso de la misma enfermedad antes de estos días. */
    public const COOLDOWN_DAYS = 4;

    protected $signature = 'tools:send-disease-alerts
        {--force : Enviar aunque estemos fuera de campaña}
        {--dry-run : Calcular sin enviar emails ni guardar nada}';

    protected $description = 'Envía las alertas públicas de riesgo alto de mildiu y oídio';

    public function handle(DiseaseRiskForecast $forecasts): int
    {
        if (! $this->option('force') && ! in_array(now('Europe/Madrid')->month, self::SEASON_MONTHS, true)) {
            $this->info('Fuera de campaña: no se envían alertas.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sent = 0;
        $withoutData = 0;

        DiseaseAlertSubscription::active()->with('municipality')->chunkById(200, function ($subscriptions) use ($forecasts, $dryRun, &$sent, &$withoutData) {
            foreach ($subscriptions as $subscription) {
                // El servicio cachea por coordenadas: vecinos del mismo municipio no repiten llamada.
                $days = $forecasts->forLocation($subscription->lat, $subscription->lng);
                if ($days === null) {
                    $withoutData++;

                    continue;
                }

                $window = array_slice($days, 0, self::WINDOW_DAYS);
                $diseases = [];
                $alertDays = [];

                foreach (['mildew' => 'last_mildew_alert_at', 'powdery' => 'last_powdery_alert_at'] as $disease => $column) {
                    $highDays = array_filter($window, fn (array $day) => $day[$disease] === DiseaseRiskModel::HIGH);
                    $lastAlert = $subscription->{$column};

                    if ($highDays && (! $lastAlert || $lastAlert->lt(now()->subDays(self::COOLDOWN_DAYS)))) {
                        $diseases[] = $disease;
                        $alertDays += $highDays; // mismas claves = mismo día
                    }
                }

                if ($diseases === []) {
                    continue;
                }

                ksort($alertDays);
                $sent++;
                $this->line("→ {$subscription->email} ({$subscription->municipality?->name}): ".implode(', ', $diseases));

                if ($dryRun) {
                    continue;
                }

                Notification::route('mail', $subscription->email)
                    ->notify(new DiseaseRiskAlertNotification($subscription, $diseases, array_values($alertDays)));

                $subscription->update(array_fill_keys(
                    array_map(fn (string $d) => $d === 'mildew' ? 'last_mildew_alert_at' : 'last_powdery_alert_at', $diseases),
                    now()
                ));
            }
        });

        $this->info(($dryRun ? '[dry-run] ' : '')."Alertas enviadas: {$sent}. Suscripciones sin datos meteo: {$withoutData}.");

        return self::SUCCESS;
    }
}
