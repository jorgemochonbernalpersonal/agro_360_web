<?php

namespace App\Console\Commands;

use App\Models\PhytosanitaryTreatment;
use App\Notifications\WithdrawalPeriodEnding;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendWithdrawalPeriodAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treatments:send-withdrawal-alerts {--days=3 : Days before safe date to send alert}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alerts for phytosanitary treatments approaching their withdrawal period end';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $alertDays = $this->option('days');

        $this->info('Checking for treatments approaching withdrawal period end...');

        // El plazo de seguridad más largo soportado por el modelo de producto no está
        // acotado por config, así que usamos una cota razonable: ningún tratamiento con
        // más de 180 días desde su fecha de actividad puede seguir "en plazo" para las
        // alertas (--days por defecto es 3), lo que evita cargar en memoria el histórico
        // completo de tratamientos de la aplicación.
        $maxWithdrawalPeriodDays = 180;

        $notifiedCount = 0;

        // Obtener tratamientos fitosanitarios con plazo de seguridad, acotados a un rango
        // de fechas razonable, y procesados en chunks para no cargar todo en memoria.
        PhytosanitaryTreatment::whereHas('product', function ($query) {
            $query->whereNotNull('withdrawal_period_days')
                ->where('withdrawal_period_days', '>', 0);
        })
            ->whereHas('activity', function ($query) use ($maxWithdrawalPeriodDays) {
                $query->where('activity_date', '>=', now()->subDays($maxWithdrawalPeriodDays));
            })
            ->with(['activity', 'activity.plot', 'activity.plot.viticulturist', 'product'])
            ->chunkById(200, function ($treatments) use ($alertDays, &$notifiedCount) {
                foreach ($treatments as $treatment) {
                    $activity = $treatment->activity;
                    $product = $treatment->product;

                    if (! $activity || ! $product) {
                        continue;
                    }

                    $safeDate = $activity->activity_date->copy()->addDays($product->withdrawal_period_days);

                    // Comparar por día completo (startOfDay a ambos lados), no por el
                    // instante actual: now()->diffInDays() devuelve un float cuando la
                    // hora actual y la de $safeDate difieren (p. ej. 08:15 vs 00:00), y el
                    // cast a (int) trunca hacia cero (0.66 → 0), lo que podía hacer que la
                    // alerta de "1 día restante" nunca se disparase o llegara con un día
                    // de desfase.
                    $daysRemaining = now()->startOfDay()->diffInDays($safeDate->copy()->startOfDay(), false);

                    // Notificar si está dentro del rango de alerta
                    if ($daysRemaining > 0 && $daysRemaining <= $alertDays) {
                        $cacheKey = "withdrawal_alert_{$treatment->id}_{$daysRemaining}";

                        if (Cache::has($cacheKey)) {
                            continue;
                        }

                        try {
                            $user = $activity->plot->viticulturist;

                            if ($user) {
                                Cache::put($cacheKey, true, now()->addHours(24));

                                $user->notify(
                                    new WithdrawalPeriodEnding($treatment, $safeDate, $daysRemaining)
                                );
                                $notifiedCount++;

                                $this->line("📧 Notified {$user->name} about treatment on {$activity->plot->name} ({$daysRemaining} days remaining)");
                            }
                        } catch (\Exception $e) {
                            $this->error("Failed to notify: {$e->getMessage()}");
                        }
                    }
                }
            });

        $this->newLine();
        $this->info("✅ Sent {$notifiedCount} withdrawal period alerts.");

        return self::SUCCESS;
    }
}
