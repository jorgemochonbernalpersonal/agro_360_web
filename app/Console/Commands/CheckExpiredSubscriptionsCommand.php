<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringSoon;
use Illuminate\Console\Command;

class CheckExpiredSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired {--notify-days=7,3,1 : Days before expiration to send notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $notifyDays = explode(',', $this->option('notify-days'));

        $this->info('Checking for expiring subscriptions...');

        $expiredCount = 0;
        $notifiedCount = 0;

        // Marcar como expiradas las suscripciones vencidas
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);
            $expiredCount++;
        }

        if ($expiredCount > 0) {
            $this->warn("⚠️  Marked {$expiredCount} subscriptions as expired.");
        }

        // Notificar suscripciones próximas a expirar
        foreach ($notifyDays as $days) {
            $targetDate = now()->addDays((int) $days)->startOfDay();
            $endDate = $targetDate->copy()->endOfDay();

            $expiringSubscriptions = Subscription::where('status', 'active')
                ->whereBetween('ends_at', [$targetDate, $endDate])
                ->with('user')
                ->get();

            foreach ($expiringSubscriptions as $subscription) {
                try {
                    $subscription->user->notify(
                        new SubscriptionExpiringSoon($subscription, (int) $days)
                    );
                    $notifiedCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to notify user {$subscription->user_id}: {$e->getMessage()}");
                }
            }

            if ($expiringSubscriptions->count() > 0) {
                $this->info("📧 Sent {$expiringSubscriptions->count()} notifications for subscriptions expiring in {$days} days.");
            }
        }

        $this->newLine();
        $this->info("✅ Check completed. Total notifications sent: {$notifiedCount}");

        return self::SUCCESS;
    }
}
