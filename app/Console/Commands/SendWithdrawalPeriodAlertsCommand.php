<?php

namespace App\Console\Commands;

use App\Models\PhytosanitaryTreatment;
use App\Notifications\WithdrawalPeriodEnding;
use Illuminate\Console\Command;

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

        // Obtener tratamientos fitosanitarios con plazo de seguridad
        $treatments = PhytosanitaryTreatment::whereHas('product', function ($query) {
            $query->whereNotNull('withdrawal_period_days')
                  ->where('withdrawal_period_days', '>', 0);
        })
        ->with(['activity', 'activity.plot', 'activity.plot.viticulturist', 'product'])
        ->get();

        $notifiedCount = 0;

        foreach ($treatments as $treatment) {
            $activity = $treatment->activity;
            $product = $treatment->product;

            if (!$activity || !$product) {
                continue;
            }

            $safeDate = $activity->activity_date->copy()->addDays($product->withdrawal_period_days);
            $daysRemaining = now()->diffInDays($safeDate, false);

            // Notificar si está dentro del rango de alerta
            if ($daysRemaining > 0 && $daysRemaining <= $alertDays) {
                try {
                    $user = $activity->plot->viticulturist;
                    
                    if ($user) {
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

        $this->newLine();
        $this->info("✅ Sent {$notifiedCount} withdrawal period alerts.");

        return self::SUCCESS;
    }
}
